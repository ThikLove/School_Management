@extends('layouts.app')

@section('title', 'Teachers')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Teachers</li>
@endsection

@section('content')
    <x-page-header title="Teachers" subtitle="Teaching staff, their departments and contact details.">
        @can('create', App\Models\Teacher::class)
            <a href="{{ route('teachers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add teacher</a>
        @endcan
    </x-page-header>

    <div class="card mb-3">
        <form method="GET" action="{{ route('teachers.index') }}" class="card-body row g-2 align-items-end">
            <div class="col-md-5">
                <label for="search" class="form-label">Search</label>
                <input type="search" id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Name or teacher code…">
            </div>
            <div class="col-md-4">
                <label for="department_id" class="form-label">Department</label>
                <select id="department_id" name="department_id" class="form-select">
                    <option value="">All departments</option>
                    @foreach ($departments as $id => $name)
                        <option value="{{ $id }}" @selected(($filters['department_id'] ?? '') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <x-filter-actions :reset="route('teachers.index')" />
            </div>
        </form>
    </div>

    <div class="card">
        @if ($teachers->isEmpty())
            <x-empty-state :title="array_filter($filters) ? 'No teachers match your filters' : 'No teachers yet'" icon="bi-person-badge">
                @if (array_filter($filters)) Try another name or department. @else Add your first teacher to get started. @endif
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>Code</th><th>Teacher</th><th>Department</th><th>Phone</th><th>Hired</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($teachers as $teacher)
                            <tr>
                                <td class="text-muted">{{ $teacher->teacher_code }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar">{{ Str::upper(Str::substr($teacher->name, 0, 1)) }}</span>
                                        <div class="min-w-0">
                                            <a href="{{ route('teachers.show', $teacher) }}" class="fw-semibold text-reset">{{ $teacher->name }}</a>
                                            <div class="small text-muted text-truncate">{{ $teacher->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-soft-info">{{ $teacher->department->name }}</span></td>
                                <td>{{ $teacher->phone ?? '—' }}</td>
                                <td class="text-nowrap">{{ $teacher->hire_date->format('d M Y') }}</td>
                                <td class="text-end">
                                    <x-row-actions
                                        :show="route('teachers.show', $teacher)"
                                        :edit="route('teachers.edit', $teacher)"
                                        :delete="route('teachers.destroy', $teacher)"
                                        :delete-message="'Delete '.$teacher->name.'? Classes they supervise will have no homeroom teacher. Teachers who still teach subjects cannot be deleted.'" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($teachers->hasPages())
                <div class="card-footer bg-transparent">{{ $teachers->links() }}</div>
            @endif
        @endif
    </div>
@endsection
