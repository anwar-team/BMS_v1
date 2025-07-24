<?php

namespace Database\Factories;

use App\Models\Chapter;
use App\Models\Page;
use App\Models\PageReference;
use App\Models\Reference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PageReference>
 */
class PageReferenceFactory extends Factory
{
    protected $model = PageReference::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $citationTexts = [
            'قال الله تعالى', 'وقال رسول الله صلى الله عليه وسلم', 'روى البخاري',
            'أخرج مسلم', 'ذكر ابن كثير', 'نقل الطبري', 'أورد النووي',
            'انظر', 'راجع', 'كما في', 'وفي رواية أخرى', 'وقد ثبت',
            'والصحيح', 'والراجح', 'وهذا ما عليه جمهور العلماء'
        ];
        
        $contexts = [
            'في سياق الحديث عن أحكام الصلاة', 'عند شرح آيات الزكاة',
            'في معرض الكلام عن الحج', 'أثناء تفسير الآية الكريمة',
            'في باب الطهارة', 'عند ذكر أحاديث الصيام', 'في فصل المعاملات',
            'في سياق الحديث عن العقيدة', 'عند شرح أسماء الله الحسنى',
            'في معرض الكلام عن الأخلاق', 'أثناء ذكر قصص الأنبياء'
        ];
        
        return [
            'page_id' => Page::factory(),
            'reference_id' => Reference::factory(),
            'chapter_id' => Chapter::factory(),
            'citation_text' => $this->faker->randomElement($citationTexts),
            'position_in_page' => $this->faker->numberBetween(100, 2000),
            'citation_type' => $this->faker->randomElement(['direct_quote', 'paraphrase', 'reference', 'see_also', 'compare']),
            'context' => $this->faker->randomElement($contexts),
            'is_primary_source' => $this->faker->boolean(60), // 60% مصادر أولية
        ];
    }

    /**
     * اقتباس مباشر
     */
    public function directQuote(): static
    {
        $quotes = [
            '"إنما الأعمال بالنيات وإنما لكل امرئ ما نوى"',
            '"المسلم من سلم المسلمون من لسانه ويده"',
            '"لا يؤمن أحدكم حتى يحب لأخيه ما يحب لنفسه"',
            '"الدين النصيحة"',
            '"من كان يؤمن بالله واليوم الآخر فليقل خيراً أو ليصمت"'
        ];
        
        return $this->state(fn (array $attributes) => [
            'citation_type' => 'direct_quote',
            'citation_text' => $this->faker->randomElement($quotes),
            'is_primary_source' => true,
        ]);
    }

    /**
     * إعادة صياغة
     */
    public function paraphrase(): static
    {
        return $this->state(fn (array $attributes) => [
            'citation_type' => 'paraphrase',
            'citation_text' => 'وقد ذكر المؤلف أن ' . $this->faker->sentence(),
            'is_primary_source' => false,
        ]);
    }

    /**
     * مرجع عام
     */
    public function reference(): static
    {
        return $this->state(fn (array $attributes) => [
            'citation_type' => 'reference',
            'citation_text' => $this->faker->randomElement(['انظر', 'راجع', 'ارجع إلى']),
            'is_primary_source' => $this->faker->boolean(30),
        ]);
    }

    /**
     * انظر أيضاً
     */
    public function seeAlso(): static
    {
        return $this->state(fn (array $attributes) => [
            'citation_type' => 'see_also',
            'citation_text' => 'انظر أيضاً',
            'context' => 'للمزيد من التفاصيل حول هذا الموضوع',
            'is_primary_source' => false,
        ]);
    }

    /**
     * مقارنة
     */
    public function compare(): static
    {
        return $this->state(fn (array $attributes) => [
            'citation_type' => 'compare',
            'citation_text' => $this->faker->randomElement(['قارن مع', 'وانظر الفرق في', 'وخلافاً لما ذكر في']),
            'context' => 'في سياق المقارنة بين الآراء المختلفة',
            'is_primary_source' => false,
        ]);
    }

    /**
     * مصدر أولي
     */
    public function primarySource(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary_source' => true,
            'citation_type' => $this->faker->randomElement(['direct_quote', 'reference']),
        ]);
    }

    /**
     * مصدر ثانوي
     */
    public function secondarySource(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary_source' => false,
            'citation_type' => $this->faker->randomElement(['paraphrase', 'see_also', 'compare']),
        ]);
    }

    /**
     * مرجع قرآني
     */
    public function quranic(): static
    {
        $quranicPhrases = [
            'قال الله تعالى', 'وقوله سبحانه', 'كما في قوله عز وجل',
            'وفي الآية الكريمة', 'وقد أنزل الله تعالى'
        ];
        
        return $this->state(fn (array $attributes) => [
            'citation_text' => $this->faker->randomElement($quranicPhrases),
            'citation_type' => 'direct_quote',
            'is_primary_source' => true,
            'context' => 'في سياق الاستشهاد بالقرآن الكريم',
        ]);
    }

    /**
     * مرجع حديث
     */
    public function hadith(): static
    {
        $hadithPhrases = [
            'قال رسول الله صلى الله عليه وسلم', 'وفي الحديث الشريف',
            'روى البخاري', 'أخرج مسلم', 'وفي رواية أخرى',
            'وثبت عن النبي صلى الله عليه وسلم'
        ];
        
        return $this->state(fn (array $attributes) => [
            'citation_text' => $this->faker->randomElement($hadithPhrases),
            'citation_type' => 'direct_quote',
            'is_primary_source' => true,
            'context' => 'في سياق الاستشهاد بالسنة النبوية',
        ]);
    }

    /**
     * مرجع فقهي
     */
    public function fiqh(): static
    {
        $fiqhPhrases = [
            'وقال الإمام أحمد', 'ذهب الشافعي إلى', 'وعند المالكية',
            'والراجح عند الحنابلة', 'وهو مذهب الحنفية', 'وقال جمهور العلماء'
        ];
        
        return $this->state(fn (array $attributes) => [
            'citation_text' => $this->faker->randomElement($fiqhPhrases),
            'citation_type' => $this->faker->randomElement(['reference', 'paraphrase']),
            'context' => 'في سياق ذكر الآراء الفقهية',
            'is_primary_source' => false,
        ]);
    }

    /**
     * مرجع في بداية الصفحة
     */
    public function atPageStart(): static
    {
        return $this->state(fn (array $attributes) => [
            'position_in_page' => $this->faker->numberBetween(50, 300),
        ]);
    }

    /**
     * مرجع في نهاية الصفحة
     */
    public function atPageEnd(): static
    {
        return $this->state(fn (array $attributes) => [
            'position_in_page' => $this->faker->numberBetween(1500, 2000),
        ]);
    }
}