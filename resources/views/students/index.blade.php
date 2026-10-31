@extends('layouts.app')

@php($isAdmin = auth()->user()->isAdmin())

@section('title', $isAdmin ? 'Students' : 'My students')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Students</li>
@endsection

@section('content')
    <x-page-header :title="$isAdmin ? 'Students' : 'My students'" :subtitle="$isAdmin ? 'All student records, their classes and status.' : 'Students in the classes you supervise or teach.'">
        @can('create', App\Models\Student::class)
            <a href="{{ route('students.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add student</a>
        @endcan
    </x-page-header>

    <div class="card mb-3">
        <form method="GET" action="{{ route('students.index') }}" class="card-body row g-2 align-items-end">
            <div class="col-md-12 col-lg-3">
                <label for="search" class="form-label">Search</label>
                <input type="search" id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Name or student code…">
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
                <label for="gender" class="form-label">Gender</label>
                <select id="gender" name="gender" class="form-select">
                    <option value="">All</option>
                    @foreach (App\Models\Student::GENDERS as $gender)
                        <option value="{{ $gender }}" @selected(($filters['gender'] ?? '') === $gender)>{{ ucfirst($gender) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">All</option>
                    @foreach (App\Models\Student::STATUSES as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-12 col-lg-3">
                <x-filter-actions :reset="route('students.index')" />
            </div>
        </form>
    </div>

    <div class="card">
        @if ($students->isEmpty())
            <x-empty-state :title="array_filter($filters) ? 'No students match your filters' : 'No students yet'" icon="bi-people">
                @if (array_filter($filters)) Try another search, or clear the filters. @elseif ($isAdmin) Add your first student to get started. @else There are no students in your classes yet. @endif
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>Code</th><th>Student</th><th>Class</th><th>Gender</th><th>Enrolled</th><th>Status</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            <tr>
                                <td class="text-muted">{{ $student->student_code }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar">{{ Str::upper(Str::substr($student->name, 0, 1)) }}</span>
                                        <div class="min-w-0">
                                            <a href="{{ route('students.show', $student) }}" class="fw-semibold text-reset">{{ $student->name }}</a>
                                            <div class="small text-muted text-truncate">{{ $student->email ?? 'No email' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $student->schoolClass->class_name }}</td>
                                <td class="text-capitalize">{{ $student->gender }}</td>
                                <td class="text-nowrap">{{ $student->enrollment_date->format('d M Y') }}</td>
                                <td><x-status-badge :status="$student->status" /></td>
                                <td class="text-end">
                                    <x-row-actions
                                        :show="route('students.show', $student)"
                                        :edit="auth()->user()->can('update', $student) ? route('students.edit', $student) : null"
                                        :delete="auth()->user()->can('delete', $student) ? route('students.destroy', $student) : null"
                                        :delete-message="'Delete '.$student->name.'? Students with attendance or enrollment history are kept; set their status to inactive or graduated instead.'" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($students->hasPages())
                <div class="card-footer bg-transparent">{{ $students->links() }}</div>
            @endif
        @endif
    </div>
@endsection
