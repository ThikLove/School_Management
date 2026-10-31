@extends('layouts.app')

@section('title', 'Add subject')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('subjects.index') }}">Subjects</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('content')
    <x-page-header title="Add subject" subtitle="Subjects are assigned to classes from each class's page." />

    <div class="card" style="max-width: 760px;">
        <form method="POST" action="{{ route('subjects.store') }}" class="card-body" novalidate>
            @csrf
            @include('subjects._form')
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('subjects.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save subject</button>
            </div>
        </form>
    </div>
@endsection
