<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;

    private User $studentUser;

    private SchoolClass $ownClass;

    private SchoolClass $otherClass;

    private Student $ownStudent;

    private Student $otherStudent;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'role:admin'])->get('/_test/admin-only', fn () => 'admin area');

        $this->teacherUser = User::factory()->teacher()->create();
        $teacher = Teacher::factory()->for($this->teacherUser)->create();

        $this->ownClass = SchoolClass::factory()->create(['homeroom_teacher_id' => $teacher->id]);
        $this->otherClass = SchoolClass::factory()->create();

        $this->studentUser = User::factory()->student()->create();
        $this->ownStudent = Student::factory()->for($this->studentUser)->create(['class_id' => $this->ownClass->id]);
        $this->otherStudent = Student::factory()->create(['class_id' => $this->otherClass->id]);
    }

    public function test_role_middleware_allows_admins_and_blocks_other_roles(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/_test/admin-only')->assertOk();
        $this->actingAs($this->teacherUser)->get('/_test/admin-only')->assertForbidden();
        $this->actingAs($this->studentUser)->get('/_test/admin-only')->assertForbidden();
    }

    public function test_admins_can_do_everything(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->can('create', Department::class));
        $this->assertTrue($admin->can('delete', $this->otherStudent));
        $this->assertTrue($admin->can('update', $this->otherClass));
    }

    public function test_teachers_can_only_view_their_own_classes_and_students(): void
    {
        $this->assertTrue($this->teacherUser->can('view', $this->ownClass));
        $this->assertFalse($this->teacherUser->can('view', $this->otherClass));

        $this->assertTrue($this->teacherUser->can('view', $this->ownStudent));
        $this->assertFalse($this->teacherUser->can('view', $this->otherStudent));
    }

    public function test_subject_teachers_can_access_the_class_they_teach_in(): void
    {
        $subject = Subject::factory()->create();
        $this->otherClass->subjects()->attach($subject->id, ['teacher_id' => $this->teacherUser->teacher->id]);

        $teacherUser = $this->teacherUser->fresh();

        $this->assertTrue($teacherUser->can('view', $this->otherClass));
        $this->assertTrue($teacherUser->can('view', $subject));
    }

    public function test_teachers_cannot_manage_school_records(): void
    {
        $this->assertFalse($this->teacherUser->can('create', Student::class));
        $this->assertFalse($this->teacherUser->can('update', $this->ownStudent));
        $this->assertFalse($this->teacherUser->can('delete', $this->ownClass));
        $this->assertFalse($this->teacherUser->can('viewAny', Department::class));
    }

    public function test_teachers_record_attendance_only_for_their_classes(): void
    {
        $this->assertTrue($this->teacherUser->can('create', [Attendance::class, $this->ownClass]));
        $this->assertFalse($this->teacherUser->can('create', [Attendance::class, $this->otherClass]));

        $ownRecord = Attendance::factory()->create(['student_id' => $this->ownStudent->id]);
        $otherRecord = Attendance::factory()->create(['student_id' => $this->otherStudent->id]);

        $this->assertTrue($this->teacherUser->can('update', $ownRecord));
        $this->assertFalse($this->teacherUser->can('update', $otherRecord));
        $this->assertFalse($this->teacherUser->can('delete', $ownRecord));
    }

    public function test_students_can_only_view_their_own_records(): void
    {
        $this->assertTrue($this->studentUser->can('view', $this->ownStudent));
        $this->assertFalse($this->studentUser->can('view', $this->otherStudent));

        $this->assertTrue($this->studentUser->can('view', $this->ownClass));
        $this->assertFalse($this->studentUser->can('view', $this->otherClass));

        $ownRecord = Attendance::factory()->create(['student_id' => $this->ownStudent->id]);
        $otherRecord = Attendance::factory()->create(['student_id' => $this->otherStudent->id]);

        $this->assertTrue($this->studentUser->can('view', $ownRecord));
        $this->assertFalse($this->studentUser->can('view', $otherRecord));
        $this->assertFalse($this->studentUser->can('update', $ownRecord));
        $this->assertFalse($this->studentUser->can('viewAny', Student::class));
    }

    public function test_each_role_sees_its_own_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('dashboard'))->assertOk()->assertSee('Total students');

        $this->actingAs($this->teacherUser)
            ->get(route('dashboard'))->assertOk()->assertSee($this->ownClass->class_name)->assertDontSee($this->otherClass->class_name);

        $this->actingAs($this->studentUser)
            ->get(route('dashboard'))->assertOk()->assertSee($this->ownStudent->student_code)->assertSee('Attendance rate');
    }

    public function test_unlinked_accounts_see_a_friendly_message(): void
    {
        $this->actingAs(User::factory()->student()->create())
            ->get(route('dashboard'))->assertOk()->assertSee("Your account isn't linked yet");
    }
}
