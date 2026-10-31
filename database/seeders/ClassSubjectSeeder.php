<?php

namespace Database\Seeders;

use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class ClassSubjectSeeder extends Seeder
{
    /**
     * Assign every subject to every class, taught by a teacher from that subject's department.
     *
     * When a department has several teachers, the classes are shared out between them in turn.
     */
    public function run(): void
    {
        $classes = SchoolClass::orderBy('class_name')->get();
        $teachersByDepartment = Teacher::with('department')->orderBy('teacher_code')->get()
            ->groupBy(fn (Teacher $teacher) => $teacher->department->name);

        foreach (Subject::all() as $subject) {
            $departmentName = SubjectSeeder::SUBJECTS[$subject->subject_code][2] ?? null;
            $teachers = $teachersByDepartment->get($departmentName);

            if ($teachers === null) {
                continue;
            }

            foreach ($classes->values() as $index => $class) {
                ClassSubject::firstOrCreate([
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teachers[$index % $teachers->count()]->id,
                ]);
            }
        }
    }
}
