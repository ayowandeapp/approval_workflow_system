<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApprovalFlow>
 */
class ApprovalFlowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'approver_id' => User::factory(),
            // 'department_id' => Department::factory(),
            'level' => fake()->randomElement([1, 2]),
            'is_active' => 1,
            // 'is_active' => fake()->randomElement(ActivationStatus::cases())->value,
        ];
    }
}
