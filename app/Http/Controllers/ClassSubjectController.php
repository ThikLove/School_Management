<?php

namespace App\Http\Controllers;

use App\Models\ClassSubject;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Assigns subjects (and the teacher who teaches them) to a class.
 */
class ClassSubjectController extends Controller
{
    /**
     * Assign a subject and teacher to the class.
     */
    public function store(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        Gate::authorize('update', $schoolClass);

        $validated = $request->validateWithBag('assignment', [
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id'),
                Rule::unique('class_subject')
                    ->where('class_id', $schoolClass->id)
                    ->where('teacher_id', $request->input('teacher_id')),
            ],
            'teacher_id' => ['required', 'integer', Rule::exists('teachers', 'id')],
        ], [
            'subject_id.unique' => 'This teacher already teaches that subject in this class.',
        ]);

        $schoolClass->assignments()->create($validated);

        return back()->with('success', 'Subject assigned to the class.');
    }

    /**
     * Remove a subject assignment from the class.
     */
    public function destroy(SchoolClass $schoolClass, ClassSubject $assignment): RedirectResponse
    {
        Gate::authorize('update', $schoolClass);

        abort_unless($assignment->class_id === $schoolClass->id, 404);

        $assignment->delete();

        return back()->with('success', 'Subject assignment removed.');
    }
}
