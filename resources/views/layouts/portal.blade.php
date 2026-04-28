@php($theme = request()->cookie('theme', 'light'))
<!doctype html>
<html lang="id" data-bs-theme="{{ $theme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Pelanggan') · {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/adminkit.css') }}">
    @stack('head')
</head>
<body class="portal-body">
@php($user = auth()->user())
@php($customer = $user?->customer)
@php($tenant = $user?->tenant)
<nav class="navbar navbar-expand-lg navbar-app portal-nav">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('portal.dashboard') }}">
            <span class="brand-mark"><i class="bi bi-router"></i></span>
            <strong>{{ $tenant?->business_name ?: config('app.name') }}</strong>
        </a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#portalNav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="portalNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}" href="{{ route('portal.dashboard') }}"><i class="bi bi-house me-1"></i>Beranda</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('portal.invoices.*') ? 'active' : '' }}" href="{{ route('portal.invoices.index') }}"><i class="bi bi-receipt me-1"></i>Tagihan</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('portal.payments.*') ? 'active' : '' }}" href="{{ route('portal.payments.index') }}"><i class="bi bi-cash-coin me-1"></i>Pembayaran</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('portal.profile') ? 'active' : '' }}" href="{{ route('portal.profile') }}"><i class="bi bi-person me-1"></i>Profil</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                @include('partials.theme_toggle')
                @include('partials.notification_bell')
                <div class="dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>{{ $customer?->name ?: $user->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header small">
                            <span class="badge text-bg-primary">{{ $user->roleLabel() }}</span>
                        </li>
                        <li><a class="dropdown-item" href="{{ route('portal.profile') }}"><i class="bi bi-gear me-2"></i>Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
<main class="container py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    @yield('content')
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@include('partials.theme_script')
@stack('scripts')
</body>
</html>
