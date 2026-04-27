@extends('layouts.app')
@section('title', 'Invoice')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Invoice</h3>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('invoices.generate_month') }}" class="d-flex gap-1">@csrf
            <input type="month" name="period" value="{{ now()->format('Y-m') }}" class="form-control form-control-sm">
            <button class="btn btn-outline-primary btn-sm"><i class="bi bi-magic"></i> Generate Bulan</button>
        </form>
        <a href="{{ route('invoices.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Buat Invoice</a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5"><input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Cari nomor / nama pelanggan"></div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua status</option>
                    @foreach(['unpaid','paid','overdue','cancelled'] as $s)<option value="{{ $s }}" @selected($status===$s)>{{ ucfirst($s) }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-outline-secondary w-100"><i class="bi bi-search"></i> Cari</button></div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>No</th><th>Pelanggan</th><th>Periode</th><th>Jatuh Tempo</th><th class="text-end">Jumlah</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td><a href="{{ route('invoices.show', $inv) }}" class="text-decoration-none">{{ $inv->invoice_no }}</a></td>
                            <td>{{ $inv->customer?->name }}<div class="small text-muted">{{ $inv->package?->name }}</div></td>
                            <td class="small">{{ $inv->period_start->translatedFormat('M Y') }}</td>
                            <td class="small {{ !$inv->isPaid() && $inv->due_date->isPast() ? 'text-danger' : '' }}">{{ $inv->due_date->translatedFormat('d M Y') }}</td>
                            <td class="text-end text-currency">Rp {{ number_format($inv->amount_idr, 0, ',', '.') }}</td>
                            <td><span class="status-pill status-{{ $inv->status }}">{{ $inv->status }}</span></td>
                            <td class="text-end"><a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada invoice.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $invoices->links() }}
    </div>
</div>
@endsection
