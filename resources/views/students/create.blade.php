@extends('layouts.app')

@section('title', 'Add student')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('content')
    <x-page-header title="Add student" subtitle="Register a new student and enroll them in a class." />

    <div class="card">
        <form method="POST" action="{{ route('students.store') }}" class="card-body" novalidate>
            @csrf
            @include('students._form')
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('students.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save student</button>
            </div>
        </form>
    </div>
@endsection
