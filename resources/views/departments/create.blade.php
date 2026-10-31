@extends('layouts.app')

@section('title', 'Add department')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departments</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('content')
    <x-page-header title="Add department" subtitle="Create a new academic department." />

    <div class="card" style="max-width: 760px;">
        <form method="POST" action="{{ route('departments.store') }}" class="card-body" novalidate>
            @csrf
            @include('departments._form')
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('departments.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save department</button>
            </div>
        </form>
    </div>
@endsection
