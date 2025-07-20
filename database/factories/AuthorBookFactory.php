<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuthorBook>
 */
class AuthorBookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_id' => $this->faker->numberBetween(1, 10),
            'author_id' => $this->faker->numberBetween(1, 10),
            'role' => $this->faker->randomElement(['مؤلف رئيسي', 'مؤلف مشارك', 'محرر']),
            'is_main' => $this->faker->boolean(80),
            'display_order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
