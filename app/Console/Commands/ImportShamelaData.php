<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookSection;
use App\Models\Publisher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportShamelaData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shamela:import 
                            {--file=shamela_data.json : ملف البيانات المستخرجة}
                            {--categories-only : استيراد الأقسام فقط}
                            {--books-only : استيراد الكتب فقط}
                            {--authors-only : استيراد المؤلفين فقط}
                            {--dry-run : تشغيل تجريبي بدون حفظ}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'استيراد البيانات المستخرجة من موقع shamela.ws إلى قاعدة البيانات';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->option('file');
        $isDryRun = $this->option('dry-run');
        
        if (!file_exists($filePath)) {
            $this->error("ملف البيانات غير موجود: {$filePath}");
            return 1;
        }

        $this->info('بدء عملية استيراد البيانات من shamela.ws...');
        
        try {
            $data = json_decode(file_get_contents($filePath), true);
            
            if (!$data) {
                $this->error('فشل في قراءة ملف البيانات أو الملف فارغ');
                return 1;
            }

            DB::beginTransaction();

            $stats = [
                'categories' => 0,
                'authors' => 0,
                'books' => 0,
                'errors' => 0
            ];

            // استيراد الأقسام
            if (!$this->option('books-only') && !$this->option('authors-only')) {
                $stats['categories'] = $this->importCategories($data['categories'] ?? [], $isDryRun);
            }

            // استيراد المؤلفين
            if (!$this->option('categories-only') && !$this->option('books-only')) {
                $stats['authors'] = $this->importAuthors($data['authors'] ?? [], $isDryRun);
            }

            // استيراد الكتب
            if (!$this->option('categories-only') && !$this->option('authors-only')) {
                $stats['books'] = $this->importBooks($data['books'] ?? [], $isDryRun);
            }

            if ($isDryRun) {
                DB::rollBack();
                $this->info('تم تشغيل العملية في الوضع التجريبي - لم يتم حفظ أي بيانات');
            } else {
                DB::commit();
                $this->info('تم حفظ البيانات بنجاح');
            }

            $this->displayStats($stats);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('خطأ في عملية الاستيراد: ' . $e->getMessage());
            Log::error('Shamela import error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return 1;
        }

        return 0;
    }

    /**
     * استيراد الأقسام
     */
    private function importCategories(array $categories, bool $isDryRun): int
    {
        $this->info('استيراد الأقسام...');
        $imported = 0;
        
        $progressBar = $this->output->createProgressBar(count($categories));
        $progressBar->start();

        foreach ($categories as $categoryData) {
            try {
                $slug = $this->createSlug($categoryData['name']);
                
                $existingCategory = BookSection::where('slug', $slug)->first();
                
                if (!$existingCategory) {
                    if (!$isDryRun) {
                        BookSection::create([
                            'name' => $categoryData['name'],
                            'description' => 'قسم مستورد من المكتبة الشاملة',
                            'slug' => $slug,
                            'is_active' => true,
                            'sort_order' => $imported + 1,
                        ]);
                    }
                    $imported++;
                }
                
            } catch (\Exception $e) {
                $this->warn("خطأ في استيراد القسم: {$categoryData['name']} - {$e->getMessage()}");
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine();
        
        return $imported;
    }

    /**
     * استيراد المؤلفين
     */
    private function importAuthors(array $authors, bool $isDryRun): int
    {
        $this->info('استيراد المؤلفين...');
        $imported = 0;
        
        $progressBar = $this->output->createProgressBar(count($authors));
        $progressBar->start();

        foreach ($authors as $authorData) {
            try {
                $nameParts = $this->parseAuthorName($authorData['name']);
                
                $existingAuthor = Author::where('fname', $nameParts['fname'])
                    ->where('lname', $nameParts['lname'])
                    ->first();
                
                if (!$existingAuthor) {
                    if (!$isDryRun) {
                        Author::create([
                            'fname' => $nameParts['fname'],
                            'mname' => $nameParts['mname'],
                            'lname' => $nameParts['lname'],
                            'biography' => 'مؤلف مستورد من المكتبة الشاملة',
                        ]);
                    }
                    $imported++;
                }
                
            } catch (\Exception $e) {
                $this->warn("خطأ في استيراد المؤلف: {$authorData['name']} - {$e->getMessage()}");
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine();
        
        return $imported;
    }

    /**
     * استيراد الكتب
     */
    private function importBooks(array $books, bool $isDryRun): int
    {
        $this->info('استيراد الكتب...');
        $imported = 0;
        
        $progressBar = $this->output->createProgressBar(count($books));
        $progressBar->start();

        foreach ($books as $bookData) {
            try {
                $slug = $this->createSlug($bookData['title']);
                
                $existingBook = Book::where('slug', $slug)->first();
                
                if (!$existingBook) {
                    if (!$isDryRun) {
                        // البحث عن المؤلف
                        $author = null;
                        if (!empty($bookData['author']) && $bookData['author'] !== 'مؤلف غير محدد') {
                            $nameParts = $this->parseAuthorName($bookData['author']);
                            $author = Author::where('fname', $nameParts['fname'])
                                ->where('lname', $nameParts['lname'])
                                ->first();
                        }

                        // إنشاء الكتاب
                        $book = Book::create([
                            'title' => $bookData['title'],
                            'description' => $bookData['description'] ?? '',
                            'slug' => $slug,
                            'pages_count' => $bookData['pages_count'] ?? 0,
                            'source_url' => $bookData['url'] ?? '',
                            'status' => 'published',
                            'visibility' => 'public',
                        ]);

                        // ربط المؤلف بالكتاب
                        if ($author) {
                            $book->authors()->attach($author->id, [
                                'role' => 'مؤلف',
                                'is_main' => true,
                                'display_order' => 1
                            ]);
                        }
                    }
                    $imported++;
                }
                
            } catch (\Exception $e) {
                $this->warn("خطأ في استيراد الكتاب: {$bookData['title']} - {$e->getMessage()}");
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine();
        
        return $imported;
    }

    /**
     * تحليل اسم المؤلف إلى أجزاء
     */
    private function parseAuthorName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName));
        $count = count($parts);
        
        if ($count == 1) {
            return [
                'fname' => $parts[0],
                'mname' => '',
                'lname' => ''
            ];
        } elseif ($count == 2) {
            return [
                'fname' => $parts[0],
                'mname' => '',
                'lname' => $parts[1]
            ];
        } else {
            return [
                'fname' => $parts[0],
                'mname' => implode(' ', array_slice($parts, 1, -1)),
                'lname' => end($parts)
            ];
        }
    }

    /**
     * إنشاء slug من النص العربي
     */
    private function createSlug(string $text): string
    {
        // إزالة الرموز الخاصة والمسافات الزائدة
        $slug = preg_replace('/[^\p{L}\p{N}\s-]/u', '', trim($text));
        $slug = preg_replace('/[-\s]+/', '-', $slug);
        return Str::lower($slug);
    }

    /**
     * عرض إحصائيات الاستيراد
     */
    private function displayStats(array $stats): void
    {
        $this->newLine();
        $this->info('=== إحصائيات الاستيراد ===');
        $this->line("الأقسام المستوردة: {$stats['categories']}");
        $this->line("المؤلفين المستوردين: {$stats['authors']}");
        $this->line("الكتب المستوردة: {$stats['books']}");
        
        if ($stats['errors'] > 0) {
            $this->warn("عدد الأخطاء: {$stats['errors']}");
        }
        
        $this->newLine();
    }
}