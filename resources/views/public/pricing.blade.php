@extends('layouts.public')
@section('title', 'Harga - '.config('app.name'))
@section('content')
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="fw-bold">Pilih paket sesuai jumlah pelanggan</h1>
            <p class="text-muted">Bayar bulanan via Pakasir. Trial 3 hari otomatis aktif saat daftar.</p>
        </div>
        <div class="row g-4 justify-content-center">
            @foreach($plans as $i => $plan)
                <div class="col-md-4">
                    <div class="pricing-card {{ $i === 1 ? 'featured' : '' }}">
                        @if($i === 1)<div class="badge bg-primary mb-2">PALING POPULER</div>@endif
                        <h4>{{ $plan->name }}</h4>
                        <div class="price my-3">Rp {{ number_format($plan->price_idr, 0, ',', '.') }} <small>/bulan</small></div>
                        <ul class="list-unstyled mb-4 small text-muted">
                            <li><i class="bi bi-check-circle text-success me-2"></i>Hingga {{ number_format($plan->max_customers, 0, ',', '.') }} pelanggan</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Invoice otomatis & PDF</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Integrasi MikroTik PPPoE</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Notifikasi WhatsApp</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Pakasir per-owner</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-{{ $i === 1 ? 'primary' : 'outline-primary' }} w-100">Mulai Trial</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
