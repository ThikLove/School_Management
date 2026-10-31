<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherRequest;
use App\Models\ClassSubject;
use App\Models\Department;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Teacher::class);

        $filters = $request->only('search', 'department_id');

        $teachers = Teacher::with('department')
            ->withCount('homeroomClasses')
            ->filter($filters)
            ->orderBy('teacher_code')
            ->paginate(10)
            ->withQueryString();

        return view('teachers.index', [
            'teachers' => $teachers,
            'filters' => $filters,
            'departments' => Department::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', Teacher::class);

        return view('teachers.create', $this->formData(new Teacher));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TeacherRequest $request): RedirectResponse
    {
        $teacher = DB::transaction(function () use ($request) {
            $teacher = Teacher::create($request->safe()->except('user_id'));
            $this->linkAccount($teacher, $request->validated('user_id'));

            return $teacher;
        });

        return redirect()->route('teachers.show', $teacher)
            ->with('success', "Teacher \"{$teacher->name}\" was added.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Teacher $teacher): View
    {
        Gate::authorize('view', $teacher);

        $teacher->load(['department', 'user', 'homeroomClasses' => fn ($query) => $query->withCount('students')->orderBy('class_name')]);

        $assignments = ClassSubject::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->get()
            ->sortBy(fn (ClassSubject $assignment) => $assignment->schoolClass->class_name.$assignment->subject->name);

        return view('teachers.show', compact('teacher', 'assignments'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Teacher $teacher): View
    {
        Gate::authorize('update', $teacher);

        return view('teachers.edit', $this->formData($teacher));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        DB::transaction(function () use ($request, $teacher) {
            $teacher->update($request->safe()->except('user_id'));
            $this->linkAccount($teacher, $request->validated('user_id'));
        });

        return redirect()->route('teachers.show', $teacher)
            ->with('success', 'Teacher details were updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * Teachers who still teach subjects must be replaced first; homeroom classes
     * simply lose their homeroom teacher (the foreign key sets it to NULL).
     */
    public function destroy(Teacher $teacher): RedirectResponse
    {
        Gate::authorize('delete', $teacher);

        $assignmentCount = $teacher->assignments()->count();

        if ($assignmentCount > 0) {
            return back()->with('error', "{$teacher->name} still teaches {$assignmentCount} class subject(s). Assign those subjects to another teacher before deleting.");
        }

        $teacher->delete();

        return redirect()->route('teachers.index')
            ->with('success', "Teacher \"{$teacher->name}\" was deleted.");
    }

    /**
     * Link (or unlink) a login account and give that account the teacher role.
     */
    private function linkAccount(Teacher $teacher, ?int $userId): void
    {
        $teacher->user_id = $userId;
        $teacher->save();

        if ($userId !== null) {
            User::whereKey($userId)->update(['role' => User::ROLE_TEACHER]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Teacher $teacher): array
    {
        return [
            'teacher' => $teacher,
            'departments' => Department::orderBy('name')->pluck('name', 'id'),
            'accounts' => $this->linkableAccounts($teacher->user_id),
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
