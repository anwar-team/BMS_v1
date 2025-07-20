<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $arabicTitles = [
            'العلوم الإسلامية', 'تاريخ العرب', 'اللغة العربية', 'الأدب العربي', 'الفقه الإسلامي',
            'الحديث الشريف', 'القرآن الكريم وتفسيره', 'البلاغة والنحو', 'الشعر العربي', 'المنطق والفلسفة',
            'الطب النبوي', 'سير الصحابة', 'المرأة في الإسلام', 'الاقتصاد الإسلامي', 'العلوم الطبيعية عند العرب',
            'الخط العربي', 'التراث العربي', 'القصص العربية', 'الرحلات والجغرافيا', 'علم النفس الإسلامي'
        ];
        $arabicDescriptions = [
            'هذا الكتاب يتناول موضوعاً مهماً في التراث العربي والإسلامي.',
            'دراسة معمقة في أحد فروع العلوم الإسلامية.',
            'يحتوي على شرح وافٍ لموضوعات اللغة العربية وآدابها.',
            'يقدم تحليلاً تاريخياً لأحداث هامة في العالم العربي.',
            'مجموعة من المقالات حول الفكر الإسلامي الحديث.',
            'سيرة ذاتية لأحد أعلام الأمة.',
            'دليل مبسط لفهم أساسيات الفقه والشريعة.',
            'رحلة معرفية في عالم الأدب والشعر.',
            'موسوعة مختصرة في العلوم الطبيعية عند العرب.',
            'دراسة مقارنة بين المدارس الفلسفية الإسلامية.'
        ];
        return [
            'title' => $this->faker->randomElement($arabicTitles),
            'description' => $this->faker->randomElement($arabicDescriptions),
            'slug' => $this->faker->unique()->slug(),
            'cover_image' => $this->faker->imageUrl(300, 400, 'books'),
            'published_year' => $this->faker->year(),
            'publisher' => $this->faker->company(),
            'publisher_id' => \App\Models\Publisher::inRandomOrder()->first()?->id,
            'pages_count' => $this->faker->numberBetween(50, 1000),
            'volumes_count' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->randomElement(['published', 'draft', 'archived']),
            'visibility' => $this->faker->randomElement(['public', 'private']),
            'cover_image_url' => $this->faker->imageUrl(300, 400, 'books'),
            'source_url' => $this->faker->url(),
            'book_section_id' => \App\Models\BookSection::inRandomOrder()->first()?->id,
        ];
    }
}
