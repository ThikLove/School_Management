@php($user = auth()->user())

<header class="topbar">
    <button class="btn btn-light btn-icon d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Open menu">
        <i class="bi bi-list fs-5"></i>
    </button>

    <div class="topbar-title grow">
        <h1>@yield('title', 'Dashboard')</h1>
        @hasSection('breadcrumbs')
            <nav aria-label="breadcrumb" class="d-none d-sm-block">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    @yield('breadcrumbs')
                </ol>
            </nav>
        @endif
    </div>

    @if (Route::has('students.index') && $user->can('viewAny', App\Models\Student::class))
        <form class="topbar-search d-none d-md-block grow" method="GET" action="{{ route('students.index') }}" role="search">
            <div class="input-group">
                <span class="input-group-text border-end-0 bg-white"><i class="bi bi-search"></i></span>
                <input type="search" name="search" class="form-control border-start-0 ps-0" placeholder="Search students by name or code…" value="{{ request()->routeIs('students.index') ? request('search') : '' }}" aria-label="Search students">
            </div>
        </form>
    @endif

    <div class="dropdown">
        <button class="btn btn-light btn-icon position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
            <i class="bi bi-bell"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end" style="width: 300px;">
            <div class="px-2 py-1 fw-semibold">Notifications</div>
            <div class="text-center text-muted small py-4">
                <i class="bi bi-check2-circle fs-4 d-block mb-1 text-success"></i>
                You're all caught up.
            </div>
        </div>
    </div>

    <div class="dropdown">
        <button class="btn btn-light d-flex align-items-center gap-2 py-1 ps-1 pe-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="avatar" style="width:2rem;height:2rem;">{{ Str::upper(Str::substr($user->name, 0, 1)) }}</span>
            <span class="d-none d-sm-inline small fw-semibold text-truncate" style="max-width: 9rem;">{{ $user->name }}</span>
            <i class="bi bi-chevron-down small text-muted"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li class="px-3 py-2">
                <div class="fw-semibold">{{ $user->name }}</div>
                <div class="small text-muted">{{ $user->email }}</div>
                <span class="badge badge-soft-success text-capitalize mt-1">{{ $user->role }}</span>
            </li>
            <li><hr class="dropdown-divider"></li>
            @if ($user->student && Route::has('students.show'))
                <li><a class="dropdown-item" href="{{ route('students.show', $user->student) }}"><i class="bi bi-person me-2"></i>My profile</a></li>
            @endif
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Log out</button>
                </form>
            </li>
        </ul>
    </div>
</header>
