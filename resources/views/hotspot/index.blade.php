@extends('layouts.app')
@section('title', 'Hotspot Users')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Hotspot Users</h3>
    <a href="{{ route('hotspot.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah User</a>
</div>

<div class="card">
    <div class="card-body">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Cari username / MAC">
            </div>
            <div class="col-md-2"><button class="btn btn-outline-secondary w-100"><i class="bi bi-search"></i> Cari</button></div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>NAS</th>
                        <th>Profile</th>
                        <th>MAC</th>
                        <th>Expired</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td>
                                <div>{{ $u->username }}</div>
                                <div class="small text-muted">{{ $u->comment }}</div>
                            </td>
                            <td class="small">{{ $u->nasDevice?->name ?? '-' }}</td>
                            <td class="small">{{ $u->profile ?? $u->package?->mikrotik_profile ?? '-' }}</td>
                            <td class="small">{{ $u->mac_address ?? '-' }}</td>
                            <td class="small">{{ $u->expires_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td><span class="badge bg-{{ $u->statusBadge() }}">{{ $u->status }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('hotspot.edit', $u) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('hotspot.destroy', $u) }}" class="d-inline" onsubmit="return confirm('Hapus user ini?')">@csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada user hotspot.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </div>
</div>
@endsection
