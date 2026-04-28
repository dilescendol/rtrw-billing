@extends('layouts.public')
@section('title', 'Billing ISP RT/RW Net Modern - '.config('app.name'))
@section('content')
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="mb-3">Billing ISP RT/RW Net <br><span class="text-warning">tanpa ribet</span></h1>
                <p class="lead mb-4">Kelola pelanggan, paket internet, NAS MikroTik, RADIUS, GenieACS, panel pelanggan, sampai notifikasi WhatsApp dalam satu dashboard. Coba gratis 3 hari—tanpa kartu kredit.</p>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('register') }}" class="btn btn-warning btn-lg fw-semibold">Mulai Gratis 3 Hari</a>
                    <a href="{{ route('pricing') }}" class="btn btn-outline-light btn-lg">Lihat Harga</a>
                </div>
                <div class="mt-4 small opacity-75"><i class="bi bi-shield-check me-1"></i>Data tiap owner terisolasi · Mode terang/gelap · Notifikasi real-time per role</div>
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
            <p class="text-muted">Dari pencatatan pelanggan sampai remote IP customer—semua di satu tempat.</p>
        </div>
        <div class="row g-4">
            @php($features = [
                ['bi-shield-lock',     'Super Admin & Admin',    'Akun super admin untuk tim platform dan akun admin/owner untuk tiap operator yang berlangganan.', 'tersedia'],
                ['bi-house-heart',     'Landing & Pricing',       'Halaman publik untuk menampilkan fungsi produk, fitur, dan paket harga.', 'tersedia'],
                ['bi-router',          'NAS · MikroTik · PPPoE',  'Auto-create user PPPoE, profile rate-limit, hotspot user/voucher, batas sesuai paket.', 'roadmap'],
                ['bi-broadcast',       'RADIUS + GenieACS',       'Integrasi RADIUS untuk autentikasi PPPoE/Hotspot dan GenieACS untuk TR-069 ONT/CPE.', 'roadmap'],
                ['bi-moon-stars',      'Mode Terang / Gelap',     'Tema gelap nyaman untuk monitoring malam hari, tersimpan per-perangkat.', 'tersedia'],
                ['bi-whatsapp',        'WA Gateway · Auto Bill',  'Reminder tagihan, notif lunas, dan broadcast via WhatsApp Gateway terintegrasi.', 'tersedia'],
                ['bi-person-badge',    'Panel Pelanggan',         'Pelanggan login mandiri, lihat tagihan, riwayat pembayaran, dan ubah profil.', 'tersedia'],
                ['bi-activity',        'Monitor Mikrotik On/Off', 'Owner memantau status mikrotik (uptime, signal, traffic) langsung dari dashboard.', 'roadmap'],
                ['bi-terminal',        'Remote IP Pelanggan',     'Owner memberi remote/tindakan ke IP pelanggan tanpa perlu visit ke rumah.', 'roadmap'],
                ['bi-people-fill',     'Role per Pekerjaan',      'Admin, Teknisi, Kolektor, Pelanggan—setiap role hanya melihat menu yang relevan.', 'tersedia'],
                ['bi-bell',            'Notif Bar per Role',      'Bell notifikasi di samping akun, isi disesuaikan dengan role (admin / teknisi / kolektor / pelanggan).', 'tersedia'],
                ['bi-chat-dots',       'Chat',                    'Komunikasi internal antara admin, teknisi, kolektor, dan pelanggan dalam satu jendela.', 'roadmap'],
            ])
            @foreach($features as [$icon, $title, $desc, $stage])
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 p-3 position-relative">
                        @if($stage === 'roadmap')
                            <span class="badge text-bg-secondary position-absolute top-0 end-0 m-2">Roadmap</span>
                        @else
                            <span class="badge text-bg-success position-absolute top-0 end-0 m-2">Tersedia</span>
                        @endif
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
                            <li><i class="bi bi-check2 me-1 text-success"></i>Maksimal {{ $plan->max_customers ?? 'Unlimited' }} pelanggan</li>
                            @if($plan->allow_mikrotik)<li><i class="bi bi-check2 me-1 text-success"></i>Integrasi MikroTik</li>@endif
                            @if($plan->allow_whatsapp)<li><i class="bi bi-check2 me-1 text-success"></i>Notifikasi WhatsApp</li>@endif
                            @if($plan->allow_pdf_invoice)<li><i class="bi bi-check2 me-1 text-success"></i>Invoice PDF</li>@endif
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-primary w-100">Pilih {{ $plan->name }}</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
