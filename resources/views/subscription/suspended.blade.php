@extends('layouts.auth')
@section('title', 'Akun Disuspend')
@section('content')
<div class="text-center mb-3">
    <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size:3rem;"></i>
    <h1 class="mt-2">Akun Anda di-suspend</h1>
    <p class="subtitle">{{ $tenant?->suspended_reason ?? 'Trial 3 hari Anda telah habis.' }}</p>
</div>
<p class="text-center text-muted small">Untuk melanjutkan, pilih paket berlangganan di bawah ini. Pembayaran via Pakasir.</p>

@if($plans->isEmpty())
    <div class="alert alert-info">Belum ada paket aktif. Hubungi admin platform.</div>
@else
    @foreach($plans as $plan)
        <div class="card mb-2"><div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $plan->name }}</strong>
                <div class="small text-muted">Hingga {{ number_format($plan->max_customers) }} pelanggan</div>
            </div>
            <div class="text-end">
                <div class="fw-bold">Rp {{ number_format($plan->price_idr, 0, ',', '.') }} / bulan</div>
                <form method="POST" action="{{ route('subscription.checkout', $plan) }}" class="mt-1">@csrf<button class="btn btn-primary btn-sm">Pilih</button></form>
            </div>
        </div></div>
    @endforeach
@endif

<form method="POST" action="{{ route('logout') }}" class="text-center mt-3">@csrf<button class="btn btn-link small">Keluar</button></form>
@endsection
