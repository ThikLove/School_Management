<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * By default the student is enrolled in the class they are currently placed in.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'class_id' => fn (array $attributes) => Student::find($attributes['student_id'])->class_id,
            'enrollment_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'academic_year' => '2026-2027',
            'status' => 'active',
        ];
    }
}
