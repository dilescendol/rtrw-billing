@extends('layouts.superadmin')
@section('title', 'Tenants')
@section('content')
<h3 class="mb-3">Tenant Operator</h3>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card"><div class="card-body">
        <div class="text-muted small">Total Tenant</div>
        <h3>{{ $stats['tenants_total'] }}</h3>
    </div></div></div>
    <div class="col-md-3"><div class="card border-warning"><div class="card-body">
        <div class="text-muted small">Trial</div>
        <h3>{{ $stats['tenants_trial'] }}</h3>
    </div></div></div>
    <div class="col-md-3"><div class="card border-success"><div class="card-body">
        <div class="text-muted small">Aktif</div>
        <h3>{{ $stats['tenants_active'] }}</h3>
    </div></div></div>
    <div class="col-md-3"><div class="card border-danger"><div class="card-body">
        <div class="text-muted small">Suspended</div>
        <h3>{{ $stats['tenants_suspended'] }}</h3>
    </div></div></div>
</div>

<form method="GET" class="mb-3">
    <div class="input-group" style="max-width: 320px;">
        <input type="text" name="q" class="form-control" placeholder="Cari nama / slug…" value="{{ $q }}">
        <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead><tr>
                <th>Bisnis</th><th>Owner</th><th>Plan</th><th>Status</th>
                <th class="text-end">Pelanggan</th><th class="text-end">Invoice</th>
                <th>Trial</th><th>Plan Berakhir</th>
            </tr></thead>
            <tbody>
            @forelse($tenants as $t)
                <tr>
                    <td>
                        <strong>{{ $t->business_name ?: $t->name }}</strong>
                        <div class="small text-muted">{{ $t->slug }}</div>
                    </td>
                    <td>
                        {{ $t->owner?->name ?: '-' }}
                        <div class="small text-muted">{{ $t->owner?->email }}</div>
                    </td>
                    <td>{{ $t->plan?->name ?: '-' }}</td>
                    <td>
                        @if($t->status === 'active')<span class="badge bg-success">Aktif</span>
                        @elseif($t->status === 'trial')<span class="badge bg-warning">Trial</span>
                        @else<span class="badge bg-danger">Suspended</span>@endif
                    </td>
                    <td class="text-end">{{ $t->customers_count }}</td>
                    <td class="text-end">{{ $t->invoices_count }}</td>
                    <td>{{ optional($t->trial_ends_at)->format('d M Y') ?: '-' }}</td>
                    <td>{{ optional($t->plan_ends_at)->format('d M Y') ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada tenant.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $tenants->links() }}</div>
@endsection
