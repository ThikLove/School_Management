<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Admins see every student; teachers only see students in their classes.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Student::class);

        $filters = $request->only('search', 'class_id', 'gender', 'status');

        $students = Student::with('schoolClass')
            ->visibleTo($request->user())
            ->filter($filters)
            ->orderBy('student_code')
            ->paginate(10)
            ->withQueryString();

        return view('students.index', [
            'students' => $students,
            'filters' => $filters,
            'classes' => SchoolClass::visibleTo($request->user())->orderBy('class_name')->pluck('class_name', 'id'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', Student::class);

        return view('students.create', $this->formData(new Student([
            'status' => 'active',
            'enrollment_date' => today(),
        ])));
    }

    /**
     * Store a newly created resource in storage.
     *
     * The student and their first enrollment are saved together in one transaction.
     */
    public function store(StudentRequest $request): RedirectResponse
    {
        $student = DB::transaction(function () use ($request) {
            $student = Student::create($request->safe()->except('user_id'));
            $this->linkAccount($student, $request->validated('user_id'));

            if ($student->status === 'active') {
                $this->enrollInCurrentClass($student, $student->enrollment_date->toDateString());
            }

            return $student;
        });

        return redirect()->route('students.show', $student)
            ->with('success', "Student \"{$student->name}\" was added and enrolled in {$student->schoolClass->class_name}.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student): View
    {
        Gate::authorize('view', $student);

        $student->load([
            'schoolClass.homeroomTeacher',
            'user',
            'enrollments' => fn ($query) => $query->with('schoolClass')->orderByDesc('academic_year'),
        ]);

        $attendanceSummary = $student->attendances()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $recentAttendance = $student->attendances()
            ->with('schoolClass')
            ->orderByDesc('attendance_date')
            ->limit(10)
            ->get();

        return view('students.show', compact('student', 'attendanceSummary', 'recentAttendance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student): View
    {
        Gate::authorize('update', $student);

        return view('students.edit', $this->formData($student));
    }

    /**
     * Update the specified resource in storage.
     *
     * Moving an active student to another class also records the move in the
     * enrollment history (old enrollment "transferred", new one "active").
     */
    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        DB::transaction(function () use ($request, $student) {
            $student->fill($request->safe()->except('user_id'));
            $classChanged = $student->isDirty('class_id');
            $student->save();

            $this->linkAccount($student, $request->validated('user_id'));

            if ($classChanged && $student->status === 'active') {
                $this->enrollInCurrentClass($student, today()->toDateString());
            }
        });

        return redirect()->route('students.show', $student)
            ->with('success', 'Student details were updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * Deletion policy: a student can only be deleted when the record was created by
     * mistake, i.e. they have no attendance and no completed or transferred enrollments.
     * Otherwise the history is kept and the status should be set to inactive or graduated.
     */
    public function destroy(Student $student): RedirectResponse
    {
        Gate::authorize('delete', $student);

        $hasHistory = $student->attendances()->exists()
            || $student->enrollments()->where('status', '!=', 'active')->exists();

        if ($hasHistory) {
            return back()->with('error', "{$student->name} has attendance or enrollment history, so the record is kept. Change the status to \"inactive\" or \"graduated\" instead.");
        }

        DB::transaction(function () use ($student) {
            $student->enrollments()->delete();
            $student->delete();
        });

        return redirect()->route('students.index')
            ->with('success', "Student \"{$student->name}\" was deleted.");
    }

    /**
     * Create or re-activate the current-year enrollment for the student's class.
     */
    private function enrollInCurrentClass(Student $student, string $enrollmentDate): void
    {
        Enrollment::updateOrCreate(
            [
                'student_id' => $student->id,
                'class_id' => $student->class_id,
                'academic_year' => Enrollment::currentAcademicYear(),
            ],
            ['enrollment_date' => $enrollmentDate, 'status' => 'active'],
        )->syncActiveState();
    }

    /**
     * Link (or unlink) a login account and give that account the student role.
     */
    private function linkAccount(Student $student, ?int $userId): void
    {
        $student->user_id = $userId;
        $student->save();

        if ($userId !== null) {
            User::whereKey($userId)->update(['role' => User::ROLE_STUDENT]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Student $student): array
    {
        return [
            'student' => $student,
            'classes' => SchoolClass::orderBy('class_name')->pluck('class_name', 'id'),
            'accounts' => $this->linkableAccounts($student->user_id),
        ];
    }

    /**
     * Non-admin login accounts that are not linked to anyone yet, plus the current one.
     *
     * @return Collection<int, string>
     */
    private function linkableAccounts(?int $currentUserId): Collection
    {
        return User::where('role', '!=', User::ROLE_ADMIN)
            ->where(fn ($query) => $query
                ->where(fn ($query) => $query->doesntHave('teacher')->doesntHave('student'))
                ->orWhere('id', $currentUserId))
            ->orderBy('email')
            ->get()
            ->mapWithKeys(fn (User $user) => [$user->id => "{$user->email} ({$user->name})"]);
    }
}
