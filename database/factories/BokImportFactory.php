<?php

namespace Database\Factories;

use App\Models\BokImport;
use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BokImport>
 */
class BokImportFactory extends Factory
{
    protected $model = BokImport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $arabicTitles = [
            'صحيح البخاري',
            'صحيح مسلم',
            'سنن أبي داود',
            'جامع الترمذي',
            'سنن النسائي',
            'سنن ابن ماجه',
            'موطأ مالك',
            'مسند أحمد',
            'تفسير الطبري',
            'تفسير ابن كثير',
            'البداية والنهاية',
            'سير أعلام النبلاء',
            'فتح الباري',
            'شرح النووي على مسلم',
            'المغني لابن قدامة',
            'الأم للشافعي',
            'المدونة الكبرى',
            'بدائع الصنائع',
            'إحياء علوم الدين',
            'مجموع فتاوى ابن تيمية'
        ];

        $arabicAuthors = [
            'الإمام البخاري',
            'الإمام مسلم',
            'أبو داود السجستاني',
            'الترمذي',
            'النسائي',
            'ابن ماجه',
            'الإمام مالك',
            'الإمام أحمد',
            'الطبري',
            'ابن كثير',
            'ابن الأثير',
            'الذهبي',
            'ابن حجر العسقلاني',
            'النووي',
            'ابن قدامة',
            'الإمام الشافعي',
            'سحنون',
            'الكاساني',
            'الغزالي',
            'ابن تيمية'
        ];

        $title = $this->faker->randomElement($arabicTitles);
        $author = $this->faker->randomElement($arabicAuthors);
        $filename = Str::slug($title, '_') . '.bok';
        
        return [
            'original_filename' => $filename,
            'file_path' => 'bok-imports/' . $filename,
            'file_size' => $this->faker->numberBetween(1024 * 1024, 100 * 1024 * 1024), // 1MB to 100MB
            'file_hash' => hash('sha256', $filename . time()),
            'title' => $title,
            'author' => $author,
            'description' => $this->faker->optional()->paragraph(),
            'language' => 'ar',
            'volumes_count' => $this->faker->numberBetween(1, 20),
            'chapters_count' => $this->faker->numberBetween(10, 500),
            'pages_count' => $this->faker->numberBetween(100, 5000),
            'estimated_words' => $this->faker->numberBetween(50000, 2000000),
            'status' => $this->faker->randomElement([
                BokImport::STATUS_PENDING,
                BokImport::STATUS_PROCESSING,
                BokImport::STATUS_COMPLETED,
                BokImport::STATUS_FAILED,
                BokImport::STATUS_CANCELLED
            ]),
            'conversion_options' => [
                'auto_detect_structure' => $this->faker->boolean(),
                'clean_text' => $this->faker->boolean(),
                'create_indexes' => $this->faker->boolean(),
                'language' => 'ar',
                'import_status' => 'published'
            ],
            'analysis_result' => [
                'tables' => ['Book_Info', 'Content', 'Indexes', 'Sections'],
                'estimated_records' => $this->faker->numberBetween(1000, 50000),
                'file_size' => $this->faker->numberBetween(1024 * 1024, 100 * 1024 * 1024),
                'encoding' => 'UTF-8',
                'structure_detected' => $this->faker->boolean()
            ],
            'conversion_log' => [
                [
                    'timestamp' => now()->subMinutes(10)->toISOString(),
                    'level' => 'info',
                    'message' => 'بدء تحليل ملف BOK'
                ],
                [
                    'timestamp' => now()->subMinutes(8)->toISOString(),
                    'level' => 'info',
                    'message' => 'تم استخراج معلومات الكتاب'
                ],
                [
                    'timestamp' => now()->subMinutes(5)->toISOString(),
                    'level' => 'info',
                    'message' => 'بدء تحويل المحتوى'
                ]
            ],
            'error_message' => null,
            'book_id' => null,
            'is_featured' => $this->faker->boolean(20), // 20% chance
            'allow_download' => $this->faker->boolean(80), // 80% chance
            'allow_search' => $this->faker->boolean(90), // 90% chance
            'is_public' => $this->faker->boolean(85), // 85% chance
            'started_at' => null,
            'completed_at' => null,
            'processing_time' => null,
            'backup_path' => null,
            'backup_created' => $this->faker->boolean(30), // 30% chance
            'user_id' => User::factory(),
            'import_source' => $this->faker->randomElement([
                BokImport::SOURCE_WEB,
                BokImport::SOURCE_CLI,
                BokImport::SOURCE_API
            ]),
        ];
    }

    /**
     * حالة مكتملة
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = $this->faker->dateTimeBetween('-1 month', '-1 day');
            $completedAt = $this->faker->dateTimeBetween($startedAt, 'now');
            $processingTime = $completedAt->getTimestamp() - $startedAt->getTimestamp();
            
            return [
                'status' => BokImport::STATUS_COMPLETED,
                'started_at' => $startedAt,
                'completed_at' => $completedAt,
                'processing_time' => $processingTime,
                'book_id' => Book::factory(),
                'error_message' => null,
            ];
        });
    }

    /**
     * حالة فاشلة
     */
    public function failed(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = $this->faker->dateTimeBetween('-1 week', '-1 day');
            $completedAt = $this->faker->dateTimeBetween($startedAt, 'now');
            $processingTime = $completedAt->getTimestamp() - $startedAt->getTimestamp();
            
            $errorMessages = [
                'فشل في قراءة ملف BOK',
                'خطأ في تحليل هيكل الملف',
                'نفاد الذاكرة أثناء المعالجة',
                'خطأ في الاتصال بقاعدة البيانات',
                'ملف تالف أو غير مدعوم',
                'انتهت مهلة المعالجة',
                'خطأ في ترميز النصوص العربية'
            ];
            
            return [
                'status' => BokImport::STATUS_FAILED,
                'started_at' => $startedAt,
                'completed_at' => $completedAt,
                'processing_time' => $processingTime,
                'book_id' => null,
                'error_message' => $this->faker->randomElement($errorMessages),
            ];
        });
    }

    /**
     * حالة قيد المعالجة
     */
    public function processing(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => BokImport::STATUS_PROCESSING,
                'started_at' => $this->faker->dateTimeBetween('-2 hours', 'now'),
                'completed_at' => null,
                'processing_time' => null,
                'book_id' => null,
                'error_message' => null,
            ];
        });
    }

    /**
     * حالة في الانتظار
     */
    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => BokImport::STATUS_PENDING,
                'started_at' => null,
                'completed_at' => null,
                'processing_time' => null,
                'book_id' => null,
                'error_message' => null,
            ];
        });
    }

    /**
     * حالة ملغية
     */
    public function cancelled(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = $this->faker->optional()->dateTimeBetween('-1 week', '-1 day');
            $completedAt = $startedAt ? $this->faker->dateTimeBetween($startedAt, 'now') : now();
            $processingTime = $startedAt ? $completedAt->getTimestamp() - $startedAt->getTimestamp() : 0;
            
            return [
                'status' => BokImport::STATUS_CANCELLED,
                'started_at' => $startedAt,
                'completed_at' => $completedAt,
                'processing_time' => $processingTime,
                'book_id' => null,
                'error_message' => null,
            ];
        });
    }

    /**
     * مع نسخة احتياطية
     */
    public function withBackup(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'backup_created' => true,
                'backup_path' => 'backups/bok/' . $attributes['file_hash'] . '.backup',
            ];
        });
    }

    /**
     * مع كتاب مرتبط
     */
    public function withBook(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'book_id' => Book::factory(),
                'status' => BokImport::STATUS_COMPLETED,
            ];
        });
    }

    /**
     * ملف كبير
     */
    public function largeFile(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'file_size' => $this->faker->numberBetween(50 * 1024 * 1024, 500 * 1024 * 1024), // 50MB to 500MB
                'volumes_count' => $this->faker->numberBetween(10, 50),
                'chapters_count' => $this->faker->numberBetween(500, 2000),
                'pages_count' => $this->faker->numberBetween(2000, 10000),
                'estimated_words' => $this->faker->numberBetween(1000000, 5000000),
            ];
        });
    }

    /**
     * من مصدر CLI
     */
    public function fromCli(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'import_source' => BokImport::SOURCE_CLI,
            ];
        });
    }

    /**
     * من مصدر API
     */
    public function fromApi(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'import_source' => BokImport::SOURCE_API,
            ];
        });
    }
}