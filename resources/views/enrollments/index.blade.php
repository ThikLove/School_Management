@extends('layouts.app')

@section('title', 'Enrollments')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Enrollments</li>
@endsection

@section('content')
    <x-page-header title="Enrollments" subtitle="Which class each student attends in each academic year.">
        <a href="{{ route('enrollments.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Enroll student</a>
    </x-page-header>

    <div class="card mb-3">
        <form method="GET" action="{{ route('enrollments.index') }}" class="card-body row g-2 align-items-end">
            <div class="col-lg-3">
                <label for="search" class="form-label">Search</label>
                <input type="search" id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Student name or code…">
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <label for="class_id" class="form-label">Class</label>
                <select id="class_id" name="class_id" class="form-select">
                    <option value="">All classes</option>
                    @foreach ($classes as $id => $name)
                        <option value="{{ $id }}" @selected(($filters['class_id'] ?? '') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <label for="academic_year" class="form-label">Academic year</label>
                <select id="academic_year" name="academic_year" class="form-select">
                    <option value="">All years</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year }}" @selected(($filters['academic_year'] ?? '') === $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">All</option>
                    @foreach (App\Models\Enrollment::STATUSES as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-12 col-lg-3">
                <x-filter-actions :reset="route('enrollments.index')" />
            </div>
        </form>
    </div>

    <div class="card">
        @if ($enrollments->isEmpty())
            <x-empty-state :title="array_filter($filters) ? 'No enrollments match your filters' : 'No enrollments yet'" icon="bi-journal-check">
                @if (array_filter($filters)) Try other filters, or clear them. @else Enroll a student in a class to get started. @endif
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>Student</th><th>Class</th><th>Academic year</th><th>Enrolled on</th><th>Status</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($enrollments as $enrollment)
                            <tr>
                                <td>
                                    <a href="{{ route('students.show', $enrollment->student) }}" class="fw-semibold text-reset">{{ $enrollment->student->name }}</a>
                                    <div class="small text-muted">{{ $enrollment->student->student_code }}</div>
                                </td>
                                <td>{{ $enrollment->schoolClass->class_name }}</td>
                                <td>{{ $enrollment->academic_year }}</td>
                                <td class="text-nowrap">{{ $enrollment->enrollment_date->format('d M Y') }}</td>
                                <td><x-status-badge :status="$enrollment->status" /></td>
                                <td class="text-end">
                                    <x-row-actions
                                        :edit="route('enrollments.edit', $enrollment)"
                                        :delete="$enrollment->status !== 'completed' ? route('enrollments.destroy', $enrollment) : null"
                                        :delete-message="'Delete this enrollment of '.$enrollment->student->name.' in '.$enrollment->schoolClass->class_name.' ('.$enrollment->academic_year.')? Use this only to correct a mistake.'" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($enrollments->hasPages())
                <div class="card-footer bg-transparent">{{ $enrollments->links() }}</div>
            @endif
        @endif
    </div>
@endsection
