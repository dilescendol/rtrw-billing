@extends('layouts.app')
@section('title', 'Paket Internet')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Paket Internet</h3>
    <a href="{{ route('packages.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Paket</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Nama</th><th>Speed</th><th>Profile MikroTik</th><th class="text-end">Harga</th><th>Pelanggan</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($packages as $p)
                    <tr>
                        <td><strong>{{ $p->name }}</strong><div class="small text-muted">{{ $p->description }}</div></td>
                        <td>{{ $p->speed_mbps ? $p->speed_mbps.' Mbps' : '-' }}</td>
                        <td><code>{{ $p->mikrotik_profile ?? '-' }}</code></td>
                        <td class="text-end text-currency">Rp {{ number_format($p->price_idr, 0, ',', '.') }}</td>
                        <td>{{ $p->customers_count }}</td>
                        <td>{!! $p->is_active ? '<span class="status-pill status-active">aktif</span>' : '<span class="status-pill status-suspended">nonaktif</span>' !!}</td>
                        <td class="text-end">
                            <a href="{{ route('packages.edit', $p) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('packages.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Hapus paket ini?')">
                                @csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada paket.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
