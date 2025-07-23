<?php

namespace App\Console\Commands;

use App\Services\BokConverterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Helper\ProgressBar;

class ConvertBokCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bok:convert 
                            {file : مسار ملف .bok المراد تحويله}
                            {--batch : تحويل جميع ملفات .bok في المجلد}
                            {--status=published : حالة الكتاب بعد التحويل (draft|published|archived)}
                            {--clean : تنظيف النصوص أثناء التحويل}
                            {--no-structure : عدم الكشف التلقائي عن هيكل الكتاب}
                            {--verbose : عرض تفاصيل إضافية}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تحويل ملفات .bok من المكتبة الشاملة إلى قاعدة البيانات';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $isBatch = $this->option('batch');
        
        $this->info('🚀 بدء عملية تحويل ملفات BOK');
        $this->newLine();
        
        if ($isBatch) {
            return $this->handleBatchConversion($filePath);
        } else {
            return $this->handleSingleConversion($filePath);
        }
    }
    
    /**
     * تحويل ملف واحد
     */
    private function handleSingleConversion(string $filePath): int
    {
        // التحقق من وجود الملف
        if (!File::exists($filePath)) {
            $this->error("❌ الملف غير موجود: {$filePath}");
            return Command::FAILURE;
        }
        
        // التحقق من امتداد الملف
        if (!str_ends_with(strtolower($filePath), '.bok')) {
            $this->error('❌ يجب أن يكون الملف بامتداد .bok');
            return Command::FAILURE;
        }
        
        $this->info("📖 تحويل الملف: " . basename($filePath));
        
        try {
            $converter = new BokConverterService();
            
            // إنشاء شريط التقدم
            $progressBar = $this->output->createProgressBar(4);
            $progressBar->setFormat('verbose');
            $progressBar->start();
            
            $progressBar->setMessage('تحليل هيكل الملف...');
            $progressBar->advance();
            
            $progressBar->setMessage('استخراج البيانات...');
            $progressBar->advance();
            
            $progressBar->setMessage('تطبيع البيانات...');
            $progressBar->advance();
            
            $progressBar->setMessage('إدراج في قاعدة البيانات...');
            $result = $converter->convertBokFile($filePath);
            $progressBar->advance();
            
            $progressBar->finish();
            $this->newLine(2);
            
            if ($result['success']) {
                $this->info("✅ تم التحويل بنجاح!");
                $this->table(
                    ['المعلومة', 'القيمة'],
                    [
                        ['معرف الكتاب', $result['book_id']],
                        ['عنوان الكتاب', $result['title']],
                        ['حالة التحويل', 'مكتمل']
                    ]
                );
                
                if ($this->option('verbose')) {
                    $this->displayBookDetails($result['book_id']);
                }
                
                return Command::SUCCESS;
            } else {
                $this->error("❌ فشل التحويل: {$result['error']}");
                return Command::FAILURE;
            }
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في التحويل: " . $e->getMessage());
            
            if ($this->option('verbose')) {
                $this->error($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        }
    }
    
    /**
     * تحويل متعدد للملفات
     */
    private function handleBatchConversion(string $directoryPath): int
    {
        if (!File::isDirectory($directoryPath)) {
            $this->error("❌ المجلد غير موجود: {$directoryPath}");
            return Command::FAILURE;
        }
        
        // البحث عن ملفات .bok
        $bokFiles = File::glob($directoryPath . '/*.bok');
        
        if (empty($bokFiles)) {
            $this->warn('⚠️ لم يتم العثور على ملفات .bok في المجلد المحدد');
            return Command::SUCCESS;
        }
        
        $this->info("📚 تم العثور على " . count($bokFiles) . " ملف .bok");
        $this->newLine();
        
        $successCount = 0;
        $failureCount = 0;
        $results = [];
        
        // إنشاء شريط التقدم الرئيسي
        $mainProgress = $this->output->createProgressBar(count($bokFiles));
        $mainProgress->setFormat('verbose');
        $mainProgress->start();
        
        foreach ($bokFiles as $filePath) {
            $fileName = basename($filePath);
            $mainProgress->setMessage("معالجة: {$fileName}");
            
            try {
                $converter = new BokConverterService();
                $result = $converter->convertBokFile($filePath);
                
                if ($result['success']) {
                    $successCount++;
                    $results[] = [
                        'file' => $fileName,
                        'status' => '✅ نجح',
                        'title' => $result['title'],
                        'book_id' => $result['book_id']
                    ];
                } else {
                    $failureCount++;
                    $results[] = [
                        'file' => $fileName,
                        'status' => '❌ فشل',
                        'title' => 'غير متاح',
                        'book_id' => 'غير متاح'
                    ];
                }
                
            } catch (\Exception $e) {
                $failureCount++;
                $results[] = [
                    'file' => $fileName,
                    'status' => '❌ خطأ',
                    'title' => $e->getMessage(),
                    'book_id' => 'غير متاح'
                ];
            }
            
            $mainProgress->advance();
        }
        
        $mainProgress->finish();
        $this->newLine(2);
        
        // عرض النتائج
        $this->info('📊 ملخص عملية التحويل المتعدد:');
        $this->table(
            ['الملف', 'الحالة', 'عنوان الكتاب', 'معرف الكتاب'],
            $results
        );
        
        $this->newLine();
        $this->info("✅ نجح: {$successCount} ملف");
        $this->info("❌ فشل: {$failureCount} ملف");
        $this->info("📈 معدل النجاح: " . round(($successCount / count($bokFiles)) * 100, 2) . "%");
        
        return $failureCount === 0 ? Command::SUCCESS : Command::FAILURE;
    }
    
    /**
     * عرض تفاصيل الكتاب
     */
    private function displayBookDetails(int $bookId): void
    {
        try {
            $book = \App\Models\Book::with(['volumes', 'chapters', 'pages'])->find($bookId);
            
            if (!$book) {
                $this->warn('⚠️ لم يتم العثور على تفاصيل الكتاب');
                return;
            }
            
            $this->newLine();
            $this->info('📋 تفاصيل الكتاب:');
            
            $details = [
                ['العنوان', $book->title],
                ['الوصف', $book->description ? substr($book->description, 0, 100) . '...' : 'غير متاح'],
                ['اللغة', $book->language],
                ['الحالة', $book->status],
                ['عدد الأجزاء', $book->volumes->count()],
                ['عدد الفصول', $book->chapters->count()],
                ['عدد الصفحات', $book->pages->count()],
                ['تاريخ الإنشاء', $book->created_at->format('Y-m-d H:i:s')]
            ];
            
            $this->table(['المعلومة', 'القيمة'], $details);
            
        } catch (\Exception $e) {
            $this->warn('⚠️ تعذر عرض تفاصيل الكتاب: ' . $e->getMessage());
        }
    }
}