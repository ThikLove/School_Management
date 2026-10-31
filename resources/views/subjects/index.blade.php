@extends('layouts.app')

@php($isAdmin = auth()->user()->isAdmin())

@section('title', $isAdmin ? 'Subjects' : 'My subjects')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Subjects</li>
@endsection

@section('content')
    <x-page-header :title="$isAdmin ? 'Subjects' : 'My subjects'"
                   :subtitle="$isAdmin ? 'Subjects offered by the school.' : (auth()->user()->isTeacher() ? 'Subjects you teach and the classes you teach them in.' : 'Subjects taught in your class and who teaches them.')">
        @can('create', App\Models\Subject::class)
            <a href="{{ route('subjects.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add subject</a>
        @endcan
    </x-page-header>

    <div class="card mb-3">
        <form method="GET" action="{{ route('subjects.index') }}" class="card-body row g-2 align-items-end">
            <div class="col-md-8 col-lg-9">
                <label for="search" class="form-label">Search</label>
                <input type="search" id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Subject name or code…">
            </div>
            <div class="col-md-4 col-lg-3">
                <x-filter-actions :reset="route('subjects.index')" />
            </div>
        </form>
    </div>

    <div class="card">
        @if ($subjects->isEmpty())
            <x-empty-state :title="array_filter($filters) ? 'No subjects match your search' : 'No subjects yet'" icon="bi-book">
                @if (array_filter($filters)) Try another name or code. @elseif ($isAdmin) Add your first subject to get started. @else No subjects are assigned to you yet. @endif
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Code</th><th>Subject</th>
                            @if ($isAdmin)
                                <th>Description</th><th class="text-center">Classes</th>
                            @else
                                <th>{{ auth()->user()->isTeacher() ? 'Your classes' : 'Teacher' }}</th>
                            @endif
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subjects as $subject)
                            <tr>
                                <td class="text-muted">{{ $subject->subject_code }}</td>
                                <td class="fw-semibold"><a href="{{ route('subjects.show', $subject) }}" class="text-reset">{{ $subject->name }}</a></td>
                                @if ($isAdmin)
                                    <td class="text-muted">{{ Str::limit($subject->description, 60) ?: '—' }}</td>
                                    <td class="text-center"><span class="badge badge-soft-success">{{ $subject->assignments_count }}</span></td>
                                @else
                                    <td>
                                        @foreach ($assignments->get($subject->id, collect()) as $assignment)
                                            <span class="badge badge-soft-info me-1">{{ auth()->user()->isTeacher() ? $assignment->schoolClass->class_name : $assignment->teacher->name }}</span>
                                        @endforeach
                                    </td>
                                @endif
                                <td class="text-end">
                                    <x-row-actions
                                        :show="route('subjects.show', $subject)"
                                        :edit="auth()->user()->can('update', $subject) ? route('subjects.edit', $subject) : null"
                                        :delete="auth()->user()->can('delete', $subject) ? route('subjects.destroy', $subject) : null"
                                        :delete-message="'Delete '.$subject->name.'? It will also be removed from every class it is assigned to.'" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($subjects->hasPages())
                <div class="card-footer bg-transparent">{{ $subjects->links() }}</div>
            @endif
        @endif
    </div>
@endsection
