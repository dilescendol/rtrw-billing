@extends('layouts.app')
@section('title', 'Voucher Hotspot')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Voucher Hotspot</h3>
    <a href="{{ route('vouchers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Generate Batch</a>
</div>

<div class="row g-3 mb-3">
    @foreach($batches as $b)
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body py-3">
                    <div class="small text-muted">Batch</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>{{ $b->batch_code }}</strong>
                        <span class="badge bg-secondary">{{ $b->total }} kupon</span>
                    </div>
                    <div class="small text-muted mt-1">{{ \Carbon\Carbon::parse($b->created_at)->diffForHumans() }}</div>
                    <div class="mt-2 d-flex gap-2">
                        <a href="{{ route('vouchers.index', ['batch' => $b->batch_code]) }}" class="btn btn-sm btn-light">Lihat</a>
                        <a href="{{ route('vouchers.print', ['batch' => $b->batch_code]) }}" class="btn btn-sm btn-outline-primary" target="_blank">Print</a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-body">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="search" name="batch" value="{{ $batch }}" class="form-control" placeholder="Filter batch">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="available" @selected($status==='available')>Tersedia</option>
                    <option value="sold" @selected($status==='sold')>Terjual</option>
                    <option value="used" @selected($status==='used')>Terpakai</option>
                    <option value="expired" @selected($status==='expired')>Expired</option>
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-outline-secondary w-100"><i class="bi bi-funnel"></i> Filter</button></div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Batch</th>
                        <th>Paket</th>
                        <th class="text-end">Harga</th>
                        <th>Profile</th>
                        <th>Durasi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $v)
                        <tr>
                            <td><code>{{ $v->code }}</code></td>
                            <td class="small">{{ $v->batch_code }}</td>
                            <td>{{ $v->package?->name ?? '-' }}</td>
                            <td class="text-end">Rp {{ number_format($v->price_idr, 0, ',', '.') }}</td>
                            <td class="small">{{ $v->profile ?? '-' }}</td>
                            <td class="small">{{ $v->duration_minutes ? $v->duration_minutes.' menit' : '-' }}</td>
                            <td><span class="badge bg-{{ $v->statusBadge() }}">{{ $v->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada voucher.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $vouchers->links() }}
    </div>
</div>
@endsection
