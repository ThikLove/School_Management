<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    public const CURRENT_YEAR = '2026-2027';

    public const PREVIOUS_YEAR = '2025-2026';

    /**
     * Seed one current-year enrollment per student, plus last year's
     * completed enrollment for students in grades 11 and 12.
     */
    public function run(): void
    {
        $classesByName = SchoolClass::all()->keyBy('class_name');

        foreach (Student::with('schoolClass')->get() as $student) {
            $class = $student->schoolClass;

            Enrollment::firstOrCreate([
                'student_id' => $student->id,
                'class_id' => $class->id,
                'academic_year' => self::CURRENT_YEAR,
            ], [
                'enrollment_date' => '2026-09-01',
                'status' => match ($student->status) {
                    'inactive' => 'transferred',
                    'graduated' => 'completed',
                    default => 'active',
                },
            ]);

            $previousClass = $classesByName->get($this->previousClassName($class));

            if ($previousClass !== null) {
                Enrollment::firstOrCreate([
                    'student_id' => $student->id,
                    'class_id' => $previousClass->id,
                    'academic_year' => self::PREVIOUS_YEAR,
                ], [
                    'enrollment_date' => '2025-09-01',
                    'status' => 'completed',
                ]);
            }
        }
    }

    /**
     * The class one grade lower with the same section letter, e.g. "Grade 11B" => "Grade 10B".
     */
    private function previousClassName(SchoolClass $class): string
    {
        $section = substr($class->class_name, -1);

        return 'Grade '.($class->grade_level - 1).$section;
    }
}
