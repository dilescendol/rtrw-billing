@extends('layouts.portal')
@section('title', 'Detail Tagihan')
@section('content')
<a href="{{ route('portal.invoices.index') }}" class="btn btn-link mb-2 p-0"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
<div class="card mb-4"><div class="card-body">
    <div class="d-flex justify-content-between flex-wrap gap-3">
        <div>
            <h4 class="mb-0">Invoice {{ $invoice->invoice_no }}</h4>
            <div class="small text-muted">Periode {{ optional($invoice->period_start)->format('d M Y') }} – {{ optional($invoice->period_end)->format('d M Y') }}</div>
        </div>
        <div class="text-end">
            <div class="text-muted small">Jumlah</div>
            <h3 class="mb-0">Rp {{ number_format($invoice->amount_idr, 0, ',', '.') }}</h3>
            <div>
                @if($invoice->status === 'paid')<span class="badge bg-success">Lunas</span>
                @elseif($invoice->status === 'overdue')<span class="badge bg-danger">Lewat Tempo</span>
                @elseif($invoice->status === 'cancelled')<span class="badge bg-secondary">Dibatalkan</span>
                @else<span class="badge bg-warning">Belum Bayar</span>@endif
            </div>
        </div>
    </div>
    <hr>
    <div class="row g-3 small">
        <div class="col-md-4"><strong>Jatuh Tempo:</strong> {{ optional($invoice->due_date)->format('d M Y') }}</div>
        <div class="col-md-4"><strong>Paket:</strong> {{ $invoice->package?->name ?: '-' }}</div>
        <div class="col-md-4"><strong>Dibayar:</strong> {{ $invoice->paid_at ? $invoice->paid_at->format('d M Y H:i') : '-' }}</div>
    </div>
    @if($invoice->notes)
        <div class="alert alert-info small mt-3 mb-0">{{ $invoice->notes }}</div>
    @endif
</div></div>
@endsection
