<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/adminkit.css') }}">
    @stack('head')
</head>
<body>
    @php($tenant = app()->bound('current_tenant') ? app('current_tenant') : auth()->user()?->tenant)
    <div class="wrapper">
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-content">
                <a class="sidebar-brand" href="{{ route('dashboard') }}">
                    <span class="brand-mark"><i class="bi bi-router"></i></span>{{ $tenant?->business_name ?: $tenant?->name ?: config('app.name') }}
                </a>
                <ul class="sidebar-nav">
                    <li class="sidebar-header">Utama</li>
                    <li class="sidebar-item">
                        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="bi bi-grid"></i><span>Dashboard</span>
                        </a>
                    </li>

                    <li class="sidebar-header">Manajemen</li>
                    <li class="sidebar-item">
                        <a href="{{ route('customers.index') }}" class="sidebar-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i><span>Pelanggan</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('packages.index') }}" class="sidebar-link {{ request()->routeIs('packages.*') ? 'active' : '' }}">
                            <i class="bi bi-box-seam"></i><span>Paket Internet</span>
                        </a>
                    </li>

                    <li class="sidebar-header">Tagihan</li>
                    <li class="sidebar-item">
                        <a href="{{ route('invoices.index') }}" class="sidebar-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                            <i class="bi bi-receipt"></i><span>Invoice</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('payments.index') }}" class="sidebar-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                            <i class="bi bi-cash-coin"></i><span>Pembayaran</span>
                        </a>
                    </li>

                    <li class="sidebar-header">Konfigurasi</li>
                    <li class="sidebar-item">
                        <a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <i class="bi bi-gear"></i><span>Pengaturan</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('subscription.plans') }}" class="sidebar-link {{ request()->routeIs('subscription.*') ? 'active' : '' }}">
                            <i class="bi bi-stars"></i><span>Subscription</span>
                        </a>
                    </li>
                </ul>
                <div class="sidebar-footer">
                    @if($tenant)
                        Plan: <strong>{{ ucfirst($tenant->status) }}</strong>
                        @if($tenant->isTrial() && $tenant->trial_ends_at)
                            <br>Trial sisa: {{ $tenant->trialDaysLeft() }} hari
                        @endif
                    @endif
                </div>
            </div>
        </nav>

        <div class="main">
            @if($tenant?->isTrial() && $tenant->trial_ends_at)
                <div class="trial-banner {{ $tenant->trialDaysLeft() <= 1 ? 'danger' : '' }}">
                    <i class="bi bi-hourglass-split me-1"></i>
                    Mode Trial · {{ $tenant->trialDaysLeft() }} hari tersisa.
                    <a href="{{ route('subscription.plans') }}" class="ms-2 fw-semibold text-decoration-underline">Upgrade sekarang</a>
                </div>
            @endif

            <nav class="navbar navbar-expand navbar-app">
                <button class="btn-burger" type="button" id="sidebar-toggle"><i class="bi bi-list fs-4"></i></button>
                <div class="ms-auto d-flex align-items-center gap-3">
                    <span class="text-muted small d-none d-md-inline">Halo, {{ auth()->user()->name }}</span>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('settings.index') }}"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
                            <li><a class="dropdown-item" href="{{ route('subscription.plans') }}"><i class="bi bi-stars me-2"></i>Subscription</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">@csrf
                                    <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <main class="content">
                @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
                @if(session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebar').classList.toggle('collapsed');
        });
    </script>
    @stack('scripts')
</body>
</html>
