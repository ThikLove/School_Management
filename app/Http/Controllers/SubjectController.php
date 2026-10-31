<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubjectRequest;
use App\Models\ClassSubject;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Admins see every subject; teachers see what they teach; students see their class's subjects.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Subject::class);

        $filters = $request->only('search');
        $user = $request->user();

        $subjects = Subject::withCount('assignments')
            ->visibleTo($user)
            ->filter($filters)
            ->orderBy('subject_code')
            ->paginate(10)
            ->withQueryString();

        // For teachers and students, show who teaches each subject in "their" classes.
        $assignments = ClassSubject::with(['schoolClass', 'teacher'])
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->when($user->isTeacher(), fn ($query) => $query->where('teacher_id', $user->teacher?->id))
            ->when($user->isStudent(), fn ($query) => $query->where('class_id', $user->student?->class_id))
            ->get()
            ->groupBy('subject_id');

        return view('subjects.index', compact('subjects', 'filters', 'assignments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', Subject::class);

        return view('subjects.create', ['subject' => new Subject]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubjectRequest $request): RedirectResponse
    {
        $subject = Subject::create($request->validated());

        return redirect()->route('subjects.show', $subject)
            ->with('success', "Subject \"{$subject->name}\" was created.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Subject $subject): View
    {
        Gate::authorize('view', $subject);

        $user = $request->user();

        $assignments = ClassSubject::with(['schoolClass', 'teacher'])
            ->where('subject_id', $subject->id)
            ->when($user->isTeacher(), fn ($query) => $query->where('teacher_id', $user->teacher?->id))
            ->when($user->isStudent(), fn ($query) => $query->where('class_id', $user->student?->class_id))
            ->get()
            ->sortBy(fn (ClassSubject $assignment) => $assignment->schoolClass->class_name);

        return view('subjects.show', compact('subject', 'assignments'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subject $subject): View
    {
        Gate::authorize('update', $subject);

        return view('subjects.edit', compact('subject'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SubjectRequest $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validated());

        return redirect()->route('subjects.show', $subject)
            ->with('success', 'Subject details were updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * The subject's class assignments are removed with it (cascade); no history is lost.
     */
    public function destroy(Subject $subject): RedirectResponse
    {
        Gate::authorize('delete', $subject);

        $subject->delete();

        return redirect()->route('subjects.index')
            ->with('success', "Subject \"{$subject->name}\" and its class assignments were deleted.");
    }
}
