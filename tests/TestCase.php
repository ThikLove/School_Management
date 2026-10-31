<?php

namespace Tests;

use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function admin(): User
    {
        return User::factory()->admin()->create();
    }

    /**
     * A teacher login account linked to a teacher profile.
     */
    protected function teacherUser(): User
    {
        $user = User::factory()->teacher()->create();
        Teacher::factory()->for($user)->create();

        return $user;
    }

    /**
     * A student placed in the class with an active current-year enrollment.
     */
    protected function enrolledStudent(SchoolClass $schoolClass, array $attributes = []): Student
    {
        $student = Student::factory()->create(['class_id' => $schoolClass->id, ...$attributes]);

        Enrollment::factory()->create([
            'student_id' => $student->id,
            'class_id' => $schoolClass->id,
            'academic_year' => Enrollment::currentAcademicYear(),
            'status' => 'active',
        ]);

        return $student;
    }
}
