<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * How many recorded school days the attendance chart shows.
     */
    private const CHART_DAYS = 10;

    /**
     * Show the dashboard for the logged-in user's role.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return view('dashboard', $this->adminData($user));
        }

        if ($user->isTeacher() && $user->teacher !== null) {
            return view('dashboard', [
                'teacher' => $user->teacher,
                'classes' => SchoolClass::visibleTo($user)
                    ->withCount('students')
                    ->orderBy('class_name')
                    ->get(),
                ...$this->attendanceData($user),
            ]);
        }

        if ($user->isStudent() && $user->student !== null) {
            $student = $user->student->load('schoolClass.homeroomTeacher');

            return view('dashboard', [
                'student' => $student,
                'attendance' => Attendance::where('student_id', $student->id)
                    ->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status'),
            ]);
        }

        // A teacher or student account that an admin has not linked to a profile yet.
        return view('dashboard', ['unlinked' => true]);
    }

    /**
     * Every number on the admin dashboard comes from a database query.
     *
     * @return array<string, mixed>
     */
    private function adminData(User $user): array
    {
        return [
            'stats' => [
                'students' => Student::count(),
                'teachers' => Teacher::count(),
                'classes' => SchoolClass::count(),
                'subjects' => Subject::count(),
                'enrolled' => Enrollment::where('academic_year', Enrollment::currentAcademicYear())
                    ->where('status', 'active')
                    ->count(),
            ],
            'academicYear' => Enrollment::currentAcademicYear(),
            'recentStudents' => Student::with('schoolClass')->latest()->latest('id')->limit(5)->get(),
            'recentAttendance' => Attendance::with(['student', 'schoolClass'])->latest()->latest('id')->limit(6)->get(),
            'classDistribution' => SchoolClass::withCount(['students' => fn ($query) => $query->where('status', 'active')])
                ->orderBy('class_name')
                ->get()
                ->map(fn (SchoolClass $class) => ['label' => $class->class_name, 'value' => $class->students_count])
                ->values(),
            ...$this->attendanceData($user),
        ];
    }

    /**
     * Today's (or the latest recorded day's) attendance summary, plus a per-day
     * breakdown for the chart, limited to what the user is allowed to see.
     *
     * @return array<string, mixed>
     */
    private function attendanceData(User $user): array
    {
        $latestDate = Attendance::visibleTo($user)->max('attendance_date');
        $summaryDate = Attendance::visibleTo($user)->whereDate('attendance_date', today())->exists()
            ? today()
            : ($latestDate ? Carbon::parse($latestDate) : null);

        $daySummary = $summaryDate
            ? Attendance::visibleTo($user)
                ->whereDate('attendance_date', $summaryDate)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
            : collect();

        return [
            'summaryDate' => $summaryDate,
            'daySummary' => $daySummary,
            'attendanceChart' => $this->attendanceChart($user),
        ];
    }

    /**
     * Status counts for each of the last recorded school days.
     *
     * @return array{labels: list<string>, series: array<string, list<int>>}
     */
    private function attendanceChart(User $user): array
    {
        $dates = Attendance::visibleTo($user)
            ->select('attendance_date')
            ->distinct()
            ->orderByDesc('attendance_date')
            ->limit(self::CHART_DAYS)
            ->pluck('attendance_date')
            ->sort()
            ->values();

        /** @var Collection<string, Collection<string, int>> $counts */
        $counts = Attendance::visibleTo($user)
            ->whereIn('attendance_date', $dates)
            ->selectRaw('attendance_date, status, count(*) as total')
            ->groupBy('attendance_date', 'status')
            ->get()
            ->groupBy(fn (Attendance $row) => $row->attendance_date->toDateString())
            ->map(fn (Collection $rows) => $rows->pluck('total', 'status'));

        $series = [];

        foreach (['present', 'excused', 'late', 'absent'] as $status) {
            $series[$status] = $dates
                ->map(fn ($date) => (int) ($counts->get($date->toDateString())?->get($status) ?? 0))
                ->all();
        }

        return [
            'labels' => $dates->map(fn ($date) => $date->format('D d M'))->all(),
            'series' => $series,
        ];
    }
}
