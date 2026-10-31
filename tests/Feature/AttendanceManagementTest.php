<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;

    private SchoolClass $ownClass;

    private SchoolClass $otherClass;

    private Student $ownStudent;

    private Student $otherStudent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherUser = $this->teacherUser();
        $this->ownClass = SchoolClass::factory()->create(['homeroom_teacher_id' => $this->teacherUser->teacher->id]);
        $this->otherClass = SchoolClass::factory()->create();
        $this->ownStudent = $this->enrolledStudent($this->ownClass, ['name' => 'Own Student']);
        $this->otherStudent = $this->enrolledStudent($this->otherClass, ['name' => 'Other Student']);
    }

    /**
     * @return array<string, mixed>
     */
    private function record(array $overrides = []): array
    {
        return [
            'class_id' => $this->ownClass->id,
            'student_id' => $this->ownStudent->id,
            'attendance_date' => today()->toDateString(),
            'status' => 'present',
            ...$overrides,
        ];
    }

    public function test_create_form_only_offers_the_teachers_classes_and_enrolled_students(): void
    {
        $this->actingAs($this->teacherUser)
            ->get(route('attendance.create'))
            ->assertOk()
            ->assertSee($this->ownClass->class_name)
            ->assertDontSee($this->otherClass->class_name)
            ->assertSee('Own Student')
            ->assertDontSee('Other Student');
    }

    public function test_teacher_can_record_attendance_for_their_class(): void
    {
        $this->actingAs($this->teacherUser)
            ->post(route('attendance.store'), $this->record(['status' => 'late', 'remarks' => 'Bus delay']))
            ->assertRedirect(route('attendance.create', ['class_id' => $this->ownClass->id]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', ['student_id' => $this->ownStudent->id, 'status' => 'late', 'remarks' => 'Bus delay']);
    }

    public function test_teacher_cannot_record_attendance_for_another_class(): void
    {
        $this->actingAs($this->teacherUser)
            ->post(route('attendance.store'), $this->record(['class_id' => $this->otherClass->id, 'student_id' => $this->otherStudent->id]))
            ->assertSessionHasErrors('class_id');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_student_must_be_enrolled_in_the_selected_class(): void
    {
        $this->actingAs($this->admin())
            ->post(route('attendance.store'), $this->record(['student_id' => $this->otherStudent->id]))
            ->assertSessionHasErrors(['student_id' => 'The selected student is not enrolled in this class.']);
    }

    public function test_duplicate_attendance_is_rejected(): void
    {
        $this->actingAs($this->teacherUser)->post(route('attendance.store'), $this->record());

        $this->actingAs($this->teacherUser)
            ->post(route('attendance.store'), $this->record(['status' => 'absent']))
            ->assertSessionHasErrors(['attendance_date' => 'Attendance for this student, class and date has already been recorded.']);

        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_future_dates_are_rejected(): void
    {
        $this->actingAs($this->teacherUser)
            ->post(route('attendance.store'), $this->record(['attendance_date' => today()->addDay()->toDateString()]))
            ->assertSessionHasErrors('attendance_date');
    }

    public function test_teacher_can_update_only_their_own_class_records_and_cannot_delete(): void
    {
        $own = Attendance::factory()->create(['student_id' => $this->ownStudent->id, 'attendance_date' => today()->subDay()]);
        $other = Attendance::factory()->create(['student_id' => $this->otherStudent->id, 'attendance_date' => today()->subDay()]);

        $this->actingAs($this->teacherUser)
            ->put(route('attendance.update', $own), ['attendance_date' => $own->attendance_date->toDateString(), 'status' => 'excused', 'remarks' => 'Doctor note'])
            ->assertRedirect(route('attendance.index'));
        $this->assertSame('excused', $own->fresh()->status);

        $this->actingAs($this->teacherUser)->put(route('attendance.update', $other), ['attendance_date' => today()->toDateString(), 'status' => 'absent'])->assertForbidden();
        $this->actingAs($this->teacherUser)->delete(route('attendance.destroy', $own))->assertForbidden();

        $this->assertModelExists($own);
    }

    public function test_admin_can_delete_attendance(): void
    {
        $record = Attendance::factory()->create(['student_id' => $this->ownStudent->id]);

        $this->actingAs($this->admin())->delete(route('attendance.destroy', $record))->assertRedirect(route('attendance.index'));

        $this->assertModelMissing($record);
    }

    public function test_attendance_can_be_filtered_by_date_range_and_status(): void
    {
        Attendance::factory()->create(['student_id' => $this->ownStudent->id, 'attendance_date' => '2026-10-01', 'status' => 'absent', 'remarks' => 'IN RANGE ABSENT']);
        Attendance::factory()->create(['student_id' => $this->ownStudent->id, 'attendance_date' => '2026-10-02', 'status' => 'present', 'remarks' => 'IN RANGE PRESENT']);
        Attendance::factory()->create(['student_id' => $this->ownStudent->id, 'attendance_date' => '2026-09-01', 'status' => 'absent', 'remarks' => 'OUT OF RANGE']);

        $this->actingAs($this->admin())
            ->get(route('attendance.index', ['date_from' => '2026-10-01', 'date_to' => '2026-10-31', 'status' => 'absent']))
            ->assertOk()
            ->assertSee('IN RANGE ABSENT')
            ->assertDontSee('IN RANGE PRESENT')
            ->assertDontSee('OUT OF RANGE');
    }

    public function test_students_see_only_their_own_attendance(): void
    {
        $studentUser = User::factory()->student()->create();
        $this->ownStudent->user()->associate($studentUser)->save();
        Attendance::factory()->create(['student_id' => $this->ownStudent->id, 'remarks' => 'MY RECORD']);
        Attendance::factory()->create(['student_id' => $this->otherStudent->id, 'remarks' => 'NOT MY RECORD']);

        $this->actingAs($studentUser)->get(route('attendance.index'))->assertOk()->assertSee('MY RECORD')->assertDontSee('NOT MY RECORD');
        $this->actingAs($studentUser)->get(route('attendance.create'))->assertForbidden();
    }
}
