<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validData(SchoolClass $class, array $overrides = []): array
    {
        return [
            'student_code' => 'S9001',
            'name' => 'Chea Vibol',
            'gender' => 'male',
            'date_of_birth' => '2010-05-01',
            'email' => 'vibol@student.school.test',
            'enrollment_date' => '2026-09-01',
            'status' => 'active',
            'class_id' => $class->id,
            ...$overrides,
        ];
    }

    public function test_creating_a_student_also_enrolls_them_in_the_class(): void
    {
        $class = SchoolClass::factory()->create(['class_name' => 'Grade 10A']);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('students.store'), $this->validData($class))->assertRedirect()->assertSessionHas('success');

        $student = Student::where('student_code', 'S9001')->firstOrFail();
        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'class_id' => $class->id,
            'academic_year' => Enrollment::currentAcademicYear(),
            'status' => 'active',
        ]);

        // The list shows the class name through the relationship, not the raw class_id.
        $this->actingAs($admin)->get(route('students.index'))->assertSee('Chea Vibol')->assertSee('Grade 10A');
    }

    public function test_student_form_is_validated(): void
    {
        $class = SchoolClass::factory()->create();
        Student::factory()->create(['student_code' => 'S9001']);

        $this->actingAs($this->admin())
            ->post(route('students.store'), $this->validData($class, [
                'name' => '',
                'gender' => 'x',
                'date_of_birth' => now()->addDay()->toDateString(),
                'status' => 'expelled',
                'class_id' => 999,
            ]))
            ->assertSessionHasErrors(['student_code', 'name', 'gender', 'date_of_birth', 'status', 'class_id']);
    }

    public function test_moving_a_student_to_another_class_records_a_transfer(): void
    {
        $oldClass = SchoolClass::factory()->create();
        $newClass = SchoolClass::factory()->create();
        $student = $this->enrolledStudent($oldClass);

        $this->actingAs($this->admin())
            ->put(route('students.update', $student), [
                ...$student->only(['student_code', 'name', 'gender', 'email', 'status']),
                'date_of_birth' => $student->date_of_birth->toDateString(),
                'enrollment_date' => $student->enrollment_date->toDateString(),
                'class_id' => $newClass->id,
            ])
            ->assertRedirect(route('students.show', $student));

        $year = Enrollment::currentAcademicYear();
        $this->assertSame($newClass->id, $student->fresh()->class_id);
        $this->assertDatabaseHas('enrollments', ['student_id' => $student->id, 'class_id' => $oldClass->id, 'academic_year' => $year, 'status' => 'transferred']);
        $this->assertDatabaseHas('enrollments', ['student_id' => $student->id, 'class_id' => $newClass->id, 'academic_year' => $year, 'status' => 'active']);
    }

    public function test_students_can_be_searched_and_filtered_with_combined_filters(): void
    {
        $classA = SchoolClass::factory()->create();
        $classB = SchoolClass::factory()->create();
        Student::factory()->create(['name' => 'Dara Male A', 'gender' => 'male', 'class_id' => $classA->id]);
        Student::factory()->create(['name' => 'Dara Female A', 'gender' => 'female', 'class_id' => $classA->id]);
        Student::factory()->create(['name' => 'Dara Male B', 'gender' => 'male', 'class_id' => $classB->id]);
        Student::factory()->graduated()->create(['name' => 'Dara Graduated A', 'gender' => 'male', 'class_id' => $classA->id]);

        $this->actingAs($this->admin())
            ->get(route('students.index', ['search' => 'dara', 'class_id' => $classA->id, 'gender' => 'male', 'status' => 'active']))
            ->assertOk()
            ->assertSee('Dara Male A')
            ->assertDontSee('Dara Female A')
            ->assertDontSee('Dara Male B')
            ->assertDontSee('Dara Graduated A')
            ->assertSee('value="dara"', false);
    }

    public function test_student_list_is_paginated_and_keeps_the_query_string(): void
    {
        Student::factory()->count(12)->create(['name' => 'Paged Student']);

        $this->actingAs($this->admin())
            ->get(route('students.index', ['search' => 'Paged']))
            ->assertOk()
            ->assertSee('search=Paged&amp;page=2', false);
    }

    public function test_student_with_attendance_history_cannot_be_deleted(): void
    {
        $student = $this->enrolledStudent(SchoolClass::factory()->create());
        Attendance::factory()->create(['student_id' => $student->id]);

        $this->actingAs($this->admin())->delete(route('students.destroy', $student))->assertSessionHas('error');

        $this->assertModelExists($student);
    }

    public function test_student_created_by_mistake_can_be_deleted_with_their_enrollment(): void
    {
        $student = $this->enrolledStudent(SchoolClass::factory()->create());

        $this->actingAs($this->admin())->delete(route('students.destroy', $student))->assertRedirect(route('students.index'));

        $this->assertModelMissing($student);
        $this->assertDatabaseMissing('enrollments', ['student_id' => $student->id]);
    }

    public function test_teachers_only_see_students_in_their_classes(): void
    {
        $user = $this->teacherUser();
        $ownClass = SchoolClass::factory()->create(['homeroom_teacher_id' => $user->teacher->id]);
        $own = Student::factory()->create(['name' => 'Own Student', 'class_id' => $ownClass->id]);
        $other = Student::factory()->create(['name' => 'Other Student']);

        $this->actingAs($user)->get(route('students.index'))->assertOk()->assertSee('Own Student')->assertDontSee('Other Student');
        $this->actingAs($user)->get(route('students.show', $own))->assertOk();
        $this->actingAs($user)->get(route('students.show', $other))->assertForbidden();
        $this->actingAs($user)->get(route('students.create'))->assertForbidden();
        $this->actingAs($user)->delete(route('students.destroy', $own))->assertForbidden();
    }

    public function test_students_only_see_their_own_profile(): void
    {
        $user = User::factory()->student()->create();
        $own = Student::factory()->for($user)->create();
        $other = Student::factory()->create();

        $this->actingAs($user)->get(route('students.show', $own))->assertOk()->assertSee($own->student_code);
        $this->actingAs($user)->get(route('students.show', $other))->assertForbidden();
        $this->actingAs($user)->get(route('students.index'))->assertForbidden();
        $this->actingAs($user)->get(route('students.edit', $own))->assertForbidden();
    }
}
