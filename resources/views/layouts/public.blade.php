<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/adminkit.css') }}">
</head>
<body>
    <nav class="public-nav">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="{{ route('home') }}" class="text-decoration-none d-flex align-items-center gap-2">
                <span class="brand-mark" style="background:#3b7ddd;color:#fff;width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px"><i class="bi bi-router"></i></span>
                <strong class="text-dark">{{ config('app.name') }}</strong>
            </a>
            <div class="d-flex gap-3 align-items-center">
                <a href="{{ route('home') }}#fitur" class="text-muted text-decoration-none d-none d-md-inline">Fitur</a>
                <a href="{{ route('pricing') }}" class="text-muted text-decoration-none d-none d-md-inline">Harga</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-muted text-decoration-none">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Daftar Gratis</a>
                @endauth
            </div>
        </div>
    </nav>
    @yield('content')
    <footer class="bg-dark text-white-50 py-4 mt-5">
        <div class="container d-flex flex-column flex-md-row justify-content-between gap-2">
            <div>&copy; {{ date('Y') }} {{ config('app.name') }} · Billing ISP RT/RW Net</div>
            <div class="small">Dibuat dengan Laravel & AdminKit</div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
