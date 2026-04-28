@php($theme = request()->cookie('theme', 'light'))
<!doctype html>
<html lang="id" data-bs-theme="{{ $theme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin') · {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/adminkit.css') }}">
</head>
<body>
    <div class="wrapper">
        <nav class="sidebar">
            <div class="sidebar-content">
                <a class="sidebar-brand" href="{{ route('superadmin.tenants.index') }}">
                    <span class="brand-mark"><i class="bi bi-shield-lock"></i></span>Super Admin
                </a>
                <ul class="sidebar-nav">
                    <li class="sidebar-header">Platform</li>
                    <li class="sidebar-item">
                        <a href="{{ route('superadmin.tenants.index') }}" class="sidebar-link {{ request()->routeIs('superadmin.tenants.*') ? 'active' : '' }}">
                            <i class="bi bi-buildings"></i><span>Tenant</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('superadmin.plans.index') }}" class="sidebar-link {{ request()->routeIs('superadmin.plans.*') ? 'active' : '' }}">
                            <i class="bi bi-stars"></i><span>Paket Subscription</span>
                        </a>
                    </li>
                </ul>
                <div class="sidebar-footer">Mode: Super Admin</div>
            </div>
        </nav>
        <div class="main">
            <nav class="navbar navbar-expand navbar-app">
                <div class="ms-auto d-flex align-items-center gap-3">
                    @include('partials.theme_toggle')
                    @include('partials.notification_bell')
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header small"><span class="badge text-bg-danger">{{ auth()->user()->roleLabel() }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">@csrf
                                    <button class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <main class="content">
                @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
                @yield('content')
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('partials.theme_script')
</body>
</html>
