@extends('layouts.app')

@section('title', $teacher->name)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('teachers.index') }}">Teachers</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $teacher->name }}</li>
@endsection

@section('content')
    <x-page-header :title="$teacher->name" :subtitle="$teacher->teacher_code.' · '.$teacher->department->name">
        @can('update', $teacher)
            <a href="{{ route('teachers.edit', $teacher) }}" class="btn btn-light"><i class="bi bi-pencil me-1"></i> Edit</a>
        @endcan
        @can('delete', $teacher)
            <form method="POST" action="{{ route('teachers.destroy', $teacher) }}" data-confirm="Delete {{ $teacher->name }}? Classes they supervise will have no homeroom teacher. Teachers who still teach subjects cannot be deleted.">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i> Delete</button>
            </form>
        @endcan
    </x-page-header>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar avatar-lg">{{ Str::upper(Str::substr($teacher->name, 0, 1)) }}</span>
                    <div class="min-w-0">
                        <h5 class="mb-1">{{ $teacher->name }}</h5>
                        <span class="badge badge-soft-info">{{ $teacher->department->name }}</span>
                    </div>
                </div>
                <ul class="list-group list-group-flush">
                    <x-detail-row label="Teacher code">{{ $teacher->teacher_code }}</x-detail-row>
                    <x-detail-row label="Gender"><span class="text-capitalize">{{ $teacher->gender }}</span></x-detail-row>
                    <x-detail-row label="Email">{{ $teacher->email }}</x-detail-row>
                    <x-detail-row label="Phone">{{ $teacher->phone }}</x-detail-row>
                    <x-detail-row label="Address">{{ $teacher->address }}</x-detail-row>
                    <x-detail-row label="Hire date">{{ $teacher->hire_date->format('d M Y') }}</x-detail-row>
                    <x-detail-row label="Login account">{{ $teacher->user?->email }}</x-detail-row>
                </ul>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">Homeroom classes</div>
                @if ($teacher->homeroomClasses->isEmpty())
                    <x-empty-state title="Not a homeroom teacher" icon="bi-door-open" class="py-4" />
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>Class</th><th>Grade</th><th>Room</th><th class="text-end">Students</th></tr></thead>
                            <tbody>
                                @foreach ($teacher->homeroomClasses as $class)
                                    <tr>
                                        <td class="fw-semibold"><a href="{{ route('classes.show', $class) }}" class="text-reset">{{ $class->class_name }}</a></td>
                                        <td>{{ $class->grade_level }}</td>
                                        <td>{{ $class->room_number ?? '—' }}</td>
                                        <td class="text-end">{{ $class->students_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Teaching assignments</span>
                    <span class="badge badge-soft-success">{{ $assignments->count() }}</span>
                </div>
                @if ($assignments->isEmpty())
                    <x-empty-state title="No subjects assigned" icon="bi-book" class="py-4">
                        Assign subjects from a class's detail page.
                    </x-empty-state>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>Class</th><th>Subject</th><th>Code</th></tr></thead>
                            <tbody>
                                @foreach ($assignments as $assignment)
                                    <tr>
                                        <td class="fw-semibold"><a href="{{ route('classes.show', $assignment->schoolClass) }}" class="text-reset">{{ $assignment->schoolClass->class_name }}</a></td>
                                        <td>{{ $assignment->subject->name }}</td>
                                        <td class="text-muted">{{ $assignment->subject->subject_code }}</td>
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
