@extends('layouts.app')

@section('title', 'Record attendance')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
    <li class="breadcrumb-item active" aria-current="page">Record</li>
@endsection

@section('content')
    <x-page-header title="Record attendance" subtitle="Choose a class first; the student list shows only students enrolled in that class." />

    <div class="card" style="max-width: 900px;">
        @if ($classes->isEmpty())
            <x-empty-state title="No classes available" icon="bi-door-open">
                You can only record attendance for classes you supervise or teach.
            </x-empty-state>
        @else
            <form method="POST" action="{{ route('attendance.store') }}" class="card-body" novalidate>
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <x-form.select name="class_id" label="Class" :options="$classes" :value="$attendance->class_id" placeholder="Select class" required />
                    </div>
                    <div class="col-md-6">
                        <label for="student_id" class="form-label">Student <span class="text-danger">*</span></label>
                        <select name="student_id" id="student_id" required data-selected="{{ old('student_id') }}"
                                @class(['form-select', 'is-invalid' => $errors->has('student_id')])>
                            <option value="">Select a class first</option>
                        </select>
                        @error('student_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="attendance_date" type="date" label="Attendance date" :value="$attendance->attendance_date?->format('Y-m-d')" required :max="now()->format('Y-m-d')" />
                    </div>
                    <div class="col-md-6">
                        <x-form.select name="status" label="Status" :options="['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused']" :value="$attendance->status" required />
                    </div>
                    <div class="col-12">
                        <x-form.input name="remarks" label="Remarks" :value="$attendance->remarks" maxlength="255" placeholder="Optional, e.g. arrived after first period" />
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('attendance.index') }}" class="btn btn-light">Back to list</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save attendance</button>
                </div>
            </form>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        /**
         * Rebuild the student dropdown whenever the class changes, using only the
         * students enrolled in that class (sent by the server as JSON).
         */
        document.addEventListener('DOMContentLoaded', () => {
            const studentsByClass = @json($studentsByClass);
            const classSelect = document.getElementById('class_id');
            const studentSelect = document.getElementById('student_id');

            if (!classSelect || !studentSelect) {
                return;
            }

            const renderStudents = () => {
                const students = studentsByClass[classSelect.value] || [];
                const selected = studentSelect.dataset.selected;

                studentSelect.innerHTML = '';
                studentSelect.add(new Option(classSelect.value ? (students.length ? 'Select student' : 'No students enrolled in this class') : 'Select a class first', ''));

                students.forEach((student) => {
                    studentSelect.add(new Option(student.name, student.id, false, String(student.id) === selected));
                });

                studentSelect.disabled = !classSelect.value || students.length === 0;
            };

            classSelect.addEventListener('change', () => {
                studentSelect.dataset.selected = '';
                renderStudents();
            });

            renderStudents();
        });
    </script>
@endpush
