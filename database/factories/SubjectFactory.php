<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_code' => fake()->unique()->bothify('???###'),
            'name' => ucwords(fake()->unique()->words(2, true)),
            'description' => fake()->sentence(10),
        ];
    }
}
