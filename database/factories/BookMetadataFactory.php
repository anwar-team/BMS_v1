<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\BookMetadata;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookMetadata>
 */
class BookMetadataFactory extends Factory
{
    protected $model = BookMetadata::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $metadataKeys = [
            'dc.title', 'dc.creator', 'dc.subject', 'dc.description', 'dc.publisher',
            'dc.contributor', 'dc.date', 'dc.type', 'dc.format', 'dc.identifier',
            'dc.source', 'dc.language', 'dc.relation', 'dc.coverage', 'dc.rights',
            'islamic.hijri_date', 'islamic.narrator', 'islamic.chain_of_transmission',
            'islamic.hadith_grade', 'islamic.fiqh_school', 'shamela.book_id',
            'shamela.category', 'shamela.verification_status', 'custom.reading_level',
            'custom.target_audience', 'custom.difficulty_level'
        ];
        
        $key = $this->faker->randomElement($metadataKeys);
        $type = $this->getMetadataType($key);
        
        return [
            'book_id' => Book::factory(),
            'metadata_key' => $key,
            'metadata_value' => $this->generateValueForKey($key),
            'metadata_type' => $type,
            'data_type' => $this->getDataType($key),
            'description' => $this->getDescription($key),
            'is_searchable' => $this->faker->boolean(80), // 80% قابلة للبحث
            'is_public' => $this->faker->boolean(90), // 90% عامة
            'display_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * تحديد نوع البيانات الوصفية حسب المفتاح
     */
    private function getMetadataType(string $key): string
    {
        if (str_starts_with($key, 'dc.')) {
            return 'dublin_core';
        } elseif (str_starts_with($key, 'islamic.')) {
            return 'islamic_metadata';
        } elseif (str_starts_with($key, 'shamela.')) {
            return 'shamela_specific';
        } else {
            return 'custom';
        }
    }

    /**
     * تحديد نوع البيانات حسب المفتاح
     */
    private function getDataType(string $key): string
    {
        $dateKeys = ['dc.date', 'islamic.hijri_date'];
        $numberKeys = ['shamela.book_id', 'display_order'];
        $booleanKeys = ['shamela.verification_status'];
        
        if (in_array($key, $dateKeys)) {
            return 'date';
        } elseif (in_array($key, $numberKeys)) {
            return 'integer';
        } elseif (in_array($key, $booleanKeys)) {
            return 'boolean';
        } else {
            return 'string';
        }
    }

    /**
     * توليد قيمة مناسبة للمفتاح
     */
    private function generateValueForKey(string $key): string
    {
        return match ($key) {
            'dc.title' => $this->faker->sentence(4),
            'dc.creator' => $this->faker->randomElement(['ابن تيمية', 'ابن القيم', 'النووي', 'ابن كثير', 'الطبري']),
            'dc.subject' => $this->faker->randomElement(['الفقه', 'التفسير', 'الحديث', 'العقيدة', 'التاريخ']),
            'dc.description' => $this->faker->paragraph(),
            'dc.publisher' => $this->faker->randomElement(['دار الكتب العلمية', 'مؤسسة الرسالة', 'دار السلام']),
            'dc.date' => $this->faker->date(),
            'dc.language' => 'ar',
            'dc.type' => 'Text',
            'dc.format' => 'application/pdf',
            'islamic.hijri_date' => $this->faker->numberBetween(1, 1445) . 'هـ',
            'islamic.narrator' => $this->faker->randomElement(['البخاري', 'مسلم', 'أبو داود', 'الترمذي']),
            'islamic.fiqh_school' => $this->faker->randomElement(['الحنفي', 'المالكي', 'الشافعي', 'الحنبلي']),
            'islamic.hadith_grade' => $this->faker->randomElement(['صحيح', 'حسن', 'ضعيف', 'موضوع']),
            'shamela.book_id' => (string) $this->faker->numberBetween(1000, 9999),
            'shamela.category' => $this->faker->randomElement(['العقيدة', 'الفقه', 'التفسير', 'الحديث']),
            'shamela.verification_status' => $this->faker->boolean() ? 'true' : 'false',
            'custom.reading_level' => $this->faker->randomElement(['مبتدئ', 'متوسط', 'متقدم']),
            'custom.target_audience' => $this->faker->randomElement(['عام', 'طلاب العلم', 'الباحثين', 'المختصين']),
            'custom.difficulty_level' => (string) $this->faker->numberBetween(1, 5),
            default => $this->faker->sentence(),
        };
    }

    /**
     * توليد وصف للمفتاح
     */
    private function getDescription(string $key): string
    {
        return match ($key) {
            'dc.title' => 'عنوان الكتاب',
            'dc.creator' => 'مؤلف الكتاب',
            'dc.subject' => 'موضوع الكتاب',
            'dc.description' => 'وصف الكتاب',
            'dc.publisher' => 'دار النشر',
            'dc.date' => 'تاريخ النشر',
            'dc.language' => 'لغة الكتاب',
            'islamic.hijri_date' => 'التاريخ الهجري',
            'islamic.narrator' => 'الراوي',
            'islamic.fiqh_school' => 'المذهب الفقهي',
            'shamela.book_id' => 'رقم الكتاب في الشاملة',
            'custom.reading_level' => 'مستوى القراءة',
            default => 'بيانات وصفية إضافية',
        };
    }

    /**
     * بيانات وصفية من دبلن كور
     */
    public function dublinCore(): static
    {
        $dcKeys = [
            'dc.title', 'dc.creator', 'dc.subject', 'dc.description',
            'dc.publisher', 'dc.date', 'dc.language', 'dc.type'
        ];
        
        return $this->state(fn (array $attributes) => [
            'metadata_type' => 'dublin_core',
            'metadata_key' => $this->faker->randomElement($dcKeys),
            'is_searchable' => true,
            'is_public' => true,
        ]);
    }

    /**
     * بيانات وصفية إسلامية
     */
    public function islamic(): static
    {
        $islamicKeys = [
            'islamic.hijri_date', 'islamic.narrator', 'islamic.chain_of_transmission',
            'islamic.hadith_grade', 'islamic.fiqh_school'
        ];
        
        return $this->state(fn (array $attributes) => [
            'metadata_type' => 'islamic_metadata',
            'metadata_key' => $this->faker->randomElement($islamicKeys),
            'is_searchable' => true,
        ]);
    }

    /**
     * بيانات وصفية خاصة بالشاملة
     */
    public function shamela(): static
    {
        $shamelaKeys = [
            'shamela.book_id', 'shamela.category', 'shamela.verification_status'
        ];
        
        return $this->state(fn (array $attributes) => [
            'metadata_type' => 'shamela_specific',
            'metadata_key' => $this->faker->randomElement($shamelaKeys),
        ]);
    }

    /**
     * بيانات وصفية مخصصة
     */
    public function custom(): static
    {
        $customKeys = [
            'custom.reading_level', 'custom.target_audience', 'custom.difficulty_level'
        ];
        
        return $this->state(fn (array $attributes) => [
            'metadata_type' => 'custom',
            'metadata_key' => $this->faker->randomElement($customKeys),
        ]);
    }

    /**
     * قابلة للبحث
     */
    public function searchable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_searchable' => true,
        ]);
    }

    /**
     * غير قابلة للبحث
     */
    public function nonSearchable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_searchable' => false,
        ]);
    }

    /**
     * عامة
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * خاصة
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * أولوية عرض عالية
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'display_order' => $this->faker->numberBetween(1, 10),
        ]);
    }

    /**
     * أولوية عرض منخفضة
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'display_order' => $this->faker->numberBetween(90, 100),
        ]);
    }
}