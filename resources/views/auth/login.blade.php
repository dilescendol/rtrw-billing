@extends('layouts.auth')
@section('title', 'Masuk')
@section('content')
<h1>Masuk</h1>
<p class="subtitle">Masuk ke dashboard billing Anda.</p>
<form method="POST" action="{{ route('login') }}">@csrf
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="d-flex justify-content-between mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Ingat saya</label>
        </div>
        <a href="{{ route('password.request') }}" class="small">Lupa password?</a>
    </div>
    <button type="submit" class="btn btn-primary w-100">Masuk</button>
</form>
<div class="text-center mt-3 small">Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a></div>
@endsection
