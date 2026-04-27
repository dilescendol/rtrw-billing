@extends('layouts.auth')
@section('title', 'Daftar')
@section('content')
<h1>Buat akun</h1>
<p class="subtitle">Trial 3 hari penuh fitur. Setelah trial, akun di-suspend hingga Anda upgrade.</p>
<form method="POST" action="{{ route('register') }}">@csrf
    <div class="mb-3">
        <label class="form-label">Nama Pemilik / Owner</label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Nama Usaha (RT/RW Net)</label>
        <input type="text" name="business_name" value="{{ old('business_name') }}" class="form-control" required placeholder="Mis. RT 03 Net">
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">No. WhatsApp</label>
            <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" required placeholder="0812...">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required minlength="8">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Konfirmasi</label>
            <input type="password" name="password_confirmation" class="form-control" required minlength="8">
        </div>
    </div>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="agree" id="agree" value="1" required>
        <label class="form-check-label small" for="agree">Saya setuju dengan ketentuan layanan & tahu bahwa email yang sama tidak bisa dipakai untuk daftar berulang dalam 30 hari.</label>
    </div>
    <button type="submit" class="btn btn-primary w-100">Daftar & Mulai Trial</button>
</form>
<div class="text-center mt-3 small">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></div>
@endsection
