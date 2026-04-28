@extends('layouts.portal')
@section('title', 'Pembayaran')
@section('content')
<h3 class="mb-3">Riwayat Pembayaran</h3>
<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead><tr>
                <th>Tanggal</th><th>Invoice</th><th>Metode</th><th class="text-end">Jumlah</th><th>Status</th>
            </tr></thead>
            <tbody>
            @forelse($payments as $p)
                <tr>
                    <td>{{ optional($p->paid_at)->format('d M Y') }}</td>
                    <td>{{ $p->invoice?->invoice_no ?: '-' }}</td>
                    <td>{{ ucfirst($p->method) }}</td>
                    <td class="text-end">Rp {{ number_format($p->amount_idr, 0, ',', '.') }}</td>
                    <td>
                        @if($p->status === 'verified')<span class="badge bg-success">Terverifikasi</span>
                        @elseif($p->status === 'rejected')<span class="badge bg-danger">Ditolak</span>
                        @else<span class="badge bg-warning">Menunggu</span>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada pembayaran.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $payments->links() }}</div>
@endsection
