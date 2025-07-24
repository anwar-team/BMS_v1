<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Footnote;
use App\Models\Page;
use App\Models\Volume;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Footnote>
 */
class FootnoteFactory extends Factory
{
    protected $model = Footnote::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'page_id' => Page::factory(),
            'chapter_id' => Chapter::factory(),
            'volume_id' => Volume::factory(),
            'footnote_number' => $this->faker->numberBetween(1, 50),
            'content' => $this->faker->paragraph(3),
            'position_in_page' => $this->faker->numberBetween(100, 2000),
            'reference_text' => $this->faker->optional()->sentence(),
            'type' => $this->faker->randomElement(['explanation', 'reference', 'translation', 'correction', 'addition']),
            'order_in_page' => $this->faker->numberBetween(1, 10),
            'is_original' => $this->faker->boolean(80), // 80% احتمال أن تكون أصلية
        ];
    }

    /**
     * حاشية تفسيرية
     */
    public function explanation(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'explanation',
            'content' => 'شرح وتفسير: ' . $this->faker->paragraph(2),
        ]);
    }

    /**
     * حاشية مرجعية
     */
    public function reference(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'reference',
            'content' => 'انظر: ' . $this->faker->sentence(),
            'reference_text' => $this->faker->sentence(),
        ]);
    }

    /**
     * حاشية ترجمة
     */
    public function translation(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'translation',
            'content' => 'ترجمة: ' . $this->faker->sentence(),
        ]);
    }

    /**
     * حاشية تصحيح
     */
    public function correction(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'correction',
            'content' => 'تصحيح: ' . $this->faker->sentence(),
            'is_original' => false,
        ]);
    }

    /**
     * حاشية إضافية
     */
    public function addition(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'addition',
            'content' => 'إضافة المحقق: ' . $this->faker->paragraph(),
            'is_original' => false,
        ]);
    }

    /**
     * حاشية طويلة
     */
    public function long(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->paragraphs(5, true),
        ]);
    }

    /**
     * حاشية قصيرة
     */
    public function short(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->sentence(),
        ]);
    }

    /**
     * حاشية أصلية
     */
    public function original(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_original' => true,
        ]);
    }

    /**
     * حاشية من المحقق
     */
    public function editorial(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_original' => false,
            'content' => '[' . $this->faker->paragraph() . '] - المحقق',
        ]);
    }
}