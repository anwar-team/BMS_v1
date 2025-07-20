<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chapter>
 */
class ChapterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $arabicChapters = [
            'مقدمة', 'الفصل الأول: التعريف', 'الفصل الثاني: التاريخ', 'الفصل الثالث: التطبيقات',
            'الفصل الرابع: التحليل', 'الفصل الخامس: النتائج', 'خاتمة', 'ملاحق', 'مراجع',
            'تمارين', 'أمثلة عملية', 'دراسات حالة', 'مفاهيم أساسية', 'مناقشات', 'ملخص'
        ];
        return [
            'volume_id' => \App\Models\Volume::inRandomOrder()->first()?->id,
            'book_id' => \App\Models\Book::inRandomOrder()->first()?->id,
            'chapter_number' => $this->faker->numberBetween(1, 100),
            'title' => $this->faker->randomElement($arabicChapters),
            'parent_id' => null, // Avoid random parent_id to prevent invalid references
            'order' => $this->faker->numberBetween(1, 100),
            'page_start' => $this->faker->numberBetween(1, 1000),
            'page_end' => $this->faker->numberBetween(1, 1000),
            'chapter_type' => $this->faker->randomElement(['main', 'sub']),
        ];
    }
}
