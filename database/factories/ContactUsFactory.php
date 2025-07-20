<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContactUs>
 */
class ContactUsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'company' => $this->faker->company(),
            'employees' => $this->faker->randomElement(['1-10', '11-50', '51-200', '201-500', '501-1000', '1000+']),
            'title' => $this->faker->jobTitle(),
            'subject' => $this->faker->sentence(6),
            'message' => $this->faker->paragraph(3),
            'status' => $this->faker->randomElement(['new', 'pending', 'closed']),
            'reply_subject' => $this->faker->optional()->sentence(4),
            'reply_message' => $this->faker->optional()->paragraph(2),
            'replied_at' => $this->faker->optional()->dateTime(),
            'replied_by_user_id' => $this->faker->optional()->numberBetween(1, 10),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'metadata' => json_encode(['source' => $this->faker->word()]),
        ];
    }
}
