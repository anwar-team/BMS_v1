<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Author>
 */
class AuthorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $arabicFirstNames = ['أحمد', 'محمد', 'علي', 'يوسف', 'إبراهيم', 'خالد', 'سعيد', 'حسن', 'محمود', 'عبدالله', 'سلمان', 'طارق', 'سامي', 'فهد', 'سليمان'];
        $arabicMiddleNames = ['بن', 'أبو', 'عبد', 'نور', 'زين', 'حسين', 'جمال', 'سعيد', 'سليم', 'سامي'];
        $arabicLastNames = ['الأنصاري', 'العربي', 'الشريف', 'الهاشمي', 'القحطاني', 'العتيبي', 'المصري', 'السوري', 'الفلسطيني', 'العراقي', 'الجزائري', 'المغربي', 'السعودي', 'اللبناني', 'التونسي'];
        $arabicNationalities = ['سعودي', 'مصري', 'أردني', 'سوري', 'لبناني', 'فلسطيني', 'مغربي', 'جزائري', 'تونسي', 'عراقي', 'كويتي', 'قطري', 'إماراتي', 'ليبي', 'سوداني'];
        return [
            'fname' => $this->faker->randomElement($arabicFirstNames),
            'mname' => $this->faker->randomElement($arabicMiddleNames),
            'lname' => $this->faker->randomElement($arabicLastNames),
            //'nickname' => $this->faker->userName(),
            'biography' => $this->faker->paragraph(),
            'nationality' => $this->faker->randomElement($arabicNationalities),
            'madhhab' => $this->faker->randomElement(['حنفي', 'شافعي', 'مالكي', 'حنبلي', 'آخر']),
            'birth_date' => $this->faker->date(),
            'death_date' => $this->faker->optional()->date(),
        ];
    }
}
