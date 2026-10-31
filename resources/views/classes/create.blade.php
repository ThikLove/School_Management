@extends('layouts.app')

@section('title', 'Add class')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('classes.index') }}">Classes</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('content')
    <x-page-header title="Add class" subtitle="Create a new class group." />

    <div class="card" style="max-width: 860px;">
        <form method="POST" action="{{ route('classes.store') }}" class="card-body" novalidate>
            @csrf
            @include('classes._form')
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('classes.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save class</button>
            </div>
        </form>
    </div>
@endsection
