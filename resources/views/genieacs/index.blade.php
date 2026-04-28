@extends('layouts.app')
@section('title', 'GenieACS Servers')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">GenieACS / TR-069</h3>
    <a href="{{ route('genieacs.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Server</a>
</div>
<div class="card">
    <div class="card-body">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="alert alert-info small">
            Endpoint <strong>NBI</strong> GenieACS (default port 7557). Kredensial Basic Auth opsional. Setelah tersimpan, klik <em>Devices</em> untuk daftar perangkat ONT/CPE.
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Nama</th><th>NBI URL</th><th>Auth</th><th>Status</th><th>Default</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    @forelse($servers as $s)
                        <tr>
                            <td>{{ $s->name }}</td>
                            <td class="small"><code>{{ $s->nbi_url }}</code></td>
                            <td class="small">{{ $s->auth_user ? 'Basic ('.$s->auth_user.')' : 'Tanpa auth' }}</td>
                            <td>
                                <span class="badge bg-{{ $s->statusBadge() }}">{{ $s->last_status ?? 'unknown' }}</span>
                                @if($s->last_seen_at)<div class="small text-muted">{{ $s->last_seen_at->diffForHumans() }}</div>@endif
                            </td>
                            <td>@if($s->is_default)<span class="badge bg-primary">Default</span>@endif</td>
                            <td class="text-end">
                                <a href="{{ route('genieacs.devices', $s) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-hdd-network"></i> Devices</a>
                                <form method="POST" action="{{ route('genieacs.test', $s) }}" class="d-inline">@csrf
                                    <button class="btn btn-sm btn-outline-secondary" title="Tes"><i class="bi bi-plug"></i></button>
                                </form>
                                <a href="{{ route('genieacs.edit', $s) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('genieacs.destroy', $s) }}" class="d-inline" onsubmit="return confirm('Hapus server ini?')">@csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada server GenieACS.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $servers->links() }}
    </div>
</div>
@endsection
