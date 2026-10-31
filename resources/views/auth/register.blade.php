@extends('layouts.guest')

@section('title', 'Create account')

@section('content')
    <h1 class="h3 mb-1">Create your account</h1>
    <p class="text-muted mb-4">New accounts start as student accounts. The school office links your account to your student record.</p>

    <form method="POST" action="{{ route('register.store') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Full name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                   class="form-control @error('name') is-invalid @enderror"
                   required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   required autocomplete="new-password" aria-describedby="passwordHelp">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div id="passwordHelp" class="form-text">At least 8 characters.</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirm password</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   class="form-control" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="bi bi-person-plus me-1"></i> Create account
        </button>
    </form>

    <p class="text-center text-muted small mt-4 mb-0">
        Already registered? <a href="{{ route('login') }}" class="fw-semibold">Log in</a>
    </p>
@endsection
