<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Volume;
use App\Models\Chapter;
use App\Models\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class BokConverterService
{
    private $mdbToolsPath;
    private $tempDir;
    
    public function __construct()
    {
        $this->mdbToolsPath = config('app.mdb_tools_path', 'mdb-tools');
        $this->tempDir = storage_path('app/temp/bok_conversion');
        
        // إنشاء مجلد مؤقت إذا لم يكن موجوداً
        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }
    
    /**
     * تحويل ملف .bok إلى قاعدة البيانات
     */
    public function convertBokFile(string $bokFilePath): array
    {
        try {
            // التحقق من وجود الملف
            if (!file_exists($bokFilePath)) {
                throw new Exception("ملف .bok غير موجود: {$bokFilePath}");
            }
            
            Log::info("بدء تحويل ملف .bok: {$bokFilePath}");
            
            // 1. تحليل هيكل قاعدة البيانات
            $structure = $this->analyzeBokStructure($bokFilePath);
            
            // 2. استخراج البيانات
            $bookData = $this->extractBookData($bokFilePath, $structure);
            
            // 3. تطبيع البيانات
            $normalizedData = $this->normalizeBookData($bookData);
            
            // 4. إدراج في قاعدة البيانات
            $book = $this->insertBookIntoDatabase($normalizedData);
            
            Log::info("تم تحويل الكتاب بنجاح: {$book->title}");
            
            return [
                'success' => true,
                'book_id' => $book->id,
                'title' => $book->title,
                'message' => 'تم تحويل الكتاب بنجاح'
            ];
            
        } catch (Exception $e) {
            Log::error("خطأ في تحويل ملف .bok: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * تحليل هيكل قاعدة بيانات .bok
     */
    private function analyzeBokStructure(string $bokFilePath): array
    {
        $structure = [];
        
        // الحصول على قائمة الجداول
        $tablesOutput = $this->executeMdbCommand('mdb-tables', [$bokFilePath]);
        $tables = array_filter(explode(' ', trim($tablesOutput)));
        
        foreach ($tables as $table) {
            if (empty($table) || strpos($table, 'MSys') === 0) {
                continue; // تجاهل جداول النظام
            }
            
            // الحصول على هيكل الجدول
            $schemaOutput = $this->executeMdbCommand('mdb-schema', [$bokFilePath, '-T', $table]);
            $structure[$table] = $this->parseTableSchema($schemaOutput);
        }
        
        return $structure;
    }
    
    /**
     * استخراج البيانات من ملف .bok
     */
    private function extractBookData(string $bokFilePath, array $structure): array
    {
        $bookData = [];
        
        foreach ($structure as $tableName => $tableInfo) {
            try {
                // تصدير بيانات الجدول إلى CSV
                $csvOutput = $this->executeMdbCommand('mdb-export', [$bokFilePath, $tableName]);
                $bookData[$tableName] = $this->parseCsvData($csvOutput);
                
            } catch (Exception $e) {
                Log::warning("تعذر استخراج بيانات الجدول {$tableName}: " . $e->getMessage());
                $bookData[$tableName] = [];
            }
        }
        
        return $bookData;
    }
    
    /**
     * تطبيع البيانات لتتوافق مع نموذج BMS
     */
    private function normalizeBookData(array $bookData): array
    {
        $normalized = [
            'book' => [],
            'volumes' => [],
            'chapters' => [],
            'pages' => []
        ];
        
        // البحث عن جدول معلومات الكتاب
        $bookInfoTable = $this->findBookInfoTable($bookData);
        if ($bookInfoTable) {
            $normalized['book'] = $this->normalizeBookInfo($bookData[$bookInfoTable]);
        }
        
        // البحث عن جدول المحتوى
        $contentTable = $this->findContentTable($bookData);
        if ($contentTable) {
            $content = $this->normalizeContent($bookData[$contentTable]);
            $normalized['volumes'] = $content['volumes'];
            $normalized['chapters'] = $content['chapters'];
            $normalized['pages'] = $content['pages'];
        }
        
        return $normalized;
    }
    
    /**
     * إدراج الكتاب في قاعدة البيانات
     */
    private function insertBookIntoDatabase(array $normalizedData): Book
    {
        return DB::transaction(function () use ($normalizedData) {
            // إنشاء الكتاب
            $book = Book::create($normalizedData['book']);
            
            // إنشاء الأجزاء
            foreach ($normalizedData['volumes'] as $volumeData) {
                $volumeData['book_id'] = $book->id;
                $volume = Volume::create($volumeData);
                
                // إنشاء الفصول للجزء
                $volumeChapters = array_filter($normalizedData['chapters'], function($chapter) use ($volume) {
                    return isset($chapter['volume_number']) && $chapter['volume_number'] == $volume->number;
                });
                
                foreach ($volumeChapters as $chapterData) {
                    $chapterData['book_id'] = $book->id;
                    $chapterData['volume_id'] = $volume->id;
                    unset($chapterData['volume_number']);
                    $chapter = Chapter::create($chapterData);
                    
                    // إنشاء الصفحات للفصل
                    $chapterPages = array_filter($normalizedData['pages'], function($page) use ($chapter) {
                        return isset($page['chapter_number']) && $page['chapter_number'] == $chapter->chapter_number;
                    });
                    
                    foreach ($chapterPages as $pageData) {
                        $pageData['book_id'] = $book->id;
                        $pageData['volume_id'] = $volume->id;
                        $pageData['chapter_id'] = $chapter->id;
                        unset($pageData['chapter_number']);
                        Page::create($pageData);
                    }
                }
            }
            
            return $book;
        });
    }
    
    /**
     * تنفيذ أمر mdb-tools
     */
    private function executeMdbCommand(string $command, array $args): string
    {
        $fullCommand = $command . ' ' . implode(' ', array_map('escapeshellarg', $args));
        
        $output = shell_exec($fullCommand);
        
        if ($output === null) {
            throw new Exception("فشل في تنفيذ الأمر: {$fullCommand}");
        }
        
        return $output;
    }
    
    /**
     * تحليل هيكل الجدول من مخرجات mdb-schema
     */
    private function parseTableSchema(string $schemaOutput): array
    {
        $columns = [];
        $lines = explode("\n", $schemaOutput);
        
        foreach ($lines as $line) {
            if (preg_match('/\s*\[([^\]]+)\]\s+([^,\s]+)/', $line, $matches)) {
                $columns[] = [
                    'name' => $matches[1],
                    'type' => $matches[2]
                ];
            }
        }
        
        return $columns;
    }
    
    /**
     * تحليل بيانات CSV
     */
    private function parseCsvData(string $csvOutput): array
    {
        $lines = explode("\n", trim($csvOutput));
        if (empty($lines)) {
            return [];
        }
        
        $headers = str_getcsv(array_shift($lines));
        $data = [];
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            $row = str_getcsv($line);
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }
        
        return $data;
    }
    
    /**
     * البحث عن جدول معلومات الكتاب
     */
    private function findBookInfoTable(array $bookData): ?string
    {
        $possibleNames = ['Book', 'BookInfo', 'Books', 'Main', 'Info'];
        
        foreach ($possibleNames as $name) {
            if (isset($bookData[$name])) {
                return $name;
            }
        }
        
        // البحث في أسماء الجداول عن كلمات مفتاحية
        foreach (array_keys($bookData) as $tableName) {
            if (stripos($tableName, 'book') !== false || 
                stripos($tableName, 'info') !== false ||
                stripos($tableName, 'main') !== false) {
                return $tableName;
            }
        }
        
        return null;
    }
    
    /**
     * البحث عن جدول المحتوى
     */
    private function findContentTable(array $bookData): ?string
    {
        $possibleNames = ['Content', 'Text', 'Pages', 'Chapters', 'Data'];
        
        foreach ($possibleNames as $name) {
            if (isset($bookData[$name])) {
                return $name;
            }
        }
        
        // البحث في أسماء الجداول
        foreach (array_keys($bookData) as $tableName) {
            if (stripos($tableName, 'content') !== false || 
                stripos($tableName, 'text') !== false ||
                stripos($tableName, 'page') !== false ||
                stripos($tableName, 'chapter') !== false) {
                return $tableName;
            }
        }
        
        return null;
    }
    
    /**
     * تطبيع معلومات الكتاب
     */
    private function normalizeBookInfo(array $bookInfo): array
    {
        if (empty($bookInfo)) {
            return [
                'title' => 'كتاب بدون عنوان',
                'description' => '',
                'language' => 'ar',
                'status' => 'published'
            ];
        }
        
        $firstRow = $bookInfo[0];
        
        return [
            'title' => $this->extractField($firstRow, ['title', 'name', 'book_name', 'Title', 'Name']) ?: 'كتاب بدون عنوان',
            'description' => $this->extractField($firstRow, ['description', 'desc', 'info', 'Description']) ?: '',
            'language' => 'ar',
            'status' => 'published'
        ];
    }
    
    /**
     * تطبيع المحتوى
     */
    private function normalizeContent(array $content): array
    {
        $volumes = [];
        $chapters = [];
        $pages = [];
        
        $currentVolume = 1;
        $currentChapter = 1;
        $currentPage = 1;
        
        foreach ($content as $row) {
            // استخراج محتوى الصفحة
            $pageContent = $this->extractField($row, ['content', 'text', 'body', 'Content', 'Text']);
            
            if (!empty($pageContent)) {
                $pages[] = [
                    'page_number' => $currentPage,
                    'content' => $this->cleanArabicText($pageContent),
                    'chapter_number' => $currentChapter
                ];
                
                $currentPage++;
            }
            
            // تحديد الفصول والأجزاء بناءً على المحتوى
            if ($this->isChapterStart($pageContent)) {
                $chapters[] = [
                    'chapter_number' => $currentChapter,
                    'title' => $this->extractChapterTitle($pageContent),
                    'volume_number' => $currentVolume,
                    'page_start' => $currentPage - 1,
                    'page_end' => $currentPage - 1
                ];
                
                $currentChapter++;
            }
        }
        
        // إنشاء جزء افتراضي إذا لم توجد أجزاء
        if (empty($volumes)) {
            $volumes[] = [
                'number' => 1,
                'title' => 'الجزء الأول',
                'page_start' => 1,
                'page_end' => count($pages)
            ];
        }
        
        // إنشاء فصل افتراضي إذا لم توجد فصول
        if (empty($chapters)) {
            $chapters[] = [
                'chapter_number' => 1,
                'title' => 'الفصل الأول',
                'volume_number' => 1,
                'page_start' => 1,
                'page_end' => count($pages)
            ];
        }
        
        return [
            'volumes' => $volumes,
            'chapters' => $chapters,
            'pages' => $pages
        ];
    }
    
    /**
     * استخراج حقل من صف البيانات
     */
    private function extractField(array $row, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            if (isset($row[$key]) && !empty($row[$key])) {
                return $row[$key];
            }
        }
        
        return null;
    }
    
    /**
     * تنظيف النص العربي
     */
    private function cleanArabicText(string $text): string
    {
        // إزالة الأحرف غير المرغوبة
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        // تنظيف المسافات الزائدة
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }
    
    /**
     * تحديد ما إذا كان النص بداية فصل
     */
    private function isChapterStart(string $text): bool
    {
        $chapterIndicators = [
            'الفصل', 'الباب', 'المبحث', 'القسم', 'الجزء',
            'فصل', 'باب', 'مبحث', 'قسم', 'جزء'
        ];
        
        foreach ($chapterIndicators as $indicator) {
            if (stripos($text, $indicator) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * استخراج عنوان الفصل
     */
    private function extractChapterTitle(string $text): string
    {
        // استخراج أول سطر كعنوان
        $lines = explode("\n", $text);
        $title = trim($lines[0]);
        
        // تحديد طول العنوان
        if (strlen($title) > 100) {
            $title = substr($title, 0, 100) . '...';
        }
        
        return $title ?: 'فصل بدون عنوان';
    }
    
    /**
     * الحصول على معلومات التقدم
     */
    public function getConversionProgress(string $sessionId): array
    {
        // يمكن تطوير هذه الوظيفة لتتبع تقدم التحويل
        return [
            'progress' => 0,
            'status' => 'waiting',
            'message' => 'في انتظار بدء التحويل'
        ];
    }
}