<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Reference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reference>
 */
class ReferenceFactory extends Factory
{
    protected $model = Reference::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            'صحيح البخاري', 'صحيح مسلم', 'سنن أبي داود', 'جامع الترمذي',
            'سنن النسائي', 'سنن ابن ماجه', 'مسند الإمام أحمد', 'الموطأ',
            'تفسير الطبري', 'تفسير ابن كثير', 'تفسير القرطبي', 'الجامع لأحكام القرآن',
            'إحياء علوم الدين', 'مجموع الفتاوى', 'زاد المعاد', 'الفقه على المذاهب الأربعة',
            'البداية والنهاية', 'تاريخ الطبري', 'سير أعلام النبلاء', 'الكامل في التاريخ'
        ];
        
        $authors = [
            'الإمام البخاري', 'الإمام مسلم', 'أبو داود السجستاني', 'الترمذي',
            'النسائي', 'ابن ماجه', 'الإمام أحمد بن حنبل', 'الإمام مالك',
            'الطبري', 'ابن كثير', 'القرطبي', 'الغزالي', 'ابن تيمية', 'ابن القيم'
        ];
        
        $publishers = [
            'دار الكتب العلمية', 'مؤسسة الرسالة', 'دار السلام', 'دار الفكر',
            'دار المعرفة', 'دار إحياء التراث العربي', 'دار الكتاب العربي',
            'مكتبة المعارف', 'دار الحديث', 'دار الوطن'
        ];
        
        $title = $this->faker->randomElement($titles);
        $author = $this->faker->randomElement($authors);
        
        return [
            'book_id' => Book::factory(),
            'title' => $title,
            'author' => $author,
            'publisher' => $this->faker->randomElement($publishers),
            'publication_year' => $this->faker->numberBetween(1980, 2024),
            'page_reference' => $this->faker->numberBetween(1, 500) . '-' . $this->faker->numberBetween(501, 1000),
            'reference_type' => $this->faker->randomElement(['book', 'article', 'hadith', 'verse', 'manuscript', 'website']),
            'isbn' => $this->faker->optional(0.6)->isbn13(),
            'url' => $this->faker->optional(0.3)->url(),
            'notes' => $this->faker->optional(0.4)->paragraph(),
            'edition' => $this->faker->optional(0.7)->numberBetween(1, 10),
            'volume_info' => $this->faker->optional(0.5)->numberBetween(1, 20),
            'citation_count' => $this->faker->numberBetween(0, 100),
            'is_verified' => $this->faker->boolean(80), // 80% مراجع موثقة
        ];
    }

    /**
     * مرجع كتاب
     */
    public function book(): static
    {
        return $this->state(fn (array $attributes) => [
            'reference_type' => 'book',
            'isbn' => $this->faker->isbn13(),
            'edition' => $this->faker->numberBetween(1, 5),
            'volume_info' => $this->faker->optional(0.7)->numberBetween(1, 10),
        ]);
    }

    /**
     * مرجع مقال
     */
    public function article(): static
    {
        return $this->state(fn (array $attributes) => [
            'reference_type' => 'article',
            'publisher' => 'مجلة ' . $this->faker->word(),
            'volume_info' => 'العدد ' . $this->faker->numberBetween(1, 100),
        ]);
    }

    /**
     * مرجع حديث
     */
    public function hadith(): static
    {
        $hadithBooks = [
            'صحيح البخاري', 'صحيح مسلم', 'سنن أبي داود', 'جامع الترمذي',
            'سنن النسائي', 'سنن ابن ماجه', 'مسند الإمام أحمد'
        ];
        
        return $this->state(fn (array $attributes) => [
            'reference_type' => 'hadith',
            'title' => $this->faker->randomElement($hadithBooks),
            'page_reference' => 'حديث رقم ' . $this->faker->numberBetween(1, 7000),
            'is_verified' => true,
        ]);
    }

    /**
     * مرجع آية قرآنية
     */
    public function verse(): static
    {
        $surahs = [
            'البقرة', 'آل عمران', 'النساء', 'المائدة', 'الأنعام', 'الأعراف',
            'الأنفال', 'التوبة', 'يونس', 'هود', 'يوسف', 'الرعد', 'إبراهيم'
        ];
        
        return $this->state(fn (array $attributes) => [
            'reference_type' => 'verse',
            'title' => 'القرآن الكريم',
            'author' => null,
            'page_reference' => 'سورة ' . $this->faker->randomElement($surahs) . ' آية ' . $this->faker->numberBetween(1, 200),
            'is_verified' => true,
        ]);
    }

    /**
     * مرجع مخطوطة
     */
    public function manuscript(): static
    {
        return $this->state(fn (array $attributes) => [
            'reference_type' => 'manuscript',
            'publisher' => 'مخطوطة - ' . $this->faker->randomElement(['دار الكتب المصرية', 'مكتبة الأزهر', 'مكتبة الحرم المكي']),
            'notes' => 'مخطوطة رقم ' . $this->faker->numberBetween(1000, 9999),
        ]);
    }

    /**
     * مرجع موقع إلكتروني
     */
    public function website(): static
    {
        return $this->state(fn (array $attributes) => [
            'reference_type' => 'website',
            'url' => $this->faker->url(),
            'publisher' => $this->faker->randomElement(['الموقع الرسمي', 'موقع إسلام ويب', 'موقع الدرر السنية']),
            'notes' => 'تاريخ الزيارة: ' . $this->faker->date(),
        ]);
    }

    /**
     * مرجع موثق
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
            'citation_count' => $this->faker->numberBetween(10, 200),
        ]);
    }

    /**
     * مرجع غير موثق
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => false,
            'citation_count' => $this->faker->numberBetween(0, 5),
            'notes' => 'يحتاج إلى مراجعة وتوثيق',
        ]);
    }

    /**
     * مرجع كثير الاستشهاد
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'citation_count' => $this->faker->numberBetween(50, 500),
            'is_verified' => true,
        ]);
    }

    /**
     * مرجع حديث
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'publication_year' => $this->faker->numberBetween(2010, 2024),
        ]);
    }

    /**
     * مرجع كلاسيكي
     */
    public function classic(): static
    {
        return $this->state(fn (array $attributes) => [
            'publication_year' => $this->faker->numberBetween(800, 1500),
            'reference_type' => $this->faker->randomElement(['book', 'manuscript']),
            'is_verified' => true,
        ]);
    }
}