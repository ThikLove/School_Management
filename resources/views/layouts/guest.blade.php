<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="auth-shell">
        <aside class="auth-aside">
            <div class="d-flex align-items-center gap-2">
                <span class="brand-mark bg-white text-success"><i class="bi bi-mortarboard-fill"></i></span>
                <span class="fw-bold fs-5">{{ config('app.name') }}</span>
            </div>

            <div>
                <h2 class="mb-3">Everything your school runs on, in one place.</h2>
                <p class="mb-4">Manage students, teachers, classes and daily attendance with a clear, simple dashboard.</p>

                <div class="auth-feature"><i class="bi bi-people"></i> Student &amp; teacher records</div>
                <div class="auth-feature"><i class="bi bi-calendar-check"></i> Daily attendance tracking</div>
                <div class="auth-feature"><i class="bi bi-shield-check"></i> Role-based secure access</div>
            </div>

            <small class="text-white-50">&copy; {{ date('Y') }} {{ config('app.name') }}</small>
        </aside>

        <main class="auth-main">
            <div class="auth-card">
                <div class="d-flex d-lg-none align-items-center gap-2 mb-4">
                    <span class="brand-mark"><i class="bi bi-mortarboard-fill"></i></span>
                    <span class="fw-bold fs-5">{{ config('app.name') }}</span>
                </div>

                @include('partials.flash')

                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
