@extends('layouts.app')
@section('title', 'Pembayaran')
@section('content')
<h3 class="mb-3">Pembayaran</h3>
<div class="card"><div class="card-body">
    <form method="GET" class="mb-3">
        <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            <option value="">Semua status</option>
            @foreach(['pending','verified','rejected'] as $s)<option value="{{ $s }}" @selected($status===$s)>{{ ucfirst($s) }}</option>@endforeach
        </select>
    </form>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Tgl</th><th>Pelanggan</th><th>Invoice</th><th>Metode</th><th class="text-end">Jumlah</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td class="small">{{ optional($p->paid_at)->translatedFormat('d M Y') }}</td>
                        <td>{{ $p->invoice?->customer?->name }}</td>
                        <td><a href="{{ route('invoices.show', $p->invoice) }}" class="text-decoration-none">{{ $p->invoice?->invoice_no }}</a></td>
                        <td>{{ ucfirst($p->method) }}</td>
                        <td class="text-end text-currency">Rp {{ number_format($p->amount_idr, 0, ',', '.') }}</td>
                        <td><span class="status-pill status-{{ $p->status }}">{{ $p->status }}</span></td>
                        <td class="text-end">
                            @if($p->status==='pending')
                                <form method="POST" action="{{ route('payments.verify', $p) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success">Verifikasi</button></form>
                                <form method="POST" action="{{ route('payments.reject', $p) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-danger">Tolak</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada pembayaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $payments->links() }}
</div></div>
@endsection
