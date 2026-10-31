<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolClassRequest;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Admins see every class; teachers only see the classes they supervise or teach.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', SchoolClass::class);

        $filters = $request->only('search', 'grade_level', 'homeroom_teacher_id');

        $classes = SchoolClass::with('homeroomTeacher')
            ->withCount(['students', 'assignments'])
            ->visibleTo($request->user())
            ->filter($filters)
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->paginate(10)
            ->withQueryString();

        return view('classes.index', [
            'classes' => $classes,
            'filters' => $filters,
            'gradeLevels' => SchoolClass::distinct()->orderBy('grade_level')->pluck('grade_level'),
            'teachers' => Teacher::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', SchoolClass::class);

        return view('classes.create', [
            'schoolClass' => new SchoolClass,
            'teachers' => Teacher::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SchoolClassRequest $request): RedirectResponse
    {
        $schoolClass = SchoolClass::create($request->validated());

        return redirect()->route('classes.show', $schoolClass)
            ->with('success', "Class \"{$schoolClass->class_name}\" was created.");
    }

    /**
     * Display the specified resource.
     */
    public function show(SchoolClass $schoolClass): View
    {
        Gate::authorize('view', $schoolClass);

        $schoolClass->load([
            'homeroomTeacher',
            'students' => fn ($query) => $query->orderBy('name'),
        ]);

        $assignments = ClassSubject::with(['subject', 'teacher'])
            ->where('class_id', $schoolClass->id)
            ->get()
            ->sortBy(fn (ClassSubject $assignment) => $assignment->subject->name);

        return view('classes.show', [
            'schoolClass' => $schoolClass,
            'assignments' => $assignments,
            'subjects' => Subject::orderBy('name')->pluck('name', 'id'),
            'teachers' => Teacher::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SchoolClass $schoolClass): View
    {
        Gate::authorize('update', $schoolClass);

        return view('classes.edit', [
            'schoolClass' => $schoolClass,
            'teachers' => Teacher::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SchoolClassRequest $request, SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->update($request->validated());

        return redirect()->route('classes.show', $schoolClass)
            ->with('success', 'Class details were updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * Classes with students or history (enrollments, attendance) cannot be deleted.
     */
    public function destroy(SchoolClass $schoolClass): RedirectResponse
    {
        Gate::authorize('delete', $schoolClass);

        $schoolClass->loadCount(['students', 'enrollments', 'attendances']);

        if ($schoolClass->students_count + $schoolClass->enrollments_count + $schoolClass->attendances_count > 0) {
            return back()->with('error', "\"{$schoolClass->class_name}\" still has {$schoolClass->students_count} student(s), {$schoolClass->enrollments_count} enrollment(s) and {$schoolClass->attendances_count} attendance record(s), so it cannot be deleted.");
        }

        $schoolClass->delete();

        return redirect()->route('classes.index')
            ->with('success', "Class \"{$schoolClass->class_name}\" was deleted.");
    }
}
