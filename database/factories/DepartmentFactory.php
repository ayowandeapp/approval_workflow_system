<?php

namespace Database\Factories;

use App\Enums\ActivationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'description' => fake()->sentence(),
            'is_active' => 1,
            // 'is_active' => fake()->randomElement(ActivationStatus::cases())->value,
        ];
    }
}
