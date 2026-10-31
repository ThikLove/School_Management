@extends('layouts.app')

@section('title', 'Departments')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Departments</li>
@endsection

@section('content')
    <x-page-header title="Departments" subtitle="Academic departments and their teaching staff.">
        @can('create', App\Models\Department::class)
            <a href="{{ route('departments.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add department</a>
        @endcan
    </x-page-header>

    <div class="card mb-3">
        <form method="GET" action="{{ route('departments.index') }}" class="card-body row g-2 align-items-end">
            <div class="col-md-8 col-lg-9">
                <label for="search" class="form-label">Search</label>
                <input type="search" id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Department name…">
            </div>
            <div class="col-md-4 col-lg-3">
                <x-filter-actions :reset="route('departments.index')" />
            </div>
        </form>
    </div>

    <div class="card">
        @if ($departments->isEmpty())
            <x-empty-state :title="array_filter($filters) ? 'No departments match your search' : 'No departments yet'" icon="bi-building">
                @if (array_filter($filters)) Try a different name, or clear the search. @else Add your first department to get started. @endif
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>Department</th><th>Description</th><th class="text-center">Teachers</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($departments as $department)
                            <tr>
                                <td class="fw-semibold"><a href="{{ route('departments.show', $department) }}" class="text-reset">{{ $department->name }}</a></td>
                                <td class="text-muted">{{ Str::limit($department->description, 70) ?: '—' }}</td>
                                <td class="text-center"><span class="badge badge-soft-success">{{ $department->teachers_count }}</span></td>
                                <td class="text-end">
                                    <x-row-actions
                                        :show="route('departments.show', $department)"
                                        :edit="route('departments.edit', $department)"
                                        :delete="route('departments.destroy', $department)"
                                        :delete-message="'Delete the department “'.$department->name.'”? This only works when no teachers belong to it.'" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($departments->hasPages())
                <div class="card-footer bg-transparent">{{ $departments->links() }}</div>
            @endif
        @endif
    </div>
@endsection
