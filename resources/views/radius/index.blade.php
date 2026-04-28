@extends('layouts.app')
@section('title', 'RADIUS Servers')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">RADIUS Servers</h3>
    <a href="{{ route('radius.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Server</a>
</div>
<div class="card">
    <div class="card-body">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="alert alert-info small">
            Konfigurasi koneksi ke FreeRADIUS (mode SQL backend, tabel <code>radcheck</code>/<code>radreply</code>/<code>radusergroup</code>/<code>radgroupreply</code>/<code>nas</code>).
            Saat customer disimpan/diubah dan plan mengaktifkan RADIUS, data akan didorong ke server default.
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Host</th>
                        <th>SQL Backend</th>
                        <th>Status</th>
                        <th>Default</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($servers as $s)
                        <tr>
                            <td>{{ $s->name }}</td>
                            <td class="small">{{ $s->host }} <span class="text-muted">·</span> auth {{ $s->auth_port }} / acct {{ $s->acct_port }}</td>
                            <td class="small">{{ $s->sql_driver }}://{{ $s->sql_username }}@{{ $s->sql_host ?? $s->host }}/{{ $s->sql_database ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $s->statusBadge() }}">{{ $s->last_status ?? 'unknown' }}</span>
                                @if($s->last_seen_at)<div class="small text-muted">{{ $s->last_seen_at->diffForHumans() }}</div>@endif
                            </td>
                            <td>@if($s->is_default)<span class="badge bg-primary">Default</span>@endif</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('radius.test', $s) }}" class="d-inline">@csrf
                                    <button class="btn btn-sm btn-outline-secondary" title="Tes koneksi"><i class="bi bi-plug"></i></button>
                                </form>
                                <a href="{{ route('radius.edit', $s) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('radius.destroy', $s) }}" class="d-inline" onsubmit="return confirm('Hapus server ini?')">@csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada server RADIUS.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $servers->links() }}
    </div>
</div>
@endsection
