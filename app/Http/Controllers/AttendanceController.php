<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Admins see everything, teachers see their classes, students see their own records.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Attendance::class);

        $filters = $request->only('search', 'class_id', 'status', 'date_from', 'date_to');
        $user = $request->user();

        $query = Attendance::visibleTo($user)->filter($filters);

        $summary = (clone $query)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $records = $query->with(['student', 'schoolClass'])
            ->orderByDesc('attendance_date')
            ->orderBy('class_id')
            ->orderBy('student_id')
            ->paginate(15)
            ->withQueryString();

        return view('attendance.index', [
            'records' => $records,
            'filters' => $filters,
            'summary' => $summary,
            'classes' => SchoolClass::visibleTo($user)->orderBy('class_name')->pluck('class_name', 'id'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * The student dropdown is filtered in the browser to the students enrolled in the chosen class.
     */
    public function create(Request $request): View
    {
        Gate::authorize('create', Attendance::class);

        $classes = SchoolClass::visibleTo($request->user())->orderBy('class_name')->get();

        $studentsByClass = Enrollment::with('student')
            ->whereIn('class_id', $classes->pluck('id'))
            ->where('status', 'active')
            ->get()
            ->groupBy('class_id')
            ->map(fn ($enrollments) => $enrollments
                ->map(fn (Enrollment $enrollment) => [
                    'id' => $enrollment->student_id,
                    'name' => "{$enrollment->student->name} ({$enrollment->student->student_code})",
                ])
                ->sortBy('name')
                ->values());

        return view('attendance.create', [
            'attendance' => new Attendance([
                'attendance_date' => today(),
                'status' => 'present',
                'class_id' => $request->integer('class_id') ?: null,
            ]),
            'classes' => $classes->pluck('class_name', 'id'),
            'studentsByClass' => $studentsByClass,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AttendanceRequest $request): RedirectResponse
    {
        $schoolClass = SchoolClass::findOrFail($request->validated('class_id'));
        Gate::authorize('create', [Attendance::class, $schoolClass]);

        $attendance = Attendance::create($request->validated());

        return redirect()->route('attendance.create', ['class_id' => $schoolClass->id])
            ->with('success', "Attendance saved for {$attendance->student->name} on {$attendance->attendance_date->format('d M Y')}.");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance): View
    {
        Gate::authorize('update', $attendance);

        $attendance->load(['student', 'schoolClass']);

        return view('attendance.edit', compact('attendance'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AttendanceRequest $request, Attendance $attendance): RedirectResponse
    {
        $attendance->update($request->validated());

        return redirect()->route('attendance.index')
            ->with('success', "Attendance for {$attendance->student->name} was updated.");
    }

    /**
     * Remove the specified resource from storage.
     *
     * Only admins may delete attendance (to correct a mistaken entry); teachers edit instead.
     */
    public function destroy(Attendance $attendance): RedirectResponse
    {
        Gate::authorize('delete', $attendance);

        $attendance->delete();

        return redirect()->route('attendance.index')
            ->with('success', 'The attendance record was deleted.');
    }
}
