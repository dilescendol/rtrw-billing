@extends('layouts.app')
@section('title', 'NAS / Router')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">NAS / Router</h3>
    <a href="{{ route('nas.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah NAS</a>
</div>

<div class="card">
    <div class="card-body">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Host</th>
                        <th>Identity</th>
                        <th>Type</th>
                        <th>Status terakhir</th>
                        <th>Default</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $d)
                        <tr>
                            <td>
                                <div>{{ $d->name }}</div>
                                <div class="small text-muted">{{ $d->api_user }}@{{ $d->host }}:{{ $d->api_port }}</div>
                            </td>
                            <td class="small">{{ $d->host }}</td>
                            <td class="small">{{ $d->identity ?? '-' }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($d->type) }}</span></td>
                            <td>
                                <span class="badge bg-{{ $d->statusBadge() }}">{{ $d->last_status ?? 'unknown' }}</span>
                                @if($d->last_seen_at)
                                    <div class="small text-muted">{{ $d->last_seen_at->diffForHumans() }}</div>
                                @endif
                            </td>
                            <td>@if($d->is_default)<span class="badge bg-primary">Default</span>@endif</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('nas.test', $d) }}" class="d-inline">@csrf
                                    <button class="btn btn-sm btn-outline-secondary" title="Tes koneksi"><i class="bi bi-plug"></i></button>
                                </form>
                                <a href="{{ route('nas.edit', $d) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('nas.destroy', $d) }}" class="d-inline" onsubmit="return confirm('Hapus NAS ini?')">@csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada perangkat NAS. Tambahkan satu untuk mulai sinkron PPPoE/Hotspot.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $devices->links() }}
    </div>
</div>
@endsection
