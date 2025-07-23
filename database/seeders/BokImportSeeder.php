<?php

namespace Database\Seeders;

use App\Models\BokImport;
use App\Models\User;
use App\Models\Book;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BokImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء مستخدم للاختبار إذا لم يكن موجوداً
        $testUser = User::firstOrCreate(
            ['email' => 'admin@bms.test'],
            [
                'name' => 'مدير النظام',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // إنشاء عمليات استيراد مكتملة مع كتب
        $completedImports = BokImport::factory()
            ->count(15)
            ->completed()
            ->withBook()
            ->create([
                'user_id' => $testUser->id,
            ]);

        // إنشاء عمليات استيراد فاشلة
        BokImport::factory()
            ->count(5)
            ->failed()
            ->create([
                'user_id' => $testUser->id,
            ]);

        // إنشاء عمليات استيراد قيد المعالجة
        BokImport::factory()
            ->count(3)
            ->processing()
            ->create([
                'user_id' => $testUser->id,
            ]);

        // إنشاء عمليات استيراد في الانتظار
        BokImport::factory()
            ->count(7)
            ->pending()
            ->create([
                'user_id' => $testUser->id,
            ]);

        // إنشاء عمليات استيراد ملغية
        BokImport::factory()
            ->count(2)
            ->cancelled()
            ->create([
                'user_id' => $testUser->id,
            ]);

        // إنشاء عمليات استيراد لملفات كبيرة
        BokImport::factory()
            ->count(3)
            ->largeFile()
            ->completed()
            ->withBook()
            ->withBackup()
            ->create([
                'user_id' => $testUser->id,
            ]);

        // إنشاء عمليات استيراد من CLI
        BokImport::factory()
            ->count(4)
            ->fromCli()
            ->completed()
            ->withBook()
            ->create([
                'user_id' => $testUser->id,
            ]);

        // إنشاء عمليات استيراد من API
        BokImport::factory()
            ->count(2)
            ->fromApi()
            ->completed()
            ->withBook()
            ->create([
                'user_id' => $testUser->id,
            ]);

        // إنشاء عمليات استيراد مميزة
        BokImport::factory()
            ->count(5)
            ->completed()
            ->withBook()
            ->create([
                'user_id' => $testUser->id,
                'is_featured' => true,
                'is_public' => true,
                'allow_download' => true,
                'allow_search' => true,
            ]);

        // إنشاء عمليات استيراد بأخطاء متنوعة
        $errorMessages = [
            'فشل في قراءة ملف BOK - الملف تالف',
            'خطأ في تحليل هيكل قاعدة البيانات',
            'نفاد الذاكرة أثناء معالجة الملف الكبير',
            'خطأ في الاتصال بقاعدة البيانات',
            'ملف BOK غير مدعوم - إصدار قديم',
            'انتهت مهلة المعالجة - الملف كبير جداً',
            'خطأ في ترميز النصوص العربية',
            'فشل في إنشاء الفهارس',
            'خطأ في استخراج معلومات الكتاب',
            'مساحة التخزين غير كافية'
        ];

        foreach ($errorMessages as $index => $errorMessage) {
            BokImport::factory()
                ->failed()
                ->create([
                    'user_id' => $testUser->id,
                    'error_message' => $errorMessage,
                    'title' => 'كتاب تجريبي ' . ($index + 1),
                    'conversion_log' => [
                        [
                            'timestamp' => now()->subHours(2)->toISOString(),
                            'level' => 'info',
                            'message' => 'بدء تحليل ملف BOK'
                        ],
                        [
                            'timestamp' => now()->subHours(1)->toISOString(),
                            'level' => 'warning',
                            'message' => 'تحذير: حجم الملف كبير'
                        ],
                        [
                            'timestamp' => now()->subMinutes(30)->toISOString(),
                            'level' => 'error',
                            'message' => $errorMessage
                        ]
                    ]
                ]);
        }

        // إنشاء عمليات استيراد بحالات مختلفة للإحصائيات
        $this->createStatisticsData($testUser);

        $this->command->info('تم إنشاء بيانات تجريبية لاستيراد BOK بنجاح!');
        $this->command->info('إجمالي عمليات الاستيراد: ' . BokImport::count());
        $this->command->info('العمليات المكتملة: ' . BokImport::completed()->count());
        $this->command->info('العمليات الفاشلة: ' . BokImport::failed()->count());
        $this->command->info('العمليات قيد المعالجة: ' . BokImport::processing()->count());
        $this->command->info('العمليات في الانتظار: ' . BokImport::pending()->count());
    }

    /**
     * إنشاء بيانات للإحصائيات
     */
    private function createStatisticsData(User $user): void
    {
        // عمليات استيراد شهرية للإحصائيات
        for ($month = 1; $month <= 12; $month++) {
            $count = rand(5, 25);
            
            BokImport::factory()
                ->count($count)
                ->completed()
                ->withBook()
                ->create([
                    'user_id' => $user->id,
                    'created_at' => now()->month($month)->day(rand(1, 28)),
                    'completed_at' => now()->month($month)->day(rand(1, 28))->addHours(rand(1, 24)),
                ]);
        }

        // عمليات استيراد يومية للأسبوع الماضي
        for ($day = 7; $day >= 1; $day--) {
            $count = rand(1, 8);
            
            BokImport::factory()
                ->count($count)
                ->completed()
                ->withBook()
                ->create([
                    'user_id' => $user->id,
                    'created_at' => now()->subDays($day),
                    'completed_at' => now()->subDays($day)->addHours(rand(1, 12)),
                ]);
        }

        // عمليات استيراد بأحجام ملفات متنوعة
        $fileSizes = [
            1 * 1024 * 1024,      // 1MB
            5 * 1024 * 1024,      // 5MB
            10 * 1024 * 1024,     // 10MB
            25 * 1024 * 1024,     // 25MB
            50 * 1024 * 1024,     // 50MB
            100 * 1024 * 1024,    // 100MB
            200 * 1024 * 1024,    // 200MB
        ];

        foreach ($fileSizes as $size) {
            BokImport::factory()
                ->count(rand(2, 5))
                ->completed()
                ->withBook()
                ->create([
                    'user_id' => $user->id,
                    'file_size' => $size,
                ]);
        }

        // عمليات استيراد بأوقات معالجة متنوعة
        $processingTimes = [30, 60, 120, 300, 600, 1200, 1800, 3600]; // ثواني

        foreach ($processingTimes as $time) {
            BokImport::factory()
                ->count(rand(1, 3))
                ->completed()
                ->withBook()
                ->create([
                    'user_id' => $user->id,
                    'processing_time' => $time,
                ]);
        }

        // عمليات استيراد بلغات مختلفة (معظمها عربي)
        $languages = ['ar' => 85, 'en' => 10, 'ur' => 3, 'fa' => 2];

        foreach ($languages as $lang => $percentage) {
            $count = intval(50 * $percentage / 100);
            
            BokImport::factory()
                ->count($count)
                ->completed()
                ->withBook()
                ->create([
                    'user_id' => $user->id,
                    'language' => $lang,
                ]);
        }
    }
}