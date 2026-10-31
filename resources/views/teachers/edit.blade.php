@extends('layouts.app')

@section('title', 'Edit teacher')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('teachers.index') }}">Teachers</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teachers.show', $teacher) }}">{{ $teacher->name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <x-page-header :title="'Edit '.$teacher->name" />

    <div class="card">
        <form method="POST" action="{{ route('teachers.update', $teacher) }}" class="card-body" novalidate>
            @csrf
            @method('PUT')
            @include('teachers._form')
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('teachers.show', $teacher) }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save changes</button>
            </div>
        </form>
    </div>
@endsection
