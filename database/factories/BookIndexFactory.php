<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\BookIndex;
use App\Models\Chapter;
use App\Models\Page;
use App\Models\Volume;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookIndex>
 */
class BookIndexFactory extends Factory
{
    protected $model = BookIndex::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $keywords = [
            'الإيمان', 'التوحيد', 'الصلاة', 'الزكاة', 'الحج', 'الصيام',
            'الجهاد', 'العدالة', 'الرحمة', 'الحكمة', 'العلم', 'التقوى',
            'الصبر', 'الشكر', 'التوبة', 'الاستغفار', 'الدعاء', 'الذكر',
            'القرآن', 'السنة', 'الحديث', 'الفقه', 'الأصول', 'التفسير',
            'العقيدة', 'الأخلاق', 'المعاملات', 'العبادات', 'الأحكام'
        ];
        
        $keyword = $this->faker->randomElement($keywords);
        
        return [
            'book_id' => Book::factory(),
            'page_id' => Page::factory(),
            'chapter_id' => Chapter::factory(),
            'volume_id' => Volume::factory(),
            'keyword' => $keyword,
            'normalized_keyword' => strtolower($keyword),
            'page_number' => $this->faker->numberBetween(1, 500),
            'context' => $this->faker->sentence() . ' ' . $keyword . ' ' . $this->faker->sentence(),
            'position_in_page' => $this->faker->numberBetween(50, 1500),
            'frequency' => $this->faker->numberBetween(1, 20),
            'index_type' => $this->faker->randomElement(['keyword', 'person', 'place', 'concept', 'hadith', 'verse']),
            'relevance_score' => $this->faker->randomFloat(2, 0.1, 1.0),
            'is_auto_generated' => $this->faker->boolean(70), // 70% مولد تلقائياً
        ];
    }

    /**
     * فهرس كلمة مفتاحية
     */
    public function keyword(): static
    {
        return $this->state(fn (array $attributes) => [
            'index_type' => 'keyword',
            'relevance_score' => $this->faker->randomFloat(2, 0.5, 1.0),
        ]);
    }

    /**
     * فهرس شخص
     */
    public function person(): static
    {
        $persons = [
            'أبو بكر الصديق', 'عمر بن الخطاب', 'عثمان بن عفان', 'علي بن أبي طالب',
            'الإمام أحمد', 'الإمام مالك', 'الإمام الشافعي', 'الإمام أبو حنيفة',
            'ابن تيمية', 'ابن القيم', 'الغزالي', 'النووي', 'ابن كثير'
        ];
        
        return $this->state(fn (array $attributes) => [
            'index_type' => 'person',
            'keyword' => $this->faker->randomElement($persons),
            'relevance_score' => $this->faker->randomFloat(2, 0.6, 1.0),
        ]);
    }

    /**
     * فهرس مكان
     */
    public function place(): static
    {
        $places = [
            'مكة المكرمة', 'المدينة المنورة', 'القدس', 'بغداد', 'دمشق',
            'القاهرة', 'قرطبة', 'الأندلس', 'خراسان', 'البصرة', 'الكوفة'
        ];
        
        return $this->state(fn (array $attributes) => [
            'index_type' => 'place',
            'keyword' => $this->faker->randomElement($places),
            'relevance_score' => $this->faker->randomFloat(2, 0.4, 0.8),
        ]);
    }

    /**
     * فهرس مفهوم
     */
    public function concept(): static
    {
        $concepts = [
            'العدالة الإلهية', 'الحكمة الربانية', 'الرحمة الواسعة', 'العلم اللدني',
            'التوكل على الله', 'الخشوع في الصلاة', 'الإخلاص في العمل'
        ];
        
        return $this->state(fn (array $attributes) => [
            'index_type' => 'concept',
            'keyword' => $this->faker->randomElement($concepts),
            'relevance_score' => $this->faker->randomFloat(2, 0.7, 1.0),
        ]);
    }

    /**
     * فهرس حديث
     */
    public function hadith(): static
    {
        $hadiths = [
            'إنما الأعمال بالنيات', 'المسلم من سلم المسلمون من لسانه ويده',
            'لا يؤمن أحدكم حتى يحب لأخيه ما يحب لنفسه', 'الدين النصيحة'
        ];
        
        return $this->state(fn (array $attributes) => [
            'index_type' => 'hadith',
            'keyword' => $this->faker->randomElement($hadiths),
            'relevance_score' => $this->faker->randomFloat(2, 0.8, 1.0),
        ]);
    }

    /**
     * فهرس آية
     */
    public function verse(): static
    {
        $verses = [
            'وما خلقت الجن والإنس إلا ليعبدون', 'إن مع العسر يسراً',
            'ولله الأسماء الحسنى فادعوه بها', 'وهو معكم أين ما كنتم'
        ];
        
        return $this->state(fn (array $attributes) => [
            'index_type' => 'verse',
            'keyword' => $this->faker->randomElement($verses),
            'relevance_score' => $this->faker->randomFloat(2, 0.9, 1.0),
        ]);
    }

    /**
     * فهرس عالي الأهمية
     */
    public function highRelevance(): static
    {
        return $this->state(fn (array $attributes) => [
            'relevance_score' => $this->faker->randomFloat(2, 0.8, 1.0),
            'frequency' => $this->faker->numberBetween(10, 50),
        ]);
    }

    /**
     * فهرس منخفض الأهمية
     */
    public function lowRelevance(): static
    {
        return $this->state(fn (array $attributes) => [
            'relevance_score' => $this->faker->randomFloat(2, 0.1, 0.4),
            'frequency' => $this->faker->numberBetween(1, 3),
        ]);
    }

    /**
     * فهرس مولد تلقائياً
     */
    public function autoGenerated(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_auto_generated' => true,
        ]);
    }

    /**
     * فهرس يدوي
     */
    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_auto_generated' => false,
            'relevance_score' => $this->faker->randomFloat(2, 0.7, 1.0),
        ]);
    }
}