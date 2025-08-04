<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedBookSystem extends Command
{
    /**
     * اسم الأمر وشكله في سطر الأوامر
     */
    protected $signature = 'books:seed {--force : إجبار تشغيل الأمر حتى في بيئة الإنتاج}';

    /**
     * وصف الأمر
     */
    protected $description = 'تشغيل seeder لنظام إدارة الكتب - إضافة بيانات تجريبية للأقسام والكتب والمؤلفين';

    /**
     * تشغيل الأمر
     */
    public function handle()
    {
        // التحقق من البيئة
        if (app()->environment('production') && !$this->option('force')) {
            $this->error('❌ لا يمكن تشغيل هذا الأمر في بيئة الإنتاج بدون --force flag');
            return self::FAILURE;
        }

        $this->info('🚀 بدء تشغيل seeder نظام إدارة الكتب...');
        $this->newLine();

        // عرض تحذير
        if (!$this->confirm('⚠️  هذا الأمر سيضيف بيانات تجريبية لقاعدة البيانات. هل تريد المتابعة؟')) {
            $this->warn('تم إلغاء العملية بواسطة المستخدم');
            return 1; // Exit code for cancelled
        }

        try {
            // تشغيل الـ seeder
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\BookSystemSeeder'
            ]);

            $this->newLine();
            $this->info('✅ تم إنهاء seeder نظام إدارة الكتب بنجاح!');
            $this->newLine();
            
            // إظهار إحصائيات
            $this->displayStats();
            
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ حدث خطأ أثناء تشغيل الـ seeder:');
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * عرض إحصائيات البيانات المضافة
     */
    private function displayStats(): void
    {
        $sectionsCount = \App\Models\BookSection::count();
        $booksCount = \App\Models\Book::count();
        $authorsCount = \App\Models\Author::count();

        $this->table(['العنصر', 'العدد'], [
            ['أقسام الكتب', $sectionsCount],
            ['الكتب', $booksCount],
            ['المؤلفون', $authorsCount]
        ]);

        $this->info('📊 إحصائيات قاعدة البيانات:');
        $this->line("- 📚 عدد أقسام الكتب: {$sectionsCount}");
        $this->line("- 📖 عدد الكتب: {$booksCount}");
        $this->line("- 👤 عدد المؤلفين: {$authorsCount}");
        
        $this->newLine();
        $this->info('🔗 يمكنك الآن زيارة الموقع لمشاهدة البيانات:');
        $this->line('- الصفحة الرئيسية: ' . url('/'));
        $this->line('- صفحة الأقسام: ' . url('/categories'));
        $this->line('- جميع الكتب: ' . url('/show-all?type=books'));
        $this->line('- جميع المؤلفين: ' . url('/show-all?type=authors'));
    }
}