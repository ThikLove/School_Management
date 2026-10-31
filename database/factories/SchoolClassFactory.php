<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $grade = fake()->numberBetween(7, 12);

        return [
            'class_name' => 'Grade '.$grade.fake()->unique()->bothify('?#'),
            'grade_level' => $grade,
            'room_number' => fake()->bothify('R-###'),
            'homeroom_teacher_id' => Teacher::factory(),
        ];
    }

    /**
     * Indicate that the class has no homeroom teacher yet.
     */
    public function withoutHomeroomTeacher(): static
    {
        return $this->state(fn (array $attributes) => [
            'homeroom_teacher_id' => null,
        ]);
    }
}
