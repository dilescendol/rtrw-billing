@php($theme = request()->cookie('theme', 'light'))
<!doctype html>
<html lang="id" data-bs-theme="{{ $theme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') · {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/adminkit.css') }}">
</head>
<body>
    <div class="auth-wrap">
        <div class="auth-card">
            <div class="brand-bar">
                <span class="brand-mark" style="background:#3b7ddd;color:#fff;width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px"><i class="bi bi-router"></i></span>
                <span>{{ config('app.name') }}</span>
            </div>
            @if(session('status'))<div class="alert alert-success py-2 px-3 small">{{ session('status') }}</div>@endif
            @if($errors->any())
                <div class="alert alert-danger py-2 px-3 small mb-3"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            @yield('content')
            <div class="text-center mt-4 small text-muted">
                <a href="{{ route('home') }}" class="text-muted text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Kembali ke beranda</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
