@extends('layouts.app')

@section('title', 'Edit department')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departments</a></li>
    <li class="breadcrumb-item"><a href="{{ route('departments.show', $department) }}">{{ $department->name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <x-page-header :title="'Edit '.$department->name" />

    <div class="card" style="max-width: 760px;">
        <form method="POST" action="{{ route('departments.update', $department) }}" class="card-body" novalidate>
            @csrf
            @method('PUT')
            @include('departments._form')
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('departments.show', $department) }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save changes</button>
            </div>
        </form>
    </div>
@endsection
