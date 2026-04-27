@extends('layouts.public')
@section('title', 'Billing ISP RT/RW Net Modern - '.config('app.name'))
@section('content')
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="mb-3">Billing ISP RT/RW Net <br><span class="text-warning">tanpa ribet</span></h1>
                <p class="lead mb-4">Kelola pelanggan, paket internet, tagihan otomatis, dan integrasi MikroTik dalam satu dashboard. Coba gratis 3 hari—tanpa kartu kredit.</p>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('register') }}" class="btn btn-warning btn-lg fw-semibold">Mulai Gratis 3 Hari</a>
                    <a href="{{ route('pricing') }}" class="btn btn-outline-light btn-lg">Lihat Harga</a>
                </div>
                <div class="mt-4 small opacity-75"><i class="bi bi-shield-check me-1"></i>Data tiap owner terisolasi · Tidak bisa dipakai berulang dengan email yang sama</div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div class="bg-white rounded-3 p-3 shadow-lg" style="transform: rotate(2deg);">
                    <div class="d-flex justify-content-between text-muted small mb-2">
                        <span><i class="bi bi-circle-fill text-danger"></i> <i class="bi bi-circle-fill text-warning"></i> <i class="bi bi-circle-fill text-success"></i></span>
                        <span>dashboard.rtrw.app</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-6"><div class="bg-light rounded p-3"><div class="text-muted small">Pelanggan</div><div class="fs-4 fw-bold">128</div></div></div>
                        <div class="col-6"><div class="bg-light rounded p-3"><div class="text-muted small">Pendapatan</div><div class="fs-4 fw-bold">Rp 18,9 jt</div></div></div>
                        <div class="col-12"><div class="bg-light rounded p-3 text-muted small">Tagihan jatuh tempo: <strong class="text-danger">12</strong></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="fitur" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Fitur lengkap untuk operator RT/RW Net</h2>
            <p class="text-muted">Mulai dari pencatatan pelanggan sampai isolir otomatis di MikroTik.</p>
        </div>
        <div class="row g-4">
            @php($features = [
                ['bi-people', 'Manajemen Pelanggan', 'Catat data pelanggan, alamat, paket, dan status koneksi.'],
                ['bi-box-seam', 'Paket Internet', 'Buat paket sesuai harga dan kecepatan, mapping ke profile MikroTik.'],
                ['bi-receipt', 'Invoice Otomatis', 'Generate tagihan tiap awal bulan dengan jadwal scheduler.'],
                ['bi-cash-coin', 'Pembayaran Pakasir', 'Terima QRIS / VA via Pakasir dengan API key milik owner sendiri.'],
                ['bi-router', 'Integrasi MikroTik', 'PPPoE auto-create & auto-isolir kalau telat bayar.'],
                ['bi-whatsapp', 'Notifikasi WhatsApp', 'Reminder tagihan via Fonnte tanpa setup aplikasi tambahan.'],
                ['bi-file-earmark-pdf', 'Invoice PDF', 'Cetak/kirim invoice dalam format PDF profesional.'],
                ['bi-shield-lock', 'Privasi Owner', 'Tiap owner pakai key Pakasir sendiri—uang masuk langsung ke rekening sendiri.'],
            ])
            @foreach($features as [$icon, $title, $desc])
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 p-3">
                        <div class="feature-icon"><i class="bi {{ $icon }}"></i></div>
                        <h5 class="mt-2">{{ $title }}</h5>
                        <p class="text-muted mb-0 small">{{ $desc }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@if($plans->isNotEmpty())
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Harga simpel, tanpa kontrak</h2>
            <p class="text-muted">Trial 3 hari penuh fitur. Kalau tidak upgrade, akun otomatis disuspend (bukan auto-charge).</p>
        </div>
        <div class="row g-4">
            @foreach($plans as $i => $plan)
                <div class="col-md-4">
                    <div class="pricing-card {{ $i === 1 ? 'featured' : '' }} bg-white">
                        @if($i === 1)<div class="badge bg-primary mb-2">PALING POPULER</div>@endif
                        <h4>{{ $plan->name }}</h4>
                        <div class="price my-3">Rp {{ number_format($plan->price_idr, 0, ',', '.') }} <small>/bulan</small></div>
                        <ul class="list-unstyled mb-4 small text-muted">
                            <li><i class="bi bi-check-circle text-success me-2"></i>Hingga {{ number_format($plan->max_customers, 0, ',', '.') }} pelanggan</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Invoice otomatis & PDF</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Integrasi MikroTik PPPoE</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Notifikasi WhatsApp (Fonnte)</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Pakasir API key milik sendiri</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-{{ $i === 1 ? 'primary' : 'outline-primary' }} w-100">Pilih {{ $plan->name }}</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="py-5 text-center">
    <div class="container">
        <h3 class="fw-bold">Siap atur billing RT/RW Net Anda?</h3>
        <p class="text-muted mb-3">Daftar sekarang, dapat trial 3 hari, lanjut langsung pilih paket atau biarkan akun di-suspend kalau belum siap.</p>
        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Buat Akun Gratis</a>
    </div>
</section>
@endsection
