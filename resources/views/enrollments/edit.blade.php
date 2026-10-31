@extends('layouts.app')

@section('title', 'Edit enrollment')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('enrollments.index') }}">Enrollments</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <x-page-header title="Edit enrollment" :subtitle="$enrollment->student->name.' · '.$enrollment->academic_year" />

    <div class="card" style="max-width: 900px;">
        <form method="POST" action="{{ route('enrollments.update', $enrollment) }}" class="card-body" novalidate>
            @csrf
            @method('PUT')
            @include('enrollments._form')
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('enrollments.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save changes</button>
            </div>
        </form>
    </div>
@endsection
