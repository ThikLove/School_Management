<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_and_search_departments(): void
    {
        Department::factory()->create(['name' => 'Science']);
        Department::factory()->create(['name' => 'Mathematics']);

        $this->actingAs($this->admin())
            ->get(route('departments.index', ['search' => 'sci']))
            ->assertOk()
            ->assertSee('Science')
            ->assertDontSee('Mathematics');
    }

    public function test_admin_can_create_view_and_update_a_department(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('departments.store'), ['name' => 'Arts', 'description' => 'Music and drawing'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $department = Department::where('name', 'Arts')->firstOrFail();

        $this->actingAs($admin)->get(route('departments.show', $department))->assertOk()->assertSee('Music and drawing');

        $this->actingAs($admin)
            ->put(route('departments.update', $department), ['name' => 'Fine Arts'])
            ->assertRedirect(route('departments.show', $department));

        $this->assertSame('Fine Arts', $department->fresh()->name);
    }

    public function test_department_name_must_be_unique(): void
    {
        Department::factory()->create(['name' => 'Science']);

        $this->actingAs($this->admin())
            ->post(route('departments.store'), ['name' => 'Science'])
            ->assertSessionHasErrors('name');
    }

    public function test_department_with_teachers_cannot_be_deleted(): void
    {
        $department = Teacher::factory()->create()->department;

        $this->actingAs($this->admin())
            ->delete(route('departments.destroy', $department))
            ->assertSessionHas('error');

        $this->assertModelExists($department);
    }

    public function test_empty_department_can_be_deleted(): void
    {
        $department = Department::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('departments.destroy', $department))
            ->assertRedirect(route('departments.index'));

        $this->assertModelMissing($department);
    }

    public function test_teachers_and_students_cannot_manage_departments(): void
    {
        $this->actingAs($this->teacherUser())->get(route('departments.index'))->assertForbidden();
        $this->actingAs($this->teacherUser())->post(route('departments.store'), ['name' => 'Hack'])->assertForbidden();

        $this->assertDatabaseMissing('departments', ['name' => 'Hack']);
    }
}
