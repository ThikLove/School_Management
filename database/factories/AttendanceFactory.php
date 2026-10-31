<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * By default the attendance is taken in the student's current class.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'class_id' => fn (array $attributes) => Student::find($attributes['student_id'])->class_id,
            'attendance_date' => fake()->unique()->dateTimeBetween('-60 days', 'now')->format('Y-m-d'),
            'status' => fake()->randomElement(['present', 'present', 'present', 'present', 'late', 'absent', 'excused']),
            'remarks' => null,
        ];
    }
}
