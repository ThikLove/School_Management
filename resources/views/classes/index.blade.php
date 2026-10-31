@extends('layouts.app')

@php($isAdmin = auth()->user()->isAdmin())

@section('title', $isAdmin ? 'Classes' : 'My classes')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Classes</li>
@endsection

@section('content')
    <x-page-header :title="$isAdmin ? 'Classes' : 'My classes'" :subtitle="$isAdmin ? 'Class groups, rooms and homeroom teachers.' : 'Classes you supervise or teach a subject in.'">
        @can('create', App\Models\SchoolClass::class)
            <a href="{{ route('classes.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add class</a>
        @endcan
    </x-page-header>

    <div class="card mb-3">
        <form method="GET" action="{{ route('classes.index') }}" class="card-body row g-2 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="search" id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Class name…">
            </div>
            <div class="col-6 col-md-2">
                <label for="grade_level" class="form-label">Grade</label>
                <select id="grade_level" name="grade_level" class="form-select">
                    <option value="">All</option>
                    @foreach ($gradeLevels as $grade)
                        <option value="{{ $grade }}" @selected(($filters['grade_level'] ?? '') == $grade)>Grade {{ $grade }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label for="homeroom_teacher_id" class="form-label">Homeroom teacher</label>
                <select id="homeroom_teacher_id" name="homeroom_teacher_id" class="form-select">
                    <option value="">Any teacher</option>
                    @foreach ($teachers as $id => $name)
                        <option value="{{ $id }}" @selected(($filters['homeroom_teacher_id'] ?? '') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <x-filter-actions :reset="route('classes.index')" />
            </div>
        </form>
    </div>

    <div class="card">
        @if ($classes->isEmpty())
            <x-empty-state :title="array_filter($filters) ? 'No classes match your filters' : 'No classes yet'" icon="bi-door-open">
                @if (array_filter($filters)) Try other filters, or clear them. @elseif ($isAdmin) Add your first class to get started. @else You are not assigned to any class yet. @endif
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>Class</th><th>Grade</th><th>Room</th><th>Homeroom teacher</th><th class="text-center">Students</th><th class="text-center">Subjects</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($classes as $class)
                            <tr>
                                <td class="fw-semibold"><a href="{{ route('classes.show', $class) }}" class="text-reset">{{ $class->class_name }}</a></td>
                                <td>Grade {{ $class->grade_level }}</td>
                                <td>{{ $class->room_number ?? '—' }}</td>
                                <td>{{ $class->homeroomTeacher?->name ?? '—' }}</td>
                                <td class="text-center"><span class="badge badge-soft-success">{{ $class->students_count }}</span></td>
                                <td class="text-center"><span class="badge badge-soft-secondary">{{ $class->assignments_count }}</span></td>
                                <td class="text-end">
                                    <x-row-actions
                                        :show="route('classes.show', $class)"
                                        :edit="auth()->user()->can('update', $class) ? route('classes.edit', $class) : null"
                                        :delete="auth()->user()->can('delete', $class) ? route('classes.destroy', $class) : null"
                                        :delete-message="'Delete '.$class->class_name.'? Classes with students, enrollments or attendance cannot be deleted.'" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($classes->hasPages())
                <div class="card-footer bg-transparent">{{ $classes->links() }}</div>
            @endif
        @endif
    </div>
@endsection
