@extends('layouts.app')
@section('title', 'Notifikasi')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Notifikasi</h3>
    @if(auth()->user()->unreadNotifications->isNotEmpty())
        <form method="POST" action="{{ route('notifications.read_all') }}">@csrf
            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-check2-all me-1"></i>Tandai semua dibaca</button>
        </form>
    @endif
</div>

<div class="card">
    <ul class="list-group list-group-flush">
        @forelse($notifications as $n)
            @php($d = $n->data)
            <li class="list-group-item d-flex align-items-start gap-3 {{ $n->read_at ? '' : 'bg-light' }}">
                <span class="text-{{ $d['level'] ?? 'info' }} fs-4"><i class="bi bi-{{ $d['icon'] ?? 'bell' }}"></i></span>
                <div class="flex-grow-1">
                    <div class="fw-semibold">{{ $d['title'] ?? 'Notifikasi' }}</div>
                    <div class="text-muted small">{{ $d['message'] ?? '' }}</div>
                    <div class="text-muted small mt-1">{{ $n->created_at->diffForHumans() }}</div>
                </div>
                @if(! $n->read_at)
                    <form method="POST" action="{{ route('notifications.read', $n->id) }}">@csrf
                        <button class="btn btn-sm btn-link p-0">Tandai dibaca</button>
                    </form>
                @endif
            </li>
        @empty
            <li class="list-group-item text-center text-muted py-5">Belum ada notifikasi.</li>
        @endforelse
    </ul>
</div>

<div class="mt-3">{{ $notifications->links() }}</div>
@endsection
