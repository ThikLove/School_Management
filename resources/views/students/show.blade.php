@extends('layouts.app')

@section('title', $student->name)

@section('breadcrumbs')
    @can('viewAny', App\Models\Student::class)
        <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
    @endcan
    <li class="breadcrumb-item active" aria-current="page">{{ $student->name }}</li>
@endsection

@section('content')
    @php
        $totalDays = $attendanceSummary->sum();
        $attended = ($attendanceSummary['present'] ?? 0) + ($attendanceSummary['late'] ?? 0);
        $rate = $totalDays > 0 ? round($attended / $totalDays * 100) : null;
    @endphp

    <x-page-header :title="$student->name" :subtitle="$student->student_code.' · '.$student->schoolClass->class_name">
        @can('create', App\Models\Enrollment::class)
            <a href="{{ route('enrollments.create', ['student_id' => $student->id]) }}" class="btn btn-light"><i class="bi bi-journal-plus me-1"></i> New enrollment</a>
        @endcan
        @can('update', $student)
            <a href="{{ route('students.edit', $student) }}" class="btn btn-light"><i class="bi bi-pencil me-1"></i> Edit</a>
        @endcan
        @can('delete', $student)
            <form method="POST" action="{{ route('students.destroy', $student) }}" data-confirm="Delete {{ $student->name }}? Students with attendance or enrollment history are kept; set their status to inactive or graduated instead.">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i> Delete</button>
            </form>
        @endcan
    </x-page-header>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar avatar-lg">{{ Str::upper(Str::substr($student->name, 0, 1)) }}</span>
                    <div class="min-w-0">
                        <h5 class="mb-1">{{ $student->name }}</h5>
                        <div class="text-muted small mb-1">{{ $student->student_code }}</div>
                        <x-status-badge :status="$student->status" />
                    </div>
                </div>
                <ul class="list-group list-group-flush">
                    <x-detail-row label="Class">
                        @can('view', $student->schoolClass)
                            <a href="{{ route('classes.show', $student->schoolClass) }}">{{ $student->schoolClass->class_name }}</a>
                        @else
                            {{ $student->schoolClass->class_name }}
                        @endcan
                    </x-detail-row>
                    <x-detail-row label="Homeroom teacher">{{ $student->schoolClass->homeroomTeacher?->name }}</x-detail-row>
                    <x-detail-row label="Gender"><span class="text-capitalize">{{ $student->gender }}</span></x-detail-row>
                    <x-detail-row label="Date of birth">{{ $student->date_of_birth->format('d M Y') }} ({{ $student->date_of_birth->age }} yrs)</x-detail-row>
                    <x-detail-row label="Email">{{ $student->email }}</x-detail-row>
                    <x-detail-row label="Phone">{{ $student->phone }}</x-detail-row>
                    <x-detail-row label="Address">{{ $student->address }}</x-detail-row>
                    <x-detail-row label="Enrollment date">{{ $student->enrollment_date->format('d M Y') }}</x-detail-row>
                    @if (auth()->user()->isAdmin())
                        <x-detail-row label="Login account">{{ $student->user?->email }}</x-detail-row>
                    @endif
                </ul>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-3"><x-stat-card label="Present" :value="$attendanceSummary['present'] ?? 0" icon="bi-check2-circle" /></div>
                <div class="col-6 col-md-3"><x-stat-card label="Late" :value="$attendanceSummary['late'] ?? 0" icon="bi-clock" tone="amber" /></div>
                <div class="col-6 col-md-3"><x-stat-card label="Absent" :value="$attendanceSummary['absent'] ?? 0" icon="bi-x-circle" tone="red" /></div>
                <div class="col-6 col-md-3"><x-stat-card label="Excused" :value="$attendanceSummary['excused'] ?? 0" icon="bi-file-earmark-medical" tone="blue" /></div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold">Attendance rate</span>
                        <span class="fw-bold text-success">{{ $rate !== null ? $rate.'%' : 'No records' }}</span>
                    </div>
                    <div class="progress" role="progressbar" aria-label="Attendance rate" aria-valuenow="{{ $rate ?? 0 }}" aria-valuemin="0" aria-valuemax="100" style="height: .6rem;">
                        <div class="progress-bar bg-success" style="width: {{ $rate ?? 0 }}%"></div>
                    </div>
                    <div class="small text-muted mt-2">Present or late on {{ $attended }} of {{ $totalDays }} recorded school days.</div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Enrollment history</div>
                @if ($student->enrollments->isEmpty())
                    <x-empty-state title="No enrollments recorded" icon="bi-journal" class="py-4" />
                @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead><tr><th>Academic year</th><th>Class</th><th>Enrolled on</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach ($student->enrollments as $enrollment)
                                    <tr>
                                        <td class="fw-semibold">{{ $enrollment->academic_year }}</td>
                                        <td>{{ $enrollment->schoolClass->class_name }}</td>
                                        <td>{{ $enrollment->enrollment_date->format('d M Y') }}</td>
                                        <td><x-status-badge :status="$enrollment->status" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Recent attendance</span>
                    @can('viewAny', App\Models\Attendance::class)
                        @if (! auth()->user()->isStudent())
                            <a href="{{ route('attendance.index', ['search' => $student->student_code]) }}" class="small">View all</a>
                        @else
                            <a href="{{ route('attendance.index') }}" class="small">View all</a>
                        @endif
                    @endcan
                </div>
                @if ($recentAttendance->isEmpty())
                    <x-empty-state title="No attendance recorded yet" icon="bi-calendar" class="py-4" />
                @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead><tr><th>Date</th><th>Class</th><th>Status</th><th>Remarks</th></tr></thead>
                            <tbody>
                                @foreach ($recentAttendance as $record)
                                    <tr>
                                        <td class="text-nowrap">{{ $record->attendance_date->format('D, d M Y') }}</td>
                                        <td>{{ $record->schoolClass->class_name }}</td>
                                        <td><x-status-badge :status="$record->status" /></td>
                                        <td class="text-muted small">{{ $record->remarks ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
