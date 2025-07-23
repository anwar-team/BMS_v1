<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\BokConverterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class BokConverterTest extends TestCase
{
    use RefreshDatabase;

    protected BokConverterService $converter;
    protected string $testBokPath;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->converter = new BokConverterService();
        
        // إنشاء مجلد الاختبار
        Storage::fake('local');
        
        // نسخ ملف BOK للاختبار إذا كان موجوداً
        $sourcePath = base_path('رسالة الأضحية في حق الفقير 1122.bok');
        if (File::exists($sourcePath)) {
            $this->testBokPath = $sourcePath;
        }
    }

    /** @test */
    public function it_can_detect_bok_file_format()
    {
        if (!isset($this->testBokPath)) {
            $this->markTestSkipped('ملف BOK للاختبار غير متوفر');
        }

        $isValidBok = File::isBokFile($this->testBokPath);
        
        $this->assertTrue($isValidBok, 'يجب أن يتم التعرف على ملف BOK بشكل صحيح');
    }

    /** @test */
    public function it_can_analyze_bok_file_structure()
    {
        if (!isset($this->testBokPath)) {
            $this->markTestSkipped('ملف BOK للاختبار غير متوفر');
        }

        $analysis = $this->converter->analyzeBokFile($this->testBokPath);
        
        $this->assertIsArray($analysis);
        $this->assertArrayHasKey('tables', $analysis);
        $this->assertArrayHasKey('estimated_records', $analysis);
        $this->assertArrayHasKey('file_size', $analysis);
        $this->assertGreaterThan(0, count($analysis['tables']));
    }

    /** @test */
    public function it_can_extract_book_metadata()
    {
        if (!isset($this->testBokPath)) {
            $this->markTestSkipped('ملف BOK للاختبار غير متوفر');
        }

        $metadata = $this->converter->extractBookMetadata($this->testBokPath);
        
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('title', $metadata);
        $this->assertArrayHasKey('author', $metadata);
        $this->assertNotEmpty($metadata['title']);
    }

    /** @test */
    public function it_validates_file_before_conversion()
    {
        // اختبار ملف غير صالح
        $invalidFile = UploadedFile::fake()->create('invalid.txt', 100);
        
        $result = $this->converter->convertBokFile($invalidFile->getPathname());
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('ملف غير صالح', $result['error']);
    }

    /** @test */
    public function it_handles_large_files_appropriately()
    {
        $maxSize = config('bok_converter.max_file_size', 100 * 1024 * 1024);
        
        // إنشاء ملف كبير جداً
        $largeFile = UploadedFile::fake()->create('large.bok', ($maxSize / 1024) + 1);
        
        $result = $this->converter->convertBokFile($largeFile->getPathname());
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('حجم الملف', $result['error']);
    }

    /** @test */
    public function it_creates_necessary_directories()
    {
        $tempDir = config('bok_converter.temp_directory');
        $backupDir = config('bok_converter.backup.directory');
        
        // محاكاة عدم وجود المجلدات
        if (File::exists($tempDir)) {
            File::deleteDirectory($tempDir);
        }
        
        // تشغيل الخدمة
        $this->converter = new BokConverterService();
        
        $this->assertTrue(File::exists($tempDir));
    }

    /** @test */
    public function it_cleans_arabic_text_properly()
    {
        $dirtyText = "هذا نص عربي\x00\x01\x02 مع أحرف تحكم\t\n\r";
        $cleanText = $this->converter->cleanArabicText($dirtyText);
        
        $this->assertStringNotContainsString("\x00", $cleanText);
        $this->assertStringNotContainsString("\x01", $cleanText);
        $this->assertStringNotContainsString("\x02", $cleanText);
        $this->assertStringContainsString('هذا نص عربي', $cleanText);
    }

    /** @test */
    public function it_detects_chapter_indicators()
    {
        $indicators = config('bok_converter.chapter_indicators');
        
        foreach ($indicators as $indicator) {
            $text = "$indicator الأول: محتوى الفصل";
            $isChapter = $this->converter->isChapterTitle($text);
            
            $this->assertTrue($isChapter, "يجب التعرف على '$indicator' كمؤشر فصل");
        }
    }

    /** @test */
    public function it_handles_conversion_errors_gracefully()
    {
        // محاكاة خطأ في mdb-tools
        $nonExistentFile = '/path/to/nonexistent/file.bok';
        
        $result = $this->converter->convertBokFile($nonExistentFile);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertNotEmpty($result['error']);
    }

    /** @test */
    public function it_logs_conversion_activities()
    {
        if (!isset($this->testBokPath)) {
            $this->markTestSkipped('ملف BOK للاختبار غير متوفر');
        }

        // تفعيل السجلات المفصلة
        config(['bok_converter.logging.detailed' => true]);
        
        $this->converter->analyzeBokFile($this->testBokPath);
        
        // التحقق من وجود ملف السجل
        $logPath = storage_path('logs/bok_converter.log');
        
        if (File::exists($logPath)) {
            $logContent = File::get($logPath);
            $this->assertStringContainsString('BOK', $logContent);
        }
    }

    /** @test */
    public function it_respects_performance_settings()
    {
        $batchSize = config('bok_converter.performance.batch_size');
        $memoryLimit = config('bok_converter.performance.memory_limit');
        $maxExecutionTime = config('bok_converter.performance.max_execution_time');
        
        $this->assertIsInt($batchSize);
        $this->assertGreaterThan(0, $batchSize);
        $this->assertIsString($memoryLimit);
        $this->assertIsInt($maxExecutionTime);
        $this->assertGreaterThan(0, $maxExecutionTime);
    }

    /** @test */
    public function it_can_create_backup_if_enabled()
    {
        config(['bok_converter.backup.enabled' => true]);
        
        if (!isset($this->testBokPath)) {
            $this->markTestSkipped('ملف BOK للاختبار غير متوفر');
        }

        $backupPath = $this->converter->createBackup($this->testBokPath);
        
        if ($backupPath) {
            $this->assertTrue(File::exists($backupPath));
            
            // تنظيف
            File::delete($backupPath);
        }
    }

    protected function tearDown(): void
    {
        // تنظيف الملفات المؤقتة
        $tempDir = config('bok_converter.temp_directory');
        if (File::exists($tempDir)) {
            File::deleteDirectory($tempDir);
        }
        
        parent::tearDown();
    }
}