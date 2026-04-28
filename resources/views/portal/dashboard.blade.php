@extends('layouts.portal')
@section('title', 'Beranda')
@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Halo,</div>
            <h4 class="mb-1">{{ $customer->name }}</h4>
            <div class="small text-muted">{{ $customer->code }}</div>
            <hr>
            <div class="small">
                <div><strong>Paket:</strong> {{ $customer->package?->name ?: '-' }}</div>
                <div><strong>Status:</strong>
                    @if($customer->status === 'active')
                        <span class="badge bg-success">Aktif</span>
                    @elseif($customer->status === 'isolated')
                        <span class="badge bg-warning">Diisolir</span>
                    @else
                        <span class="badge bg-secondary">{{ ucfirst($customer->status) }}</span>
                    @endif
                </div>
                <div><strong>Telepon:</strong> {{ $customer->phone ?: '-' }}</div>
            </div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-{{ $unpaidTotal > 0 ? 'danger' : 'success' }}"><div class="card-body">
            <div class="text-muted small">Total Tunggakan</div>
            <h3 class="mb-2">Rp {{ number_format($unpaidTotal, 0, ',', '.') }}</h3>
            @if($unpaidTotal > 0)
                <a href="{{ route('portal.invoices.index') }}" class="btn btn-sm btn-danger">Lihat Tagihan</a>
            @else
                <span class="badge bg-success">Lunas</span>
            @endif
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Pembayaran Terakhir</div>
            @if($latestPayment)
                <h5 class="mb-1">Rp {{ number_format($latestPayment->amount_idr, 0, ',', '.') }}</h5>
                <div class="small text-muted">{{ optional($latestPayment->paid_at)->format('d M Y') }} · {{ ucfirst($latestPayment->method) }}</div>
            @else
                <h5 class="mb-1 text-muted">—</h5>
                <div class="small text-muted">Belum ada pembayaran tercatat.</div>
            @endif
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><strong>Tagihan Terbaru</strong></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead><tr>
                <th>Periode</th><th>No. Invoice</th><th>Jatuh Tempo</th><th class="text-end">Jumlah</th><th>Status</th><th></th>
            </tr></thead>
            <tbody>
            @forelse($invoices as $inv)
                <tr>
                    <td>{{ optional($inv->period_start)->format('M Y') }}</td>
                    <td>{{ $inv->invoice_no }}</td>
                    <td>{{ optional($inv->due_date)->format('d M Y') }}</td>
                    <td class="text-end">Rp {{ number_format($inv->amount_idr, 0, ',', '.') }}</td>
                    <td>
                        @if($inv->status === 'paid')<span class="badge bg-success">Lunas</span>
                        @elseif($inv->status === 'overdue')<span class="badge bg-danger">Lewat Tempo</span>
                        @elseif($inv->status === 'cancelled')<span class="badge bg-secondary">Dibatalkan</span>
                        @else<span class="badge bg-warning">Belum Bayar</span>@endif
                    </td>
                    <td><a href="{{ route('portal.invoices.show', $inv) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada tagihan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
