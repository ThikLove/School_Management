@extends('layouts.app')

@section('title', $department->name)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departments</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $department->name }}</li>
@endsection

@section('content')
    <x-page-header :title="$department->name" :subtitle="$department->description">
        @can('update', $department)
            <a href="{{ route('departments.edit', $department) }}" class="btn btn-light"><i class="bi bi-pencil me-1"></i> Edit</a>
        @endcan
        @can('delete', $department)
            <form method="POST" action="{{ route('departments.destroy', $department) }}" data-confirm="Delete the department “{{ $department->name }}”? This only works when no teachers belong to it.">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i> Delete</button>
            </form>
        @endcan
    </x-page-header>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Teachers in this department</span>
            <span class="badge badge-soft-success">{{ $department->teachers->count() }}</span>
        </div>
        @if ($department->teachers->isEmpty())
            <x-empty-state title="No teachers yet" icon="bi-person-badge">
                Teachers are assigned to a department on the teacher form.
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Code</th><th>Name</th><th>Email</th><th>Phone</th><th>Hired</th></tr></thead>
                    <tbody>
                        @foreach ($department->teachers as $teacher)
                            <tr>
                                <td class="text-muted">{{ $teacher->teacher_code }}</td>
                                <td class="fw-semibold"><a href="{{ route('teachers.show', $teacher) }}" class="text-reset">{{ $teacher->name }}</a></td>
                                <td>{{ $teacher->email }}</td>
                                <td>{{ $teacher->phone ?? '—' }}</td>
                                <td>{{ $teacher->hire_date->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
