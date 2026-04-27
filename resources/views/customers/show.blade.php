@extends('layouts.app')
@section('title', 'Pelanggan: '.$customer->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">{{ $customer->name }} <small class="text-muted">{{ $customer->code }}</small></h3>
    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-light"><i class="bi bi-pencil me-1"></i>Edit</a>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <h6 class="card-title mb-3">Detail</h6>
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted">Status</dt><dd class="col-7"><span class="status-pill status-{{ $customer->status }}">{{ $customer->status }}</span></dd>
                <dt class="col-5 text-muted">Paket</dt><dd class="col-7">{{ $customer->package?->name ?? '-' }}</dd>
                <dt class="col-5 text-muted">Telepon</dt><dd class="col-7">{{ $customer->phone ?? '-' }}</dd>
                <dt class="col-5 text-muted">Email</dt><dd class="col-7">{{ $customer->email ?? '-' }}</dd>
                <dt class="col-5 text-muted">Alamat</dt><dd class="col-7">{{ $customer->address ?? '-' }}</dd>
                <dt class="col-5 text-muted">PPPoE User</dt><dd class="col-7">{{ $customer->pppoe_username ?? '-' }}</dd>
                <dt class="col-5 text-muted">Tgl Pasang</dt><dd class="col-7">{{ optional($customer->installed_at)->translatedFormat('d M Y') ?? '-' }}</dd>
                <dt class="col-5 text-muted">Tgl Jatuh Tempo</dt><dd class="col-7">{{ $customer->due_day }}</dd>
            </dl>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="card-title mb-0">Riwayat Invoice</h6>
                <form method="POST" action="{{ route('invoices.store') }}" class="d-flex gap-1">@csrf
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                    <input type="month" name="period" value="{{ now()->format('Y-m') }}" class="form-control form-control-sm">
                    <button class="btn btn-sm btn-primary">Buat Invoice</button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>No</th><th>Periode</th><th>Jatuh Tempo</th><th class="text-end">Jumlah</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($customer->invoices as $inv)
                            <tr>
                                <td><a href="{{ route('invoices.show', $inv) }}" class="text-decoration-none">{{ $inv->invoice_no }}</a></td>
                                <td class="small">{{ $inv->period_start->translatedFormat('M Y') }}</td>
                                <td class="small">{{ $inv->due_date->translatedFormat('d M Y') }}</td>
                                <td class="text-end text-currency">Rp {{ number_format($inv->amount_idr, 0, ',', '.') }}</td>
                                <td><span class="status-pill status-{{ $inv->status }}">{{ $inv->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada invoice.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div></div>
    </div>
</div>
@endsection
