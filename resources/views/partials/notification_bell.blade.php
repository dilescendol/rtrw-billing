@php($unread = auth()->check() ? auth()->user()->unreadNotifications->take(8) : collect())
@php($unreadCount = auth()->check() ? auth()->user()->unreadNotifications->count() : 0)
<div class="dropdown">
    <button class="btn btn-light btn-sm position-relative" data-bs-toggle="dropdown" id="notification-bell" aria-label="Notifikasi">
        <i class="bi bi-bell"></i>
        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: .65rem;">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                <span class="visually-hidden">notifikasi belum dibaca</span>
            </span>
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 320px; max-width: 360px;">
        <div class="px-3 py-2 d-flex justify-content-between align-items-center border-bottom">
            <strong>Notifikasi</strong>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read_all') }}" class="m-0">@csrf
                    <button class="btn btn-sm btn-link p-0">Tandai semua dibaca</button>
                </form>
            @endif
        </div>
        <div style="max-height: 360px; overflow-y: auto;">
            @forelse($unread as $n)
                @php($d = $n->data)
                <form method="POST" action="{{ route('notifications.read', $n->id) }}" class="m-0">@csrf
                    <button type="submit" class="dropdown-item d-flex align-items-start gap-2 py-2">
                        <span class="text-{{ $d['level'] ?? 'info' }} fs-5 lh-1"><i class="bi bi-{{ $d['icon'] ?? 'bell' }}"></i></span>
                        <span class="flex-grow-1 text-wrap">
                            <span class="fw-semibold d-block">{{ $d['title'] ?? 'Notifikasi' }}</span>
                            <span class="text-muted small d-block">{{ \Illuminate\Support\Str::limit($d['message'] ?? '', 80) }}</span>
                            <span class="text-muted small">{{ $n->created_at->diffForHumans() }}</span>
                        </span>
                    </button>
                </form>
            @empty
                <div class="text-center text-muted py-4 small">Tidak ada notifikasi baru.</div>
            @endforelse
        </div>
        <div class="px-3 py-2 border-top text-center">
            <a href="{{ route('notifications.index') }}" class="small">Lihat semua</a>
        </div>
    </div>
</div>
