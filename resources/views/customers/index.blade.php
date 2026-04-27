@extends('layouts.app')
@section('title', 'Pelanggan')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Pelanggan</h3>
    <a href="{{ route('customers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah</a>
</div>
<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Cari nama / kode / nomor HP">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="active" @selected($status==='active')>Aktif</option>
                    <option value="isolated" @selected($status==='isolated')>Diisolir</option>
                    <option value="terminated" @selected($status==='terminated')>Berhenti</option>
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-outline-secondary w-100"><i class="bi bi-search"></i> Cari</button></div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Kode</th><th>Nama</th><th>Paket</th><th>Status</th><th>Telp</th><th></th></tr></thead>
                <tbody>
                    @forelse($customers as $c)
                        <tr>
                            <td>{{ $c->code }}</td>
                            <td><a class="text-decoration-none" href="{{ route('customers.show', $c) }}">{{ $c->name }}</a><div class="small text-muted">{{ Str::limit($c->address, 50) }}</div></td>
                            <td>{{ $c->package?->name ?? '-' }}<div class="small text-muted">@if($c->package)Rp {{ number_format($c->package->price_idr, 0, ',', '.') }}@endif</div></td>
                            <td><span class="status-pill status-{{ $c->status }}">{{ $c->status }}</span></td>
                            <td class="small">{{ $c->phone }}</td>
                            <td class="text-end">
                                <a href="{{ route('customers.edit', $c) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada pelanggan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $customers->links() }}
    </div>
</div>
@endsection
