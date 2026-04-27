@extends('layouts.app')
@section('title', 'Subscription')
@section('content')
<h3 class="mb-3">Subscription</h3>
<div class="row g-3">
    @foreach($plans as $i => $plan)
        <div class="col-md-4">
            <div class="card pricing-card {{ $tenant->plan_id === $plan->id ? 'featured' : '' }}">
                <h4>{{ $plan->name }}</h4>
                <div class="price my-3">Rp {{ number_format($plan->price_idr, 0, ',', '.') }} <small>/bulan</small></div>
                <ul class="list-unstyled mb-4 small text-muted">
                    <li><i class="bi bi-check-circle text-success me-2"></i>Hingga {{ number_format($plan->max_customers) }} pelanggan</li>
                    <li><i class="bi bi-check-circle text-success me-2"></i>Invoice otomatis & PDF</li>
                    <li><i class="bi bi-check-circle text-success me-2"></i>MikroTik PPPoE</li>
                    <li><i class="bi bi-check-circle text-success me-2"></i>WhatsApp Fonnte</li>
                </ul>
                <form method="POST" action="{{ route('subscription.checkout', $plan) }}">@csrf
                    <button class="btn btn-{{ $tenant->plan_id === $plan->id ? 'success' : 'primary' }} w-100">
                        {{ $tenant->plan_id === $plan->id ? 'Perpanjang' : 'Pilih Paket' }}
                    </button>
                </form>
            </div>
        </div>
    @endforeach
</div>
@if($tenant->plan_ends_at)
    <p class="text-muted small mt-3">Paket aktif sampai: <strong>{{ $tenant->plan_ends_at->translatedFormat('d M Y H:i') }}</strong></p>
@endif
@endsection
