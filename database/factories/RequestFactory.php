<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Request>
 */
class RequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['pendente', 'reservado', 'devolvido', 'cancelado']),
            'requested_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'approved_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'returned_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
