<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookSection>
 */
class BookSectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $arabicSections = [
            'العلوم الشرعية', 'اللغة العربية', 'الأدب', 'التاريخ', 'الجغرافيا',
            'العلوم الطبيعية', 'الرياضيات', 'المنطق', 'الفلسفة', 'الاقتصاد',
            'الاجتماع', 'القانون', 'الطب', 'الهندسة', 'الفنون',
            'القصص والروايات', 'الشعر', 'السير والتراجم', 'التراث', 'الرحلات'
        ];
        $arabicDescriptions = [
            'قسم يهتم بموضوعات متنوعة باللغة العربية.',
            'يحتوي على كتب متخصصة في هذا المجال.',
            'مجموعة مختارة من أفضل الكتب العربية.',
            'دليل شامل لأهم المصادر والمراجع.',
            'سلسلة كتب تعليمية وتثقيفية.'
        ];
        return [
            'name' => $this->faker->randomElement($arabicSections),
            'description' => $this->faker->randomElement($arabicDescriptions),
            'parent_id' => $this->faker->optional()->numberBetween(1, 10),
            'sort_order' => $this->faker->numberBetween(1, 100),
            'is_active' => $this->faker->boolean(90),
            'slug' => $this->faker->unique()->slug(),
        ];
    }
}
