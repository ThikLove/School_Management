@extends('layouts.app')

@php($user = auth()->user())

@section('title', $user->isStudent() ? 'My attendance' : 'Attendance')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Attendance</li>
@endsection

@section('content')
    <x-page-header :title="$user->isStudent() ? 'My attendance' : 'Attendance'"
                   :subtitle="$user->isStudent() ? 'Your daily attendance records.' : ($user->isTeacher() ? 'Attendance for the classes you supervise or teach.' : 'Daily attendance records for every class.')">
        @can('create', App\Models\Attendance::class)
            <a href="{{ route('attendance.create') }}" class="btn btn-primary"><i class="bi bi-calendar-plus me-1"></i> Record attendance</a>
        @endcan
    </x-page-header>

    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3"><x-stat-card label="Present" :value="$summary['present'] ?? 0" icon="bi-check2-circle" /></div>
        <div class="col-6 col-xl-3"><x-stat-card label="Late" :value="$summary['late'] ?? 0" icon="bi-clock" tone="amber" /></div>
        <div class="col-6 col-xl-3"><x-stat-card label="Absent" :value="$summary['absent'] ?? 0" icon="bi-x-circle" tone="red" /></div>
        <div class="col-6 col-xl-3"><x-stat-card label="Excused" :value="$summary['excused'] ?? 0" icon="bi-file-earmark-medical" tone="blue" /></div>
    </div>

    <div class="card mb-3">
        <form method="GET" action="{{ route('attendance.index') }}" class="card-body row g-2 align-items-end">
            @unless ($user->isStudent())
                <div class="col-lg-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="search" id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Student name or code…">
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <label for="class_id" class="form-label">Class</label>
                    <select id="class_id" name="class_id" class="form-select">
                        <option value="">All classes</option>
                        @foreach ($classes as $id => $name)
                            <option value="{{ $id }}" @selected(($filters['class_id'] ?? '') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless
            <div class="col-6 col-md-3 col-lg-2">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">All</option>
                    @foreach (App\Models\Attendance::STATUSES as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg">
                <label for="date_from" class="form-label">From</label>
                <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
            </div>
            <div class="col-6 col-md-3 col-lg">
                <label for="date_to" class="form-label">To</label>
                <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
            </div>
            <div class="col-md-12 col-lg-2">
                <x-filter-actions :reset="route('attendance.index')" />
            </div>
        </form>
    </div>

    <div class="card">
        @if ($records->isEmpty())
            <x-empty-state :title="array_filter($filters) ? 'No records match your filters' : 'No attendance recorded yet'" icon="bi-calendar-check">
                @if (array_filter($filters)) Try a wider date range or clear the filters. @else Attendance records will appear here once they are recorded. @endif
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            @unless ($user->isStudent())<th>Student</th>@endunless
                            <th>Class</th><th>Status</th><th>Remarks</th>
                            @unless ($user->isStudent())<th class="text-end">Actions</th>@endunless
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $record)
                            <tr>
                                <td class="text-nowrap">{{ $record->attendance_date->format('D, d M Y') }}</td>
                                @unless ($user->isStudent())
                                    <td>
                                        <a href="{{ route('students.show', $record->student) }}" class="fw-semibold text-reset">{{ $record->student->name }}</a>
                                        <div class="small text-muted">{{ $record->student->student_code }}</div>
                                    </td>
                                @endunless
                                <td>{{ $record->schoolClass->class_name }}</td>
                                <td><x-status-badge :status="$record->status" /></td>
                                <td class="text-muted small">{{ $record->remarks ?? '—' }}</td>
                                @unless ($user->isStudent())
                                    <td class="text-end">
                                        <x-row-actions
                                            :edit="$user->can('update', $record) ? route('attendance.edit', $record) : null"
                                            :delete="$user->can('delete', $record) ? route('attendance.destroy', $record) : null"
                                            :delete-message="'Delete the attendance record of '.$record->student->name.' on '.$record->attendance_date->format('d M Y').'?'" />
                                    </td>
                                @endunless
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($records->hasPages())
                <div class="card-footer bg-transparent">{{ $records->links() }}</div>
            @endif
        @endif
    </div>
@endsection
