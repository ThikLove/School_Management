@extends('layouts.app')

@section('title', 'Edit attendance')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <x-page-header title="Edit attendance" :subtitle="$attendance->student->name.' · '.$attendance->schoolClass->class_name" />

    <div class="card" style="max-width: 900px;">
        <form method="POST" action="{{ route('attendance.update', $attendance) }}" class="card-body" novalidate>
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Class</label>
                    <input type="text" class="form-control" value="{{ $attendance->schoolClass->class_name }}" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Student</label>
                    <input type="text" class="form-control" value="{{ $attendance->student->name }} ({{ $attendance->student->student_code }})" disabled>
                </div>
                <div class="col-md-6">
                    <x-form.input name="attendance_date" type="date" label="Attendance date" :value="$attendance->attendance_date->format('Y-m-d')" required :max="now()->format('Y-m-d')" />
                </div>
                <div class="col-md-6">
                    <x-form.select name="status" label="Status" :options="['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused']" :value="$attendance->status" required />
                </div>
                <div class="col-12">
                    <x-form.input name="remarks" label="Remarks" :value="$attendance->remarks" maxlength="255" />
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('attendance.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save changes</button>
            </div>
        </form>
    </div>
@endsection
