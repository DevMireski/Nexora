<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'       => fake()->sentence(4),
            'description' => fake()->optional(0.8)->paragraph(),
            'status'      => fake()->randomElement(['pending', 'progress', 'done']),
            'user_id'     => User::inRandomOrder()->value('id'),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function inProgress(): static
    {
        return $this->state(['status' => 'progress']);
    }

    public function done(): static
    {
        return $this->state(['status' => 'done']);
    }
}
