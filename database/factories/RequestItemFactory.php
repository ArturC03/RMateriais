<?php

namespace Database\Factories;

use App\Models\Request;
use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RequestItem>
 */
class RequestItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'request_id' => Request::factory(),
            'material_id' => Material::factory(),
            'quantity' => $this->faker->numberBetween(1, 2),
            'due_date' => now()->addDays(3), // Fixed 3-day duration
            'returned' => $this->faker->boolean(),
        ];
    }
}
