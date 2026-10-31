@extends('layouts.app')

@section('title', $schoolClass->class_name)

@section('breadcrumbs')
    @can('viewAny', App\Models\SchoolClass::class)
        <li class="breadcrumb-item"><a href="{{ route('classes.index') }}">Classes</a></li>
    @endcan
    <li class="breadcrumb-item active" aria-current="page">{{ $schoolClass->class_name }}</li>
@endsection

@section('content')
    <x-page-header :title="$schoolClass->class_name" :subtitle="'Grade '.$schoolClass->grade_level.($schoolClass->room_number ? ' · Room '.$schoolClass->room_number : '')">
        @can('create', [App\Models\Attendance::class, $schoolClass])
            @unless (auth()->user()->isAdmin())
                <a href="{{ route('attendance.create', ['class_id' => $schoolClass->id]) }}" class="btn btn-primary"><i class="bi bi-calendar-plus me-1"></i> Record attendance</a>
            @endunless
        @endcan
        @can('update', $schoolClass)
            <a href="{{ route('classes.edit', $schoolClass) }}" class="btn btn-light"><i class="bi bi-pencil me-1"></i> Edit</a>
        @endcan
        @can('delete', $schoolClass)
            <form method="POST" action="{{ route('classes.destroy', $schoolClass) }}" data-confirm="Delete {{ $schoolClass->class_name }}? Classes with students, enrollments or attendance cannot be deleted.">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i> Delete</button>
            </form>
        @endcan
    </x-page-header>

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3"><x-stat-card label="Students" :value="$schoolClass->students->count()" icon="bi-people" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat-card label="Active students" :value="$schoolClass->students->where('status', 'active')->count()" icon="bi-person-check" tone="blue" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat-card label="Subjects" :value="$assignments->count()" icon="bi-book" tone="amber" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat-card label="Homeroom teacher" :value="$schoolClass->homeroomTeacher?->name ?? '—'" icon="bi-person-badge" tone="gray" /></div>
    </div>

    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header">Students</div>
                @if ($schoolClass->students->isEmpty())
                    <x-empty-state title="No students in this class" icon="bi-people" />
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>Code</th><th>Name</th><th>Gender</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach ($schoolClass->students as $student)
                                    <tr>
                                        <td class="text-muted">{{ $student->student_code }}</td>
                                        <td class="fw-semibold">
                                            @can('view', $student)
                                                <a href="{{ route('students.show', $student) }}" class="text-reset">{{ $student->name }}</a>
                                            @else
                                                {{ $student->name }}
                                            @endcan
                                        </td>
                                        <td class="text-capitalize">{{ $student->gender }}</td>
                                        <td><x-status-badge :status="$student->status" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header">Subjects &amp; teachers</div>
                @if ($assignments->isEmpty())
                    <x-empty-state title="No subjects assigned yet" icon="bi-book" class="py-4" />
                @else
                    <ul class="list-group list-group-flush">
                        @foreach ($assignments as $assignment)
                            <li class="list-group-item d-flex align-items-center justify-content-between gap-2">
                                <div class="min-w-0">
                                    <div class="fw-semibold">{{ $assignment->subject->name }}</div>
                                    <div class="small text-muted">{{ $assignment->subject->subject_code }} · {{ $assignment->teacher->name }}</div>
                                </div>
                                @can('update', $schoolClass)
                                    <form method="POST" action="{{ route('classes.subjects.destroy', [$schoolClass, $assignment]) }}" data-confirm="Remove {{ $assignment->subject->name }} ({{ $assignment->teacher->name }}) from {{ $schoolClass->class_name }}?" data-confirm-button="Remove">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-light btn-icon btn-sm text-danger" aria-label="Remove assignment"><i class="bi bi-x-lg"></i></button>
                                    </form>
                                @endcan
                            </li>
                        @endforeach
                    </ul>
                @endif

                @can('update', $schoolClass)
                    <div class="card-body border-top">
                        <div class="fw-semibold mb-2">Assign a subject</div>
                        <form method="POST" action="{{ route('classes.subjects.store', $schoolClass) }}" class="row g-2" novalidate>
                            @csrf
                            <div class="col-sm-6">
                                <label for="subject_id" class="form-label small">Subject</label>
                                <select id="subject_id" name="subject_id" class="form-select @error('subject_id', 'assignment') is-invalid @enderror" required>
                                    <option value="">Select subject</option>
                                    @foreach ($subjects as $id => $name)
                                        <option value="{{ $id }}" @selected(old('subject_id') == $id)>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('subject_id', 'assignment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label for="teacher_id" class="form-label small">Teacher</label>
                                <select id="teacher_id" name="teacher_id" class="form-select @error('teacher_id', 'assignment') is-invalid @enderror" required>
                                    <option value="">Select teacher</option>
                                    @foreach ($teachers as $id => $name)
                                        <option value="{{ $id }}" @selected(old('teacher_id') == $id)>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('teacher_id', 'assignment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg me-1"></i> Assign subject</button>
                            </div>
                        </form>
                    </div>
                @endcan
            </div>
        </div>
    </div>
@endsection
