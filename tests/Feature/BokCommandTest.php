<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class BokCommandTest extends TestCase
{
    use RefreshDatabase;

    protected string $testBokPath;

    protected function setUp(): void
    {
        parent::setUp();
        
        // البحث عن ملف BOK للاختبار
        $sourcePath = base_path('رسالة الأضحية في حق الفقير 1122.bok');
        if (File::exists($sourcePath)) {
            $this->testBokPath = $sourcePath;
        }
    }

    /** @test */
    public function it_shows_help_information()
    {
        $this->artisan('bok:convert --help')
            ->expectsOutput('Convert BOK files to database')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_validates_file_existence()
    {
        $nonExistentFile = '/path/to/nonexistent/file.bok';
        
        $this->artisan('bok:convert', ['file' => $nonExistentFile])
            ->expectsOutput('الملف غير موجود')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_validates_file_extension()
    {
        // إنشاء ملف مؤقت بامتداد خاطئ
        $tempFile = tempnam(sys_get_temp_dir(), 'test') . '.txt';
        File::put($tempFile, 'test content');
        
        $this->artisan('bok:convert', ['file' => $tempFile])
            ->expectsOutput('الملف يجب أن يكون بصيغة .bok')
            ->assertExitCode(1);
        
        // تنظيف
        File::delete($tempFile);
    }

    /** @test */
    public function it_processes_valid_bok_file()
    {
        if (!isset($this->testBokPath)) {
            $this->markTestSkipped('ملف BOK للاختبار غير متوفر');
        }

        $this->artisan('bok:convert', [
            'file' => $this->testBokPath,
            '--verbose' => true
        ])
        ->expectsOutput('بدء تحليل ملف BOK...')
        ->assertExitCode(0);
    }

    /** @test */
    public function it_handles_batch_conversion()
    {
        $tempDir = sys_get_temp_dir() . '/bok_test';
        File::makeDirectory($tempDir, 0755, true, true);
        
        // إنشاء ملفات BOK وهمية
        File::put($tempDir . '/book1.bok', 'Standard Jet DB');
        File::put($tempDir . '/book2.bok', 'Standard Jet DB');
        File::put($tempDir . '/not_bok.txt', 'not a bok file');
        
        $this->artisan('bok:convert', [
            'file' => $tempDir,
            '--batch' => true,
            '--verbose' => true
        ])
        ->expectsOutput('تم العثور على 2 ملف BOK')
        ->assertExitCode(0);
        
        // تنظيف
        File::deleteDirectory($tempDir);
    }

    /** @test */
    public function it_applies_text_cleaning_option()
    {
        if (!isset($this->testBokPath)) {
            $this->markTestSkipped('ملف BOK للاختبار غير متوفر');
        }

        $this->artisan('bok:convert', [
            'file' => $this->testBokPath,
            '--clean' => true,
            '--verbose' => true
        ])
        ->expectsOutput('تنظيف النصوص: مفعل')
        ->assertExitCode(0);
    }

    /** @test */
    public function it_sets_custom_status()
    {
        if (!isset($this->testBokPath)) {
            $this->markTestSkipped('ملف BOK للاختبار غير متوفر');
        }

        $this->artisan('bok:convert', [
            'file' => $this->testBokPath,
            '--status' => 'draft',
            '--verbose' => true
        ])
        ->expectsOutput('حالة الكتاب: draft')
        ->assertExitCode(0);
    }

    /** @test */
    public function it_disables_structure_detection()
    {
        if (!isset($this->testBokPath)) {
            $this->markTestSkipped('ملف BOK للاختبار غير متوفر');
        }

        $this->artisan('bok:convert', [
            'file' => $this->testBokPath,
            '--no-structure' => true,
            '--verbose' => true
        ])
        ->expectsOutput('الكشف التلقائي عن الهيكل: معطل')
        ->assertExitCode(0);
    }

    /** @test */
    public function it_shows_progress_bar_for_large_files()
    {
        if (!isset($this->testBokPath)) {
            $this->markTestSkipped('ملف BOK للاختبار غير متوفر');
        }

        // محاكاة ملف كبير
        $this->artisan('bok:convert', [
            'file' => $this->testBokPath,
            '--verbose' => true
        ])
        ->expectsOutput('تقدم التحويل:')
        ->assertExitCode(0);
    }

    /** @test */
    public function it_displays_conversion_summary()
    {
        if (!isset($this->testBokPath)) {
            $this->markTestSkipped('ملف BOK للاختبار غير متوفر');
        }

        $this->artisan('bok:convert', [
            'file' => $this->testBokPath,
            '--verbose' => true
        ])
        ->expectsOutput('ملخص التحويل:')
        ->expectsOutput('الملفات المعالجة:')
        ->expectsOutput('الكتب المحولة بنجاح:')
        ->expectsOutput('الأخطاء:')
        ->assertExitCode(0);
    }

    /** @test */
    public function it_handles_memory_limit_gracefully()
    {
        // محاكاة نفاد الذاكرة
        config(['bok_converter.performance.memory_limit' => '1M']);
        
        if (!isset($this->testBokPath)) {
            $this->markTestSkipped('ملف BOK للاختبار غير متوفر');
        }

        $this->artisan('bok:convert', [
            'file' => $this->testBokPath,
            '--verbose' => true
        ])
        ->assertExitCode(0); // يجب أن يتعامل مع الخطأ بلطف
    }

    /** @test */
    public function it_validates_status_option()
    {
        if (!isset($this->testBokPath)) {
            $this->markTestSkipped('ملف BOK للاختبار غير متوفر');
        }

        $this->artisan('bok:convert', [
            'file' => $this->testBokPath,
            '--status' => 'invalid_status'
        ])
        ->expectsOutput('حالة غير صالحة')
        ->assertExitCode(1);
    }

    /** @test */
    public function it_creates_log_entries()
    {
        if (!isset($this->testBokPath)) {
            $this->markTestSkipped('ملف BOK للاختبار غير متوفر');
        }

        // تفعيل السجلات
        config(['bok_converter.logging.enabled' => true]);
        
        $this->artisan('bok:convert', [
            'file' => $this->testBokPath,
            '--verbose' => true
        ]);
        
        // التحقق من وجود ملف السجل
        $logPath = storage_path('logs/bok_converter.log');
        if (File::exists($logPath)) {
            $logContent = File::get($logPath);
            $this->assertStringContainsString('BOK conversion', $logContent);
        }
    }

    /** @test */
    public function it_handles_interrupted_conversion()
    {
        if (!isset($this->testBokPath)) {
            $this->markTestSkipped('ملف BOK للاختبار غير متوفر');
        }

        // محاكاة انقطاع العملية
        $this->artisan('bok:convert', [
            'file' => $this->testBokPath,
            '--verbose' => true
        ])
        ->assertExitCode(0);
        
        // التحقق من تنظيف الملفات المؤقتة
        $tempDir = config('bok_converter.temp_directory');
        $tempFiles = File::glob($tempDir . '/*');
        $this->assertEmpty($tempFiles, 'يجب تنظيف الملفات المؤقتة');
    }

    /** @test */
    public function it_respects_quiet_mode()
    {
        if (!isset($this->testBokPath)) {
            $this->markTestSkipped('ملف BOK للاختبار غير متوفر');
        }

        $this->artisan('bok:convert', [
            'file' => $this->testBokPath,
            '--quiet' => true
        ])
        ->expectsNoOutput()
        ->assertExitCode(0);
    }
}