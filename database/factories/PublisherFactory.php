<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Publisher>
 */
class PublisherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $arabicPublishers = [
            'دار الكتب العلمية', 'مكتبة الرشد', 'دار الفكر', 'مؤسسة الرسالة', 'دار المعرفة',
            'دار الشروق', 'دار الهلال', 'دار النهضة العربية', 'دار المدى', 'دار اليمامة',
            'دار الجيل', 'دار ابن كثير', 'دار المنارة', 'دار القلم', 'دار الطليعة',
            'دار المعارف', 'دار الساقي', 'دار الفارابي', 'دار البشير', 'دار الأندلس'
        ];
        $arabicCountries = [
            'السعودية', 'مصر', 'لبنان', 'سوريا', 'الأردن', 'المغرب', 'تونس', 'الجزائر', 'الإمارات', 'قطر', 'الكويت', 'العراق', 'فلسطين', 'ليبيا', 'اليمن'
        ];
        return [
            'name' => $this->faker->randomElement($arabicPublishers),
            'country' => $this->faker->randomElement($arabicCountries),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'description' => $this->faker->sentence(),
            'website_url' => $this->faker->url(),
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
