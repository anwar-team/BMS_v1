<?php

namespace Database\Factories;

use App\Models\Annotation;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Annotation>
 */
class AnnotationFactory extends Factory
{
    protected $model = Annotation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $annotationTexts = [
            'هذا النص يحتاج إلى مراجعة وتدقيق', 'معلومة مهمة جداً',
            'انتبه لهذه النقطة', 'يمكن الرجوع إلى المصدر الأصلي',
            'هناك خلاف بين العلماء في هذه المسألة', 'نص جميل ومؤثر',
            'يحتاج إلى شرح إضافي', 'معلومة قيمة', 'نقطة مهمة للحفظ',
            'يستحق التأمل والتفكر', 'مفيد للطلاب', 'يحتاج إلى تطبيق عملي'
        ];
        
        $highlightedTexts = [
            'الإيمان بالله واليوم الآخر', 'العدل والإحسان', 'التقوى والصلاح',
            'العلم والحكمة', 'الصبر والشكر', 'التوبة والاستغفار',
            'الدعاء والذكر', 'الأخلاق الحسنة', 'المعاملة الطيبة',
            'البر والتقوى', 'الحق والعدل', 'الرحمة والمغفرة'
        ];
        
        $colors = ['yellow', 'green', 'blue', 'red', 'orange', 'purple', 'pink', 'gray'];
        
        return [
            'book_id' => Book::factory(),
            'page_id' => Page::factory(),
            'chapter_id' => Chapter::factory(),
            'user_id' => User::factory(),
            'annotation_text' => $this->faker->randomElement($annotationTexts),
            'highlighted_text' => $this->faker->randomElement($highlightedTexts),
            'position_start' => $this->faker->numberBetween(100, 1000),
            'position_end' => $this->faker->numberBetween(1001, 2000),
            'annotation_type' => $this->faker->randomElement(['note', 'highlight', 'bookmark', 'question', 'correction', 'explanation']),
            'visibility' => $this->faker->randomElement(['public', 'private', 'shared']),
            'color' => $this->faker->randomElement($colors),
            'likes_count' => $this->faker->numberBetween(0, 50),
            'is_verified' => $this->faker->boolean(30), // 30% موثقة
        ];
    }

    /**
     * ملاحظة
     */
    public function note(): static
    {
        $notes = [
            'ملاحظة مهمة: هذا النص يحتوي على معلومات قيمة',
            'تذكير: يجب مراجعة هذه النقطة مرة أخرى',
            'فائدة: يمكن الاستفادة من هذا في التطبيق العملي',
            'تنبيه: هناك رأي آخر في هذه المسألة',
            'إضافة: يمكن إضافة المزيد من التفاصيل هنا'
        ];
        
        return $this->state(fn (array $attributes) => [
            'annotation_type' => 'note',
            'annotation_text' => $this->faker->randomElement($notes),
            'color' => 'yellow',
        ]);
    }

    /**
     * تمييز
     */
    public function highlight(): static
    {
        return $this->state(fn (array $attributes) => [
            'annotation_type' => 'highlight',
            'annotation_text' => null, // التمييز فقط بدون نص
            'color' => $this->faker->randomElement(['yellow', 'green', 'blue']),
        ]);
    }

    /**
     * إشارة مرجعية
     */
    public function bookmark(): static
    {
        return $this->state(fn (array $attributes) => [
            'annotation_type' => 'bookmark',
            'annotation_text' => 'إشارة مرجعية - ' . $this->faker->sentence(),
            'color' => 'red',
        ]);
    }

    /**
     * سؤال
     */
    public function question(): static
    {
        $questions = [
            'ما المقصود بهذا النص؟', 'هل هناك دليل على هذا القول؟',
            'كيف يمكن تطبيق هذا عملياً؟', 'ما رأي العلماء الآخرين؟',
            'هل هناك استثناءات لهذه القاعدة؟', 'ما الحكمة من هذا التشريع؟'
        ];
        
        return $this->state(fn (array $attributes) => [
            'annotation_type' => 'question',
            'annotation_text' => $this->faker->randomElement($questions),
            'color' => 'orange',
        ]);
    }

    /**
     * تصحيح
     */
    public function correction(): static
    {
        $corrections = [
            'تصحيح: الصواب هو كذا وكذا', 'خطأ مطبعي: يجب أن يكون',
            'تصويب: النص الصحيح هو', 'ملاحظة: هناك خطأ في التاريخ',
            'تنبيه: الاسم الصحيح هو'
        ];
        
        return $this->state(fn (array $attributes) => [
            'annotation_type' => 'correction',
            'annotation_text' => $this->faker->randomElement($corrections),
            'color' => 'red',
            'is_verified' => false, // التصحيحات تحتاج مراجعة
        ]);
    }

    /**
     * شرح
     */
    public function explanation(): static
    {
        $explanations = [
            'شرح: المقصود بهذا النص هو...', 'توضيح: هذه الكلمة تعني...',
            'تفسير: السبب في هذا الحكم...', 'بيان: الحكمة من هذا التشريع...',
            'إيضاح: الفرق بين هذا وذاك...'
        ];
        
        return $this->state(fn (array $attributes) => [
            'annotation_type' => 'explanation',
            'annotation_text' => $this->faker->randomElement($explanations),
            'color' => 'blue',
        ]);
    }

    /**
     * عامة (مرئية للجميع)
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'public',
            'likes_count' => $this->faker->numberBetween(5, 100),
        ]);
    }

    /**
     * خاصة
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'private',
            'likes_count' => 0,
        ]);
    }

    /**
     * مشتركة
     */
    public function shared(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'shared',
            'likes_count' => $this->faker->numberBetween(1, 20),
        ]);
    }

    /**
     * موثقة
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
            'visibility' => 'public',
            'likes_count' => $this->faker->numberBetween(10, 200),
        ]);
    }

    /**
     * غير موثقة
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => false,
            'likes_count' => $this->faker->numberBetween(0, 10),
        ]);
    }

    /**
     * شائعة (كثيرة الإعجابات)
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'likes_count' => $this->faker->numberBetween(50, 500),
            'visibility' => 'public',
            'is_verified' => true,
        ]);
    }

    /**
     * طويلة
     */
    public function long(): static
    {
        return $this->state(fn (array $attributes) => [
            'annotation_text' => $this->faker->paragraphs(3, true),
            'highlighted_text' => $this->faker->paragraph(),
        ]);
    }

    /**
     * قصيرة
     */
    public function short(): static
    {
        return $this->state(fn (array $attributes) => [
            'annotation_text' => $this->faker->sentence(),
            'highlighted_text' => $this->faker->words(3, true),
        ]);
    }

    /**
     * في بداية الصفحة
     */
    public function atPageStart(): static
    {
        return $this->state(fn (array $attributes) => [
            'position_start' => $this->faker->numberBetween(50, 200),
            'position_end' => $this->faker->numberBetween(201, 400),
        ]);
    }

    /**
     * في نهاية الصفحة
     */
    public function atPageEnd(): static
    {
        return $this->state(fn (array $attributes) => [
            'position_start' => $this->faker->numberBetween(1500, 1800),
            'position_end' => $this->faker->numberBetween(1801, 2000),
        ]);
    }
}