@extends('layouts.app')

@section('title', 'Enroll student')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('enrollments.index') }}">Enrollments</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('content')
    <x-page-header title="Enroll student" subtitle="Record a student's enrollment in a class for an academic year." />

    <div class="card" style="max-width: 900px;">
        <form method="POST" action="{{ route('enrollments.store') }}" class="card-body" novalidate>
            @csrf
            @include('enrollments._form')
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('enrollments.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save enrollment</button>
            </div>
        </form>
    </div>
@endsection
