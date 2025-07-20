<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Volume>
 */
class VolumeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_id' => \App\Models\Book::inRandomOrder()->first()?->id,
            'number' => $this->faker->numberBetween(1, 10),
            'title' => $this->faker->sentence(2),
            'page_start' => $this->faker->numberBetween(1, 100),
            'page_end' => $this->faker->numberBetween(101, 200),
        ];
    }
}
