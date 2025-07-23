<?php

namespace App\Providers;

use App\Services\BokConverterService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class BokConverterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // تسجيل خدمة BokConverter
        $this->app->singleton(BokConverterService::class, function ($app) {
            return new BokConverterService();
        });
        
        // تسجيل التكوين
        $this->mergeConfigFrom(
            __DIR__.'/../../config/bok_converter.php',
            'bok_converter'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // نشر ملف التكوين
        $this->publishes([
            __DIR__.'/../../config/bok_converter.php' => config_path('bok_converter.php'),
        ], 'bok-converter-config');
        
        // إنشاء المجلدات المطلوبة
        $this->createRequiredDirectories();
        
        // تسجيل الأوامر
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\ConvertBokCommand::class,
            ]);
        }
        
        // تسجيل الماكرو للتحقق من ملفات BOK
        File::macro('isBokFile', function ($path) {
            return File::exists($path) && 
                   str_ends_with(strtolower($path), '.bok') &&
                   $this->isValidBokFile($path);
        });
    }
    
    /**
     * إنشاء المجلدات المطلوبة
     */
    private function createRequiredDirectories(): void
    {
        $directories = [
            config('bok_converter.temp_directory'),
            config('bok_converter.backup.backup_directory'),
            storage_path('app/bok-imports'),
            storage_path('logs'),
        ];
        
        foreach ($directories as $directory) {
            if ($directory && !File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
        }
    }
    
    /**
     * التحقق من صحة ملف BOK
     */
    private function isValidBokFile(string $path): bool
    {
        try {
            // قراءة أول 100 بايت للتحقق من التوقيع
            $handle = fopen($path, 'rb');
            if (!$handle) {
                return false;
            }
            
            $header = fread($handle, 100);
            fclose($handle);
            
            // التحقق من وجود توقيع Jet DB
            return strpos($header, 'Standard Jet DB') !== false;
            
        } catch (\Exception $e) {
            return false;
        }
    }
}