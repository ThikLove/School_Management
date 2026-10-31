<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Seed 30 students, 6 per class.
     *
     * Students in grades 11 and 12 joined the school one year earlier than grade 10 students,
     * which the EnrollmentSeeder uses to build their enrollment history.
     */
    public function run(): void
    {
        $students = [
            ['Chea Vibol', 'male'], ['Sok Sreyneang', 'female'], ['Kim Rithy', 'male'],
            ['Ly Sokunthea', 'female'], ['Prak Sambath', 'male'], ['Seng Chanthy', 'female'],
            ['Touch Piseth', 'male'], ['Vong Sreypov', 'female'], ['Yim Bunthoeun', 'male'],
            ['Pen Malis', 'female'], ['Nget Sovannarith', 'male'], ['Tep Socheata', 'female'],
            ['Hour Kimleng', 'male'], ['Mao Sreyleak', 'female'], ['Khim Vuthy', 'male'],
            ['Sar Davy', 'female'], ['Chhay Panha', 'male'], ['Long Rachana', 'female'],
            ['Sim Sokha', 'male'], ['Pich Sopheak', 'female'], ['Em Rotha', 'male'],
            ['Kong Sreynich', 'female'], ['Uy Seyha', 'male'], ['Khun Leakhena', 'female'],
            ['Norn Chanrith', 'male'], ['Yos Sophany', 'female'], ['Choun Makara', 'male'],
            ['Rin Kanha', 'female'], ['Sorn Bunrong', 'male'], ['Thach Navy', 'female'],
        ];

        $classes = SchoolClass::orderBy('class_name')->get()->values();

        foreach ($students as $index => [$name, $gender]) {
            $number = $index + 1;
            $code = sprintf('S%04d', $number);
            $class = $classes[intdiv($index, 6)];
            $birthYear = 2026 - $class->grade_level - 6;

            Student::firstOrCreate(
                ['student_code' => $code],
                Student::factory()->raw([
                    'student_code' => $code,
                    'name' => $name,
                    'gender' => $gender,
                    'date_of_birth' => fake()->dateTimeBetween("{$birthYear}-01-01", "{$birthYear}-12-31")->format('Y-m-d'),
                    'email' => strtolower(str_replace(' ', '.', $name)).'@student.school.test',
                    'enrollment_date' => $class->grade_level === 10 ? '2026-09-01' : '2025-09-01',
                    'status' => match ($number) {
                        29 => 'inactive',
                        30 => 'graduated',
                        default => 'active',
                    },
                    'class_id' => $class->id,
                ]),
            );
        }
    }
}
