<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnrollmentRequest;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Enrollment::class);

        $filters = $request->only('search', 'class_id', 'academic_year', 'status');

        $enrollments = Enrollment::with(['student', 'schoolClass'])
            ->filter($filters)
            ->orderByDesc('academic_year')
            ->orderByDesc('enrollment_date')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('enrollments.index', [
            'enrollments' => $enrollments,
            'filters' => $filters,
            'classes' => SchoolClass::orderBy('class_name')->pluck('class_name', 'id'),
            'academicYears' => Enrollment::distinct()->orderByDesc('academic_year')->pluck('academic_year'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        Gate::authorize('create', Enrollment::class);

        return view('enrollments.create', $this->formData(new Enrollment([
            'student_id' => $request->integer('student_id') ?: null,
            'enrollment_date' => today(),
            'academic_year' => Enrollment::currentAcademicYear(),
            'status' => 'active',
        ])));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EnrollmentRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            Enrollment::create($request->validated())->syncActiveState();
        });

        return redirect()->route('enrollments.index')
            ->with('success', 'The enrollment was saved.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Enrollment $enrollment): View
    {
        Gate::authorize('update', $enrollment);

        return view('enrollments.edit', $this->formData($enrollment));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EnrollmentRequest $request, Enrollment $enrollment): RedirectResponse
    {
        DB::transaction(function () use ($request, $enrollment) {
            $enrollment->update($request->validated());
            $enrollment->syncActiveState();
        });

        return redirect()->route('enrollments.index')
            ->with('success', 'The enrollment was updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * Deletion policy: completed enrollments are permanent history and cannot be deleted.
     * Active or transferred enrollments may be removed by an admin to correct a mistake.
     */
    public function destroy(Enrollment $enrollment): RedirectResponse
    {
        Gate::authorize('delete', $enrollment);

        if ($enrollment->status === 'completed') {
            return back()->with('error', 'Completed enrollments are part of the student\'s permanent history and cannot be deleted.');
        }

        $enrollment->delete();

        return redirect()->route('enrollments.index')
            ->with('success', 'The enrollment was deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Enrollment $enrollment): array
    {
        return [
            'enrollment' => $enrollment,
            'students' => Student::orderBy('name')->get()
                ->mapWithKeys(fn (Student $student) => [$student->id => "{$student->name} ({$student->student_code})"]),
            'classes' => SchoolClass::orderBy('class_name')->pluck('class_name', 'id'),
        ];
    }
}
