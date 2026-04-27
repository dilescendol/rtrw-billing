@extends('layouts.auth')
@section('title', 'Verifikasi Email')
@section('content')
<h1>Verifikasi email</h1>
<p class="subtitle">Kami sudah mengirim tautan verifikasi ke <strong>{{ auth()->user()->email }}</strong>. Klik tautan di email untuk lanjut.</p>
@if (session('status') == 'verification-link-sent')
    <div class="alert alert-success small">Tautan baru telah dikirim ke email Anda.</div>
@endif
<form method="POST" action="{{ route('verification.send') }}">@csrf
    <button type="submit" class="btn btn-primary w-100">Kirim ulang tautan</button>
</form>
<form method="POST" action="{{ route('logout') }}" class="mt-3 text-center">@csrf
    <button type="submit" class="btn btn-link">Keluar</button>
</form>
@endsection
