@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <x-page-header :title="'Hello, '.auth()->user()->name.' 👋'" :subtitle="now()->format('l, j F Y')">
        @isset($teacher)
            <a href="{{ route('attendance.create') }}" class="btn btn-primary"><i class="bi bi-calendar-plus me-1"></i> Record attendance</a>
        @endisset
        @isset($stats)
            <a href="{{ route('students.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Add student</a>
        @endisset
    </x-page-header>

    {{-- ===================== Admin ===================== --}}
    @isset($stats)
        <div class="row g-3 mb-3">
            <div class="col-6 col-lg"><x-stat-card label="Total students" :value="$stats['students']" icon="bi-people" /></div>
            <div class="col-6 col-lg"><x-stat-card label="Total teachers" :value="$stats['teachers']" icon="bi-person-badge" tone="blue" /></div>
            <div class="col-6 col-lg"><x-stat-card label="Total classes" :value="$stats['classes']" icon="bi-door-open" tone="amber" /></div>
            <div class="col-6 col-lg"><x-stat-card label="Total subjects" :value="$stats['subjects']" icon="bi-book" tone="gray" /></div>
            <div class="col-12 col-lg"><x-stat-card label="Enrolled now" :value="$stats['enrolled']" icon="bi-journal-check" :hint="$academicYear" /></div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-8">@include('partials.attendance-chart')</div>
            <div class="col-xl-4">@include('partials.attendance-summary')</div>
        </div>

        <div class="row g-3">
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header">Active students by class</div>
                    <div class="card-body">
                        @if ($classDistribution->isEmpty())
                            <x-empty-state title="No classes yet" icon="bi-door-open" class="py-4" />
                        @else
                            <div style="position: relative; height: {{ max(160, $classDistribution->count() * 44) }}px;">
                                <canvas id="classChart" role="img" aria-label="Bar chart of active students per class"></canvas>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Recent student registrations</span>
                        <a href="{{ route('students.index') }}" class="small fw-normal">View all</a>
                    </div>
                    @if ($recentStudents->isEmpty())
                        <x-empty-state title="No students yet" icon="bi-people" class="py-4" />
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($recentStudents as $recentStudent)
                                <li class="list-group-item d-flex align-items-center gap-2">
                                    <span class="avatar">{{ Str::upper(Str::substr($recentStudent->name, 0, 1)) }}</span>
                                    <div class="min-w-0 grow">
                                        <a href="{{ route('students.show', $recentStudent) }}" class="fw-semibold text-reset d-block text-truncate">{{ $recentStudent->name }}</a>
                                        <div class="small text-muted">{{ $recentStudent->student_code }} · {{ $recentStudent->schoolClass->class_name }}</div>
                                    </div>
                                    <span class="small text-muted text-nowrap">{{ $recentStudent->created_at->diffForHumans() }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Recently recorded attendance</span>
                        <a href="{{ route('attendance.index') }}" class="small fw-normal">View all</a>
                    </div>
                    @if ($recentAttendance->isEmpty())
                        <x-empty-state title="No attendance yet" icon="bi-calendar" class="py-4" />
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($recentAttendance as $record)
                                <li class="list-group-item d-flex align-items-center justify-content-between gap-2">
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-truncate">{{ $record->student->name }}</div>
                                        <div class="small text-muted">{{ $record->schoolClass->class_name }} · {{ $record->attendance_date->format('d M') }}</div>
                                    </div>
                                    <x-status-badge :status="$record->status" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        @if ($classDistribution->isNotEmpty())
            @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const rows = @json($classDistribution);

                        new Chart(document.getElementById('classChart'), {
                            type: 'bar',
                            data: {
                                labels: rows.map((row) => row.label),
                                datasets: [{
                                    label: 'Active students',
                                    data: rows.map((row) => row.value),
                                    backgroundColor: '#16A34A',
                                    hoverBackgroundColor: '#166534',
                                    borderRadius: 4,
                                    borderSkipped: 'start',
                                    maxBarThickness: 22,
                                }],
                            },
                            options: window.schoolChartDefaults({
                                indexAxis: 'y',
                                scales: {
                                    x: { beginAtZero: true, ticks: { precision: 0 } },
                                    y: { grid: { display: false } },
                                },
                                plugins: { legend: { display: false } },
                            }),
                        });
                    });
                </script>
            @endpush
        @endif
    @endisset

    {{-- ===================== Teacher ===================== --}}
    @isset($teacher)
        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-xl-3"><x-stat-card label="My classes" :value="$classes->count()" icon="bi-door-open" /></div>
            <div class="col-sm-6 col-xl-3"><x-stat-card label="My students" :value="$classes->sum('students_count')" icon="bi-people" tone="blue" /></div>
            <div class="col-sm-6 col-xl-3"><x-stat-card label="Homeroom classes" :value="$classes->where('homeroom_teacher_id', $teacher->id)->count()" icon="bi-house-door" tone="amber" /></div>
            <div class="col-sm-6 col-xl-3"><x-stat-card label="Subjects taught" :value="$teacher->subjects()->distinct()->count('subjects.id')" icon="bi-book" tone="gray" /></div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-8">@include('partials.attendance-chart')</div>
            <div class="col-xl-4">@include('partials.attendance-summary')</div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>My classes</span>
                <span class="badge badge-soft-success">{{ $classes->count() }}</span>
            </div>
            @if ($classes->isEmpty())
                <x-empty-state title="No classes yet" icon="bi-door-open">
                    You are not assigned to any class. An administrator can assign you as a homeroom or subject teacher.
                </x-empty-state>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr><th>Class</th><th>Grade</th><th>Room</th><th>Role</th><th class="text-center">Students</th><th class="text-end">Actions</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($classes as $class)
                                <tr>
                                    <td class="fw-semibold"><a href="{{ route('classes.show', $class) }}" class="text-reset">{{ $class->class_name }}</a></td>
                                    <td>{{ $class->grade_level }}</td>
                                    <td>{{ $class->room_number ?? '—' }}</td>
                                    <td>
                                        @if ($class->homeroom_teacher_id === $teacher->id)
                                            <span class="badge badge-soft-success">Homeroom</span>
                                        @else
                                            <span class="badge badge-soft-info">Subject teacher</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $class->students_count }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('attendance.create', ['class_id' => $class->id]) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-calendar-plus me-1"></i> Attendance</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endisset

    {{-- ===================== Student ===================== --}}
    @isset($student)
        @php
            $totalDays = $attendance->sum();
            $attended = ($attendance['present'] ?? 0) + ($attendance['late'] ?? 0);
            $rate = $totalDays > 0 ? round($attended / $totalDays * 100) : 0;
        @endphp
        <div class="row g-3">
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="avatar avatar-lg">{{ Str::upper(Str::substr($student->name, 0, 1)) }}</span>
                        <div>
                            <h5 class="mb-1">{{ $student->name }}</h5>
                            <div class="text-muted small mb-2">{{ $student->student_code }}</div>
                            <x-status-badge :status="$student->status" />
                        </div>
                    </div>
                    <ul class="list-group list-group-flush">
                        <x-detail-row label="Class">{{ $student->schoolClass->class_name }}</x-detail-row>
                        <x-detail-row label="Homeroom teacher">{{ $student->schoolClass->homeroomTeacher?->name }}</x-detail-row>
                        <x-detail-row label="Room">{{ $student->schoolClass->room_number }}</x-detail-row>
                    </ul>
                    <div class="card-body d-flex gap-2">
                        <a href="{{ route('students.show', $student) }}" class="btn btn-light btn-sm grow">My profile</a>
                        <a href="{{ route('subjects.index') }}" class="btn btn-light btn-sm grow">My subjects</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="row g-3">
                    <div class="col-6"><x-stat-card label="Present" :value="$attendance['present'] ?? 0" icon="bi-check2-circle" /></div>
                    <div class="col-6"><x-stat-card label="Late" :value="$attendance['late'] ?? 0" icon="bi-clock" tone="amber" /></div>
                    <div class="col-6"><x-stat-card label="Absent" :value="$attendance['absent'] ?? 0" icon="bi-x-circle" tone="red" /></div>
                    <div class="col-6"><x-stat-card label="Excused" :value="$attendance['excused'] ?? 0" icon="bi-file-earmark-medical" tone="blue" /></div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-semibold">Attendance rate</span>
                                    <span class="fw-bold text-success">{{ $rate }}%</span>
                                </div>
                                <div class="progress" role="progressbar" aria-label="Attendance rate" aria-valuenow="{{ $rate }}" aria-valuemin="0" aria-valuemax="100" style="height: .6rem;">
                                    <div class="progress-bar bg-success" style="width: {{ $rate }}%"></div>
                                </div>
                                <div class="small text-muted mt-2">Present or late on {{ $attended }} of {{ $totalDays }} recorded school days. <a href="{{ route('attendance.index') }}">See all records</a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endisset

    @isset($unlinked)
        <div class="card">
            <x-empty-state title="Your account isn't linked yet" icon="bi-link-45deg">
                Your login works, but it is not connected to a {{ auth()->user()->role }} record yet.
                Please contact the school office so an administrator can link your account.
            </x-empty-state>
        </div>
    @endisset
@endsection
