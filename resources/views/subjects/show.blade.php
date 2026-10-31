@extends('layouts.app')

@section('title', $subject->name)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('subjects.index') }}">Subjects</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $subject->name }}</li>
@endsection

@section('content')
    <x-page-header :title="$subject->name" :subtitle="$subject->subject_code">
        @can('update', $subject)
            <a href="{{ route('subjects.edit', $subject) }}" class="btn btn-light"><i class="bi bi-pencil me-1"></i> Edit</a>
        @endcan
        @can('delete', $subject)
            <form method="POST" action="{{ route('subjects.destroy', $subject) }}" data-confirm="Delete {{ $subject->name }}? It will also be removed from every class it is assigned to.">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i> Delete</button>
            </form>
        @endcan
    </x-page-header>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">About this subject</div>
                <ul class="list-group list-group-flush">
                    <x-detail-row label="Code">{{ $subject->subject_code }}</x-detail-row>
                    <x-detail-row label="Name">{{ $subject->name }}</x-detail-row>
                </ul>
                <div class="card-body text-muted">{{ $subject->description ?? 'No description.' }}</div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Taught in</span>
                    <span class="badge badge-soft-success">{{ $assignments->count() }}</span>
                </div>
                @if ($assignments->isEmpty())
                    <x-empty-state title="Not assigned to any class" icon="bi-door-open" class="py-4">
                        Assign this subject from a class's detail page.
                    </x-empty-state>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>Class</th><th>Grade</th><th>Teacher</th></tr></thead>
                            <tbody>
                                @foreach ($assignments as $assignment)
                                    <tr>
                                        <td class="fw-semibold">
                                            @can('view', $assignment->schoolClass)
                                                <a href="{{ route('classes.show', $assignment->schoolClass) }}" class="text-reset">{{ $assignment->schoolClass->class_name }}</a>
                                            @else
                                                {{ $assignment->schoolClass->class_name }}
                                            @endcan
                                        </td>
                                        <td>{{ $assignment->schoolClass->grade_level }}</td>
                                        <td>{{ $assignment->teacher->name }}</td>
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
