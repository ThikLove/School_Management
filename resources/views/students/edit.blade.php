@extends('layouts.app')

@section('title', 'Edit student')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
    <li class="breadcrumb-item"><a href="{{ route('students.show', $student) }}">{{ $student->name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <x-page-header :title="'Edit '.$student->name" :subtitle="$student->student_code" />

    <div class="card">
        <form method="POST" action="{{ route('students.update', $student) }}" class="card-body" novalidate>
            @csrf
            @method('PUT')
            @include('students._form')
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('students.show', $student) }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save changes</button>
            </div>
        </form>
    </div>
@endsection
