@extends('layouts.app')

@section('title', 'Add teacher')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('teachers.index') }}">Teachers</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('content')
    <x-page-header title="Add teacher" subtitle="Register a new member of the teaching staff." />

    <div class="card">
        <form method="POST" action="{{ route('teachers.store') }}" class="card-body" novalidate>
            @csrf
            @include('teachers._form')
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('teachers.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save teacher</button>
            </div>
        </form>
    </div>
@endsection
