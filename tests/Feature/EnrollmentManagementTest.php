<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_enroll_a_student(): void
    {
        $student = Student::factory()->create();
        $class = SchoolClass::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('enrollments.store'), [
                'student_id' => $student->id,
                'class_id' => $class->id,
                'enrollment_date' => '2025-09-01',
                'academic_year' => '2025-2026',
                'status' => 'completed',
            ])
            ->assertRedirect(route('enrollments.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('enrollments', ['student_id' => $student->id, 'class_id' => $class->id, 'academic_year' => '2025-2026']);
    }

    public function test_duplicate_enrollment_for_the_same_year_is_rejected(): void
    {
        $class = SchoolClass::factory()->create();
        $student = $this->enrolledStudent($class);

        $this->actingAs($this->admin())
            ->post(route('enrollments.store'), [
                'student_id' => $student->id,
                'class_id' => $class->id,
                'enrollment_date' => today()->toDateString(),
                'academic_year' => Enrollment::currentAcademicYear(),
                'status' => 'active',
            ])
            ->assertSessionHasErrors(['student_id' => 'This student is already enrolled in the selected class for that academic year.']);

        $this->assertSame(1, $student->enrollments()->count());
    }

    public function test_enrollment_input_is_validated(): void
    {
        $this->actingAs($this->admin())
            ->post(route('enrollments.store'), [
                'student_id' => 999,
                'class_id' => 999,
                'enrollment_date' => 'not-a-date',
                'academic_year' => '2026-2030',
                'status' => 'paused',
            ])
            ->assertSessionHasErrors(['student_id', 'class_id', 'enrollment_date', 'academic_year', 'status']);
    }

    public function test_active_enrollment_for_the_current_year_moves_the_student(): void
    {
        $oldClass = SchoolClass::factory()->create();
        $newClass = SchoolClass::factory()->create();
        $student = $this->enrolledStudent($oldClass);

        $this->actingAs($this->admin())->post(route('enrollments.store'), [
            'student_id' => $student->id,
            'class_id' => $newClass->id,
            'enrollment_date' => today()->toDateString(),
            'academic_year' => Enrollment::currentAcademicYear(),
            'status' => 'active',
        ]);

        $this->assertSame($newClass->id, $student->fresh()->class_id);
        $this->assertSame('transferred', $student->enrollments()->where('class_id', $oldClass->id)->value('status'));
    }

    public function test_completed_enrollments_cannot_be_deleted(): void
    {
        $enrollment = Enrollment::factory()->create(['status' => 'completed']);

        $this->actingAs($this->admin())->delete(route('enrollments.destroy', $enrollment))->assertSessionHas('error');

        $this->assertModelExists($enrollment);
    }

    public function test_enrollments_can_be_filtered(): void
    {
        $classA = SchoolClass::factory()->create();
        Enrollment::factory()->create(['student_id' => Student::factory()->create(['name' => 'Year A Student', 'class_id' => $classA->id]), 'academic_year' => '2025-2026', 'status' => 'completed']);
        Enrollment::factory()->create(['student_id' => Student::factory()->create(['name' => 'Year B Student']), 'academic_year' => '2026-2027']);

        $this->actingAs($this->admin())
            ->get(route('enrollments.index', ['academic_year' => '2025-2026', 'status' => 'completed', 'class_id' => $classA->id, 'search' => 'Year']))
            ->assertOk()
            ->assertSee('Year A Student')
            ->assertDontSee('Year B Student');
    }

    public function test_teachers_cannot_manage_enrollments(): void
    {
        $this->actingAs($this->teacherUser())->get(route('enrollments.index'))->assertForbidden();
    }
}
