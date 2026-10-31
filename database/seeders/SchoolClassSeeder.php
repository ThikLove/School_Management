<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    /**
     * Seed 5 classes, each with a homeroom teacher.
     */
    public function run(): void
    {
        $classes = [
            ['Grade 10A', 10, 'R-101', 'T001'],
            ['Grade 10B', 10, 'R-102', 'T002'],
            ['Grade 11A', 11, 'R-201', 'T003'],
            ['Grade 11B', 11, 'R-202', 'T004'],
            ['Grade 12A', 12, 'R-301', 'T005'],
        ];

        $teacherIds = Teacher::pluck('id', 'teacher_code');

        foreach ($classes as [$className, $gradeLevel, $room, $homeroomCode]) {
            SchoolClass::firstOrCreate(['class_name' => $className], [
                'grade_level' => $gradeLevel,
                'room_number' => $room,
                'homeroom_teacher_id' => $teacherIds[$homeroomCode],
            ]);
        }
    }
}
