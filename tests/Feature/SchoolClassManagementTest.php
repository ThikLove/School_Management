<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolClassManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_class_with_a_homeroom_teacher(): void
    {
        $teacher = Teacher::factory()->create(['name' => 'Sok Dara']);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('classes.store'), [
                'class_name' => 'Grade 9C',
                'grade_level' => 9,
                'room_number' => 'R-099',
                'homeroom_teacher_id' => $teacher->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($admin)
            ->get(route('classes.index'))
            ->assertSee('Grade 9C')
            ->assertSee('Sok Dara');
    }

    public function test_class_form_is_validated(): void
    {
        SchoolClass::factory()->create(['class_name' => 'Grade 10A']);

        $this->actingAs($this->admin())
            ->post(route('classes.store'), ['class_name' => 'Grade 10A', 'grade_level' => 20, 'homeroom_teacher_id' => 999])
            ->assertSessionHasErrors(['class_name', 'grade_level', 'homeroom_teacher_id']);
    }

    public function test_classes_can_be_filtered_by_grade_and_homeroom_teacher(): void
    {
        $teacher = Teacher::factory()->create();
        SchoolClass::factory()->create(['class_name' => 'Grade 10A', 'grade_level' => 10, 'homeroom_teacher_id' => $teacher->id]);
        SchoolClass::factory()->create(['class_name' => 'Grade 10B', 'grade_level' => 10]);
        SchoolClass::factory()->create(['class_name' => 'Grade 11A', 'grade_level' => 11]);

        $admin = $this->admin();

        $this->actingAs($admin)->get(route('classes.index', ['grade_level' => 10]))
            ->assertSee('Grade 10A')->assertSee('Grade 10B')->assertDontSee('Grade 11A');

        $this->actingAs($admin)->get(route('classes.index', ['grade_level' => 10, 'homeroom_teacher_id' => $teacher->id]))
            ->assertSee('Grade 10A')->assertDontSee('Grade 10B');
    }

    public function test_admin_can_assign_and_remove_subjects_but_not_duplicate_them(): void
    {
        $class = SchoolClass::factory()->create();
        $subject = Subject::factory()->create(['name' => 'Physics']);
        $teacher = Teacher::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('classes.subjects.store', $class), ['subject_id' => $subject->id, 'teacher_id' => $teacher->id])
            ->assertSessionHas('success');

        $this->actingAs($admin)
            ->post(route('classes.subjects.store', $class), ['subject_id' => $subject->id, 'teacher_id' => $teacher->id])
            ->assertSessionHasErrorsIn('assignment', 'subject_id');

        $this->assertSame(1, $class->assignments()->count());

        $this->actingAs($admin)->get(route('classes.show', $class))->assertSee('Physics')->assertSee($teacher->name);

        $this->actingAs($admin)
            ->delete(route('classes.subjects.destroy', [$class, $class->assignments()->first()]))
            ->assertSessionHas('success');

        $this->assertSame(0, $class->assignments()->count());
    }

    public function test_class_with_students_cannot_be_deleted(): void
    {
        $class = Student::factory()->create()->schoolClass;

        $this->actingAs($this->admin())->delete(route('classes.destroy', $class))->assertSessionHas('error');

        $this->assertModelExists($class);
    }

    public function test_empty_class_can_be_deleted(): void
    {
        $class = SchoolClass::factory()->create();

        $this->actingAs($this->admin())->delete(route('classes.destroy', $class))->assertRedirect(route('classes.index'));

        $this->assertModelMissing($class);
    }

    public function test_teachers_only_see_their_own_classes_and_cannot_edit_them(): void
    {
        $user = $this->teacherUser();
        $own = SchoolClass::factory()->create(['class_name' => 'Own Class', 'homeroom_teacher_id' => $user->teacher->id]);
        $other = SchoolClass::factory()->create(['class_name' => 'Other Class']);

        $this->actingAs($user)->get(route('classes.index'))->assertOk()->assertSee('Own Class')->assertDontSee('Other Class');
        $this->actingAs($user)->get(route('classes.show', $own))->assertOk();
        $this->actingAs($user)->get(route('classes.show', $other))->assertForbidden();
        $this->actingAs($user)->get(route('classes.edit', $own))->assertForbidden();
        $this->actingAs($user)->delete(route('classes.destroy', $own))->assertForbidden();
    }
}
