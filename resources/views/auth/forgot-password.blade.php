@extends('layouts.auth')
@section('title', 'Lupa Password')
@section('content')
<h1>Lupa password</h1>
<p class="subtitle">Masukkan email Anda, kami kirim tautan reset password.</p>
<form method="POST" action="{{ route('password.email') }}">@csrf
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
    </div>
    <button type="submit" class="btn btn-primary w-100">Kirim Tautan Reset</button>
</form>
<div class="text-center mt-3 small"><a href="{{ route('login') }}">Kembali ke login</a></div>
@endsection
