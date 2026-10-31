@php
    $user = auth()->user();

    // Menu entries per role. An entry only appears once its route exists,
    // so pages added in later steps show up in the menu automatically.
    $menu = match ($user->role) {
        'admin' => [
            'Overview' => [
                ['label' => 'Dashboard', 'icon' => 'bi-grid-1x2', 'route' => 'dashboard'],
            ],
            'Academics' => [
                ['label' => 'Students', 'icon' => 'bi-people', 'route' => 'students.index', 'active' => 'students.*'],
                ['label' => 'Teachers', 'icon' => 'bi-person-badge', 'route' => 'teachers.index', 'active' => 'teachers.*'],
                ['label' => 'Classes', 'icon' => 'bi-door-open', 'route' => 'classes.index', 'active' => 'classes.*'],
                ['label' => 'Subjects', 'icon' => 'bi-book', 'route' => 'subjects.index', 'active' => 'subjects.*'],
                ['label' => 'Departments', 'icon' => 'bi-building', 'route' => 'departments.index', 'active' => 'departments.*'],
            ],
            'Records' => [
                ['label' => 'Enrollments', 'icon' => 'bi-journal-check', 'route' => 'enrollments.index', 'active' => 'enrollments.*'],
                ['label' => 'Attendance', 'icon' => 'bi-calendar-check', 'route' => 'attendance.index', 'active' => 'attendance.*'],
            ],
        ],
        'teacher' => [
            'Overview' => [
                ['label' => 'Dashboard', 'icon' => 'bi-grid-1x2', 'route' => 'dashboard'],
            ],
            'My teaching' => [
                ['label' => 'My Classes', 'icon' => 'bi-door-open', 'route' => 'classes.index', 'active' => 'classes.*'],
                ['label' => 'My Students', 'icon' => 'bi-people', 'route' => 'students.index', 'active' => 'students.*'],
                ['label' => 'My Subjects', 'icon' => 'bi-book', 'route' => 'subjects.index', 'active' => 'subjects.*'],
                ['label' => 'Attendance', 'icon' => 'bi-calendar-check', 'route' => 'attendance.index', 'active' => 'attendance.*'],
            ],
        ],
        default => [
            'Overview' => [
                ['label' => 'Dashboard', 'icon' => 'bi-grid-1x2', 'route' => 'dashboard'],
            ],
            'My school' => array_filter([
                $user->student ? ['label' => 'My Profile', 'icon' => 'bi-person', 'route' => 'students.show', 'params' => [$user->student], 'active' => 'students.*'] : null,
                $user->student ? ['label' => 'My Class', 'icon' => 'bi-door-open', 'route' => 'classes.show', 'params' => [$user->student->class_id], 'active' => 'classes.*'] : null,
                ['label' => 'My Subjects', 'icon' => 'bi-book', 'route' => 'subjects.index', 'active' => 'subjects.*'],
                ['label' => 'My Attendance', 'icon' => 'bi-calendar-check', 'route' => 'attendance.index', 'active' => 'attendance.*'],
            ]),
        ],
    };
@endphp

<aside class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar" aria-label="Main navigation">
    <div class="sidebar-inner">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('dashboard') }}" class="sidebar-brand">
                <span class="brand-mark"><i class="bi bi-mortarboard-fill"></i></span>
                <span>
                    <span class="brand-name d-block">{{ config('app.name') }}</span>
                    <span class="brand-sub">School administration</span>
                </span>
            </a>
            <button type="button" class="btn-close d-lg-none me-3" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Close menu"></button>
        </div>

        <nav class="sidebar-nav">
            @foreach ($menu as $section => $items)
                @php($items = array_filter($items, fn ($item) => Route::has($item['route'])))

                @if (count($items) > 0)
                    <div class="sidebar-section">{{ $section }}</div>

                    @foreach ($items as $item)
                        <a href="{{ route($item['route'], $item['params'] ?? []) }}"
                           @class(['sidebar-link', 'active' => request()->routeIs($item['active'] ?? $item['route'])])
                           @if (request()->routeIs($item['active'] ?? $item['route'])) aria-current="page" @endif>
                            <i class="bi {{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                @endif
            @endforeach
        </nav>

        <div class="sidebar-footer d-flex align-items-center gap-2">
            <span class="avatar">{{ Str::upper(Str::substr($user->name, 0, 1)) }}</span>
            <div class="min-w-0 grow">
                <div class="fw-semibold text-truncate small">{{ $user->name }}</div>
                <div class="text-muted small text-capitalize">{{ $user->role }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-light btn-icon" title="Log out" aria-label="Log out">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
