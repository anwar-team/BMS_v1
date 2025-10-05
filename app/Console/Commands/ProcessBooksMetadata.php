<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\BookExtractedMetadata;
use App\Services\AuthorExtractor;
use App\Services\BookSectionMatcher;
use App\Services\BookSectionClassifier;
use App\Services\PublisherExtractor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessBooksMetadata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:process-metadata
                            {--limit=100 : Number of books to process}
                            {--batch-size=10 : Number of books per batch}
                            {--force : Force reprocess already processed books}
                            {--apply : Automatically apply high confidence matches}
                            {--book-id= : Process specific book by ID}
                            {--without-section : Process only books without book_section_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'استخراج البيانات الوصفية (المؤلف، القسم، الناشر) من أوصاف الكتب';

    protected $sectionMatcher;
    protected $sectionClassifier;
    protected $authorExtractor;
    protected $publisherExtractor;

    protected $stats = [
        'total' => 0,
        'processed' => 0,
        'skipped' => 0,
        'sections_extracted' => 0,
        'authors_extracted' => 0,
        'publishers_extracted' => 0,
        'high_confidence' => 0,
        'auto_applied' => 0,
        'needs_review' => 0,
        'errors' => 0,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🚀 بدء معالجة البيانات الوصفية للكتب...\n");

        // تهيئة الخدمات
        $this->sectionMatcher = new BookSectionMatcher();
        $this->sectionClassifier = new BookSectionClassifier();
        $this->authorExtractor = new AuthorExtractor();
        $this->publisherExtractor = new PublisherExtractor();

        // جلب الكتب
        $books = $this->getBooks();
        
        if ($books->isEmpty()) {
            $this->warn("⚠️  لم يتم العثور على كتب للمعالجة!");
            return 0;
        }

        $this->stats['total'] = $books->count();
        $this->info("📚 عدد الكتب: {$this->stats['total']}\n");

        // شريط التقدم
        $bar = $this->output->createProgressBar($this->stats['total']);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        // معالجة الكتب
        foreach ($books as $book) {
            $bar->setMessage("معالجة: {$book->title}");
            
            try {
                $this->processBook($book);
                $this->stats['processed']++;
            } catch (\Exception $e) {
                $this->stats['errors']++;
                Log::error("Error processing book {$book->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // عرض النتائج
        $this->displayResults();

        return 0;
    }

    /**
     * جلب الكتب للمعالجة
     */
    protected function getBooks()
    {
        $query = Book::whereNotNull('description')
            ->where('description', '!=', '');

        // معالجة كتاب محدد
        if ($bookId = $this->option('book-id')) {
            return $query->where('id', $bookId)->get();
        }

        // فلتر: فقط الكتب بدون أقسام
        if ($this->option('without-section')) {
            $query->whereNull('book_section_id');
        }

        // تجاهل المعالج مسبقاً إلا في حالة force
        if (!$this->option('force')) {
            $query->whereDoesntHave('extractedMetadata');
        }

        $limit = (int) $this->option('limit');
        
        return $query->limit($limit)->get();
    }

    /**
     * معالجة كتاب واحد
     */
    protected function processBook(Book $book)
    {
        // التحقق من وجود بيانات مستخرجة مسبقاً
        $metadata = $book->extractedMetadata;
        
        if ($metadata && !$this->option('force')) {
            $this->stats['skipped']++;
            return;
        }

        // إنشاء أو تحديث البيانات المستخرجة
        $metadata = $metadata ?? new BookExtractedMetadata(['book_id' => $book->id]);

        DB::beginTransaction();
        
        try {
            // 1. استخراج القسم
            $sectionData = $this->sectionMatcher->process($book->description);
            
            // إذا لم ينجح استخراج القسم من الوصف، استخدم المُصنِّف الذكي
            if (!$sectionData || !isset($sectionData['matched_section_id'])) {
                $classificationData = $this->sectionClassifier->classify($book);
                
                // الحد الأدنى للثقة: 0.30 (30%)
                if ($classificationData && $classificationData['confidence'] >= 0.30) {
                    $sectionData = [
                        'extracted_section_name' => 'تصنيف تلقائي: ' . $classificationData['section_name'],
                        'matched_section_id' => $classificationData['section_id'],
                        'section_match_confidence' => $classificationData['confidence']  // قيمة من 0.00 إلى 1.00
                    ];
                }
            }
            
            if ($sectionData) {
                $metadata->extracted_section_name = $sectionData['extracted_section_name'] ?? null;
                $metadata->matched_section_id = $sectionData['matched_section_id'] ?? null;
                $metadata->section_match_confidence = $sectionData['section_match_confidence'] ?? null;
                
                if ($sectionData['matched_section_id'] ?? null) {
                    $this->stats['sections_extracted']++;
                }
            }

            // 2. استخراج المؤلف
            $authorData = $this->authorExtractor->process($book->description);
            if ($authorData) {
                $metadata->extracted_author_name = $authorData['extracted_author_name'];
                $metadata->matched_author_id = $authorData['matched_author_id'];
                $metadata->author_match_confidence = $authorData['author_match_confidence'];
                
                if ($authorData['matched_author_id']) {
                    $this->stats['authors_extracted']++;
                }
            }

            // 3. استخراج الناشر
            $publisherData = $this->publisherExtractor->process($book->description);
            if ($publisherData) {
                $metadata->extracted_publisher_name = $publisherData['extracted_publisher_name'];
                $metadata->matched_publisher_id = $publisherData['matched_publisher_id'];
                $metadata->publisher_match_confidence = $publisherData['publisher_match_confidence'];
                
                if ($publisherData['matched_publisher_id']) {
                    $this->stats['publishers_extracted']++;
                }
            }

            // تحديد الحالة
            if ($metadata->matched_author_id || $metadata->matched_publisher_id || $metadata->matched_section_id) {
                $metadata->processing_status = 'matched';
                
                // حساب الثقة الإجمالية
                if ($metadata->getOverallConfidenceAttribute() >= 0.80) {
                    $this->stats['high_confidence']++;
                } else {
                    $metadata->needs_review = true;
                    $this->stats['needs_review']++;
                }
            } else {
                $metadata->processing_status = 'extracted';
                $metadata->needs_review = true;
                $this->stats['needs_review']++;
            }

            $metadata->is_processed = true;
            $metadata->save();

            // التطبيق التلقائي إذا مطلوب
            if ($this->option('apply') && $metadata->canAutoApply() && !$metadata->is_applied) {
                $this->applyMetadata($book, $metadata);
            }

            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * تطبيق البيانات المستخرجة على الكتاب
     */
    protected function applyMetadata(Book $book, BookExtractedMetadata $metadata)
    {
        $applied = false;

        // تطبيق القسم
        if ($metadata->matched_section_id && $metadata->section_match_confidence >= 0.80) {
            $book->book_section_id = $metadata->matched_section_id;
            $applied = true;
        }

        // تطبيق المؤلف
        if ($metadata->matched_author_id && $metadata->author_match_confidence >= 0.80) {
            // التحقق من وجود علاقة author_id في جدول books
            if (in_array('author_id', $book->getFillable())) {
                $book->author_id = $metadata->matched_author_id;
                $applied = true;
            }
        }

        // تطبيق الناشر
        if ($metadata->matched_publisher_id && $metadata->publisher_match_confidence >= 0.80) {
            // التحقق من وجود علاقة publisher_id في جدول books
            if (in_array('publisher_id', $book->getFillable())) {
                $book->publisher_id = $metadata->matched_publisher_id;
                $applied = true;
            }
        }

        if ($applied) {
            $book->save();
            $metadata->is_applied = true;
            $metadata->applied_at = now();
            $metadata->save();
            
            $this->stats['auto_applied']++;
        }
    }

    /**
     * عرض النتائج النهائية
     */
    protected function displayResults()
    {
        $this->info("📊 النتائج النهائية:");
        $this->info("═══════════════════════════════════════\n");

        $this->table(
            ['المقياس', 'العدد', 'النسبة'],
            [
                ['إجمالي الكتب', $this->stats['total'], '100%'],
                ['معالج بنجاح', $this->stats['processed'], $this->percentage($this->stats['processed'], $this->stats['total'])],
                ['متجاوز', $this->stats['skipped'], $this->percentage($this->stats['skipped'], $this->stats['total'])],
                ['', '', ''],
                ['أقسام مستخرجة', $this->stats['sections_extracted'], $this->percentage($this->stats['sections_extracted'], $this->stats['processed'])],
                ['مؤلفين مستخرجين', $this->stats['authors_extracted'], $this->percentage($this->stats['authors_extracted'], $this->stats['processed'])],
                ['ناشرين مستخرجين', $this->stats['publishers_extracted'], $this->percentage($this->stats['publishers_extracted'], $this->stats['processed'])],
                ['', '', ''],
                ['ثقة عالية (≥80%)', $this->stats['high_confidence'], $this->percentage($this->stats['high_confidence'], $this->stats['processed'])],
                ['تم التطبيق تلقائياً', $this->stats['auto_applied'], $this->percentage($this->stats['auto_applied'], $this->stats['processed'])],
                ['يحتاج مراجعة', $this->stats['needs_review'], $this->percentage($this->stats['needs_review'], $this->stats['processed'])],
                ['أخطاء', $this->stats['errors'], $this->percentage($this->stats['errors'], $this->stats['total'])],
            ]
        );

        // رسائل تحفيزية
        if ($this->stats['authors_extracted'] >= $this->stats['processed'] * 0.8) {
            $this->info("\n✅ معدل استخراج المؤلفين ممتاز!");
        }

        if ($this->stats['high_confidence'] >= $this->stats['processed'] * 0.6) {
            $this->info("✅ معدل الثقة العالية جيد جداً!");
        }

        if ($this->stats['needs_review'] > 0) {
            $this->warn("\n⚠️  {$this->stats['needs_review']} كتاب يحتاج مراجعة يدوية.");
        }

        if ($this->stats['errors'] > 0) {
            $this->error("❌ حدثت {$this->stats['errors']} أخطاء. راجع سجل الأخطاء.");
        }

        $this->newLine();
        $this->info("✨ تمت المعالجة بنجاح!");
    }

    /**
     * حساب النسبة المئوية
     */
    protected function percentage($part, $total)
    {
        if ($total == 0) return '0%';
        return round(($part / $total) * 100, 1) . '%';
    }
}
