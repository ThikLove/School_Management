<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['male', 'female']);

        return [
            'student_code' => fake()->unique()->numerify('S#####'),
            'name' => fake()->name($gender),
            'gender' => $gender,
            'date_of_birth' => fake()->dateTimeBetween('-18 years', '-12 years')->format('Y-m-d'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional(0.7)->numerify('0## ### ###'),
            'address' => fake()->streetAddress().', '.fake()->city(),
            'enrollment_date' => fake()->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
            'status' => 'active',
            'class_id' => SchoolClass::factory(),
        ];
    }

    /**
     * Indicate that the student is no longer attending.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the student has graduated.
     */
    public function graduated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'graduated',
        ]);
    }
}
