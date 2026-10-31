@extends('layouts.guest')

@section('title', 'Log in')

@section('content')
    <h1 class="h3 mb-1">Welcome back</h1>
    <p class="text-muted mb-4">Log in to your school account to continue.</p>

    <form method="POST" action="{{ route('login.store') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <div class="input-group has-validation">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="you@school.test" required autofocus autocomplete="username">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group has-validation">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="••••••••" required autocomplete="current-password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" @checked(old('remember'))>
            <label class="form-check-label" for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="bi bi-box-arrow-in-right me-1"></i> Log in
        </button>
    </form>

    <p class="text-center text-muted small mt-4 mb-0">
        Don't have an account? <a href="{{ route('register') }}" class="fw-semibold">Create one</a>
    </p>

    @if (app()->isLocal())
        <div class="card mt-4">
            <div class="card-body py-3">
                <div class="small fw-semibold mb-2"><i class="bi bi-info-circle text-success me-1"></i> Local demo accounts</div>
                <div class="small text-muted">
                    admin@school.test · teacher@school.test · student@school.test<br>
                    Password: <code>password</code>
                </div>
            </div>
        </div>
    @endif
@endsection
