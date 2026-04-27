@extends('layouts.auth')
@section('title', 'Reset Password')
@section('content')
<h1>Reset password</h1>
<p class="subtitle">Masukkan password baru Anda.</p>
<form method="POST" action="{{ route('password.update') }}">@csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $email ?? '') }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Password baru</label>
        <input type="password" name="password" class="form-control" required minlength="8">
    </div>
    <div class="mb-3">
        <label class="form-label">Konfirmasi password</label>
        <input type="password" name="password_confirmation" class="form-control" required minlength="8">
    </div>
    <button type="submit" class="btn btn-primary w-100">Simpan password baru</button>
</form>
@endsection
