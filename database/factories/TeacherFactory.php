<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
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
            'teacher_code' => fake()->unique()->numerify('T####'),
            'name' => fake()->name($gender),
            'gender' => $gender,
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('0## ### ###'),
            'address' => fake()->streetAddress().', '.fake()->city(),
            'hire_date' => fake()->dateTimeBetween('-15 years', '-3 months')->format('Y-m-d'),
            'department_id' => Department::factory(),
        ];
    }
}
