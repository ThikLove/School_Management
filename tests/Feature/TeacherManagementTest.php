<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_list_shows_department_names_and_filters_by_department(): void
    {
        $science = Department::factory()->create(['name' => 'Science']);
        $english = Department::factory()->create(['name' => 'English']);
        Teacher::factory()->create(['name' => 'Chan Sophea', 'department_id' => $science->id]);
        Teacher::factory()->create(['name' => 'Keo Rathana', 'department_id' => $english->id]);

        $this->actingAs($this->admin())
            ->get(route('teachers.index', ['department_id' => $science->id]))
            ->assertOk()
            ->assertSee('Chan Sophea')
            ->assertSee('Science')
            ->assertDontSee('Keo Rathana');
    }

    public function test_teachers_can_be_searched_by_name_or_code(): void
    {
        Teacher::factory()->create(['name' => 'Sok Dara', 'teacher_code' => 'T001']);
        Teacher::factory()->create(['name' => 'Lim Visal', 'teacher_code' => 'T002']);

        $admin = $this->admin();

        $this->actingAs($admin)->get(route('teachers.index', ['search' => 'T002']))->assertSee('Lim Visal')->assertDontSee('Sok Dara');
        $this->actingAs($admin)->get(route('teachers.index', ['search' => 'dara']))->assertSee('Sok Dara')->assertDontSee('Lim Visal');
    }

    public function test_admin_can_create_a_teacher_and_link_a_login_account(): void
    {
        $department = Department::factory()->create();
        $account = User::factory()->student()->create();

        $this->actingAs($this->admin())
            ->post(route('teachers.store'), [
                'teacher_code' => 'T100',
                'name' => 'New Teacher',
                'gender' => 'female',
                'email' => 'new.teacher@school.test',
                'hire_date' => '2024-08-01',
                'department_id' => $department->id,
                'user_id' => $account->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $teacher = Teacher::where('teacher_code', 'T100')->firstOrFail();
        $this->assertTrue($teacher->department->is($department));
        $this->assertTrue($teacher->user->is($account));
        $this->assertSame(User::ROLE_TEACHER, $account->fresh()->role);
    }

    public function test_teacher_form_is_validated(): void
    {
        Teacher::factory()->create(['teacher_code' => 'T001', 'email' => 'taken@school.test']);

        $this->actingAs($this->admin())
            ->post(route('teachers.store'), [
                'teacher_code' => 'T001',
                'email' => 'taken@school.test',
                'gender' => 'robot',
                'hire_date' => now()->addYear()->toDateString(),
                'department_id' => 999,
            ])
            ->assertSessionHasErrors(['teacher_code', 'name', 'email', 'gender', 'hire_date', 'department_id']);
    }

    public function test_admin_accounts_cannot_be_linked_to_a_teacher(): void
    {
        $teacher = Teacher::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('teachers.update', $teacher), [...$teacher->only(['teacher_code', 'name', 'gender', 'email', 'department_id']), 'hire_date' => '2020-01-01', 'user_id' => $admin->id])
            ->assertSessionHasErrors('user_id');
    }

    public function test_teacher_who_teaches_subjects_cannot_be_deleted(): void
    {
        $teacher = Teacher::factory()->create();
        SchoolClass::factory()->withoutHomeroomTeacher()->create()->subjects()->attach(Subject::factory()->create(), ['teacher_id' => $teacher->id]);

        $this->actingAs($this->admin())->delete(route('teachers.destroy', $teacher))->assertSessionHas('error');

        $this->assertModelExists($teacher);
    }

    public function test_deleting_a_homeroom_teacher_leaves_the_class_without_one(): void
    {
        $class = SchoolClass::factory()->create();
        $teacher = $class->homeroomTeacher;

        $this->actingAs($this->admin())->delete(route('teachers.destroy', $teacher))->assertRedirect(route('teachers.index'));

        $this->assertModelMissing($teacher);
        $this->assertNull($class->fresh()->homeroom_teacher_id);
    }
}
