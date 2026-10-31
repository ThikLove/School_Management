@extends('layouts.app')

@section('title', 'Edit class')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('classes.index') }}">Classes</a></li>
    <li class="breadcrumb-item"><a href="{{ route('classes.show', $schoolClass) }}">{{ $schoolClass->class_name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <x-page-header :title="'Edit '.$schoolClass->class_name" />

    <div class="card" style="max-width: 860px;">
        <form method="POST" action="{{ route('classes.update', $schoolClass) }}" class="card-body" novalidate>
            @csrf
            @method('PUT')
            @include('classes._form')
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('classes.show', $schoolClass) }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save changes</button>
            </div>
        </form>
    </div>
@endsection
