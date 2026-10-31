@extends(auth()->check() ? 'layouts.app' : 'layouts.guest')

@section('title', 'Access denied')

@section('content')
    <div class="card">
        <x-empty-state title="You don't have access to this page" icon="bi-shield-lock">
            {{ $exception->getMessage() ?: 'Your account role does not allow this action.' }}
            <div class="mt-3">
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to dashboard</a>
            </div>
        </x-empty-state>
    </div>
@endsection
