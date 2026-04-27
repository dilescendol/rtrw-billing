@extends('layouts.app')
@section('title', 'Invoice '.$invoice->invoice_no)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Invoice {{ $invoice->invoice_no }}</h3>
    <div class="d-flex gap-2">
        <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-light" target="_blank"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
        @if(! $invoice->isPaid())
            <a href="{{ route('payments.create', $invoice) }}" class="btn btn-success"><i class="bi bi-cash me-1"></i>Catat Pembayaran</a>
            <form method="POST" action="{{ route('invoices.pay', $invoice) }}" class="d-inline">@csrf<button class="btn btn-primary"><i class="bi bi-credit-card me-1"></i>Bayar via Pakasir</button></form>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="text-muted small">Untuk</div>
                    <h5 class="mb-0">{{ $invoice->customer?->name }}</h5>
                    <div class="small text-muted">{{ $invoice->customer?->phone }} · {{ $invoice->customer?->address }}</div>
                </div>
                <div class="text-end"><span class="status-pill status-{{ $invoice->status }}">{{ $invoice->status }}</span></div>
            </div>

            <table class="table mb-0">
                <thead><tr><th>Deskripsi</th><th class="text-end">Jumlah</th></tr></thead>
                <tbody>
                    <tr>
                        <td>{{ $invoice->package?->name ?? 'Layanan Internet' }} – Periode {{ $invoice->period_start->translatedFormat('M Y') }}</td>
                        <td class="text-end text-currency">Rp {{ number_format($invoice->amount_idr, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr><th>Total</th><th class="text-end text-currency">Rp {{ number_format($invoice->amount_idr, 0, ',', '.') }}</th></tr>
                </tfoot>
            </table>
        </div></div>

        <div class="card mt-3"><div class="card-body">
            <h6 class="card-title mb-3">Pembayaran</h6>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Tanggal</th><th>Metode</th><th class="text-end">Jumlah</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($invoice->payments as $p)
                            <tr>
                                <td>{{ optional($p->paid_at)->translatedFormat('d M Y') }}</td>
                                <td>{{ ucfirst($p->method) }} <small class="text-muted">{{ $p->reference }}</small></td>
                                <td class="text-end text-currency">Rp {{ number_format($p->amount_idr, 0, ',', '.') }}</td>
                                <td><span class="status-pill status-{{ $p->status }}">{{ $p->status }}</span></td>
                                <td class="text-end">
                                    @if($p->status === 'pending')
                                        <form method="POST" action="{{ route('payments.verify', $p) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success">Verifikasi</button></form>
                                        <form method="POST" action="{{ route('payments.reject', $p) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-danger">Tolak</button></form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada pembayaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div></div>
    </div>
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <h6 class="card-title">Detail</h6>
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted">Periode</dt><dd class="col-7">{{ $invoice->period_start->translatedFormat('d M') }} – {{ $invoice->period_end->translatedFormat('d M Y') }}</dd>
                <dt class="col-5 text-muted">Jatuh Tempo</dt><dd class="col-7">{{ $invoice->due_date->translatedFormat('d M Y') }}</dd>
                @if($invoice->paid_at)
                    <dt class="col-5 text-muted">Dibayar</dt><dd class="col-7">{{ $invoice->paid_at->translatedFormat('d M Y H:i') }}</dd>
                @endif
                @if($invoice->pakasir_payment_url)
                    <dt class="col-5 text-muted">Link Pakasir</dt><dd class="col-7"><a href="{{ $invoice->pakasir_payment_url }}" target="_blank">Buka</a></dd>
                @endif
            </dl>
        </div></div>
    </div>
</div>

@if(! $invoice->isPaid())
<form method="POST" action="{{ route('invoices.destroy', $invoice) }}" class="mt-3" onsubmit="return confirm('Hapus invoice ini?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Hapus invoice</button></form>
@endif
@endsection
