<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_search_and_update_subjects(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('subjects.store'), ['subject_code' => 'MATH101', 'name' => 'Mathematics'])
            ->assertSessionHas('success');
        Subject::factory()->create(['subject_code' => 'BIO101', 'name' => 'Biology']);

        $this->actingAs($admin)->get(route('subjects.index', ['search' => 'math']))->assertSee('Mathematics')->assertDontSee('Biology');
        $this->actingAs($admin)->get(route('subjects.index', ['search' => 'BIO1']))->assertSee('Biology')->assertDontSee('Mathematics');

        $subject = Subject::where('subject_code', 'MATH101')->firstOrFail();
        $this->actingAs($admin)->put(route('subjects.update', $subject), ['subject_code' => 'MATH101', 'name' => 'Advanced Mathematics']);
        $this->assertSame('Advanced Mathematics', $subject->fresh()->name);
    }

    public function test_subject_code_must_be_unique(): void
    {
        Subject::factory()->create(['subject_code' => 'MATH101']);

        $this->actingAs($this->admin())
            ->post(route('subjects.store'), ['subject_code' => 'MATH101', 'name' => 'Duplicate'])
            ->assertSessionHasErrors('subject_code');
    }

    public function test_deleting_a_subject_removes_its_class_assignments(): void
    {
        $subject = Subject::factory()->create();
        SchoolClass::factory()->create()->subjects()->attach($subject, ['teacher_id' => Teacher::factory()->create()->id]);

        $this->actingAs($this->admin())->delete(route('subjects.destroy', $subject))->assertRedirect(route('subjects.index'));

        $this->assertModelMissing($subject);
        $this->assertDatabaseCount('class_subject', 0);
    }

    public function test_teachers_see_only_the_subjects_they_teach(): void
    {
        $user = $this->teacherUser();
        $taught = Subject::factory()->create(['name' => 'Taught Subject']);
        $notTaught = Subject::factory()->create(['name' => 'Other Subject']);
        SchoolClass::factory()->create()->subjects()->attach($taught, ['teacher_id' => $user->teacher->id]);

        $this->actingAs($user)->get(route('subjects.index'))->assertOk()->assertSee('Taught Subject')->assertDontSee('Other Subject');
        $this->actingAs($user)->get(route('subjects.show', $notTaught))->assertForbidden();
        $this->actingAs($user)->get(route('subjects.create'))->assertForbidden();
    }

    public function test_students_see_the_subjects_of_their_class(): void
    {
        $class = SchoolClass::factory()->create();
        $user = User::factory()->student()->create();
        Student::factory()->for($user)->create(['class_id' => $class->id]);
        $classSubject = Subject::factory()->create(['name' => 'Class Subject']);
        Subject::factory()->create(['name' => 'Not My Subject']);
        $class->subjects()->attach($classSubject, ['teacher_id' => Teacher::factory()->create(['name' => 'Ms Teacher'])->id]);

        $this->actingAs($user)->get(route('subjects.index'))
            ->assertOk()
            ->assertSee('Class Subject')
            ->assertSee('Ms Teacher')
            ->assertDontSee('Not My Subject');
    }
}
