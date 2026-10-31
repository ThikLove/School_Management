@extends('layouts.app')

@section('title', 'Edit subject')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('subjects.index') }}">Subjects</a></li>
    <li class="breadcrumb-item"><a href="{{ route('subjects.show', $subject) }}">{{ $subject->name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <x-page-header :title="'Edit '.$subject->name" />

    <div class="card" style="max-width: 760px;">
        <form method="POST" action="{{ route('subjects.update', $subject) }}" class="card-body" novalidate>
            @csrf
            @method('PUT')
            @include('subjects._form')
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('subjects.show', $subject) }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save changes</button>
            </div>
        </form>
    </div>
@endsection
