@extends('layouts.app')
@section('title', 'WhatsApp Log')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">WhatsApp Log</h1>
    <a href="{{ route('whatsapp.templates.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-chat-text"></i> Templates
    </a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-sm-4">
        <select name="event" class="form-select form-select-sm">
            <option value="">— semua event —</option>
            @foreach(\App\Models\WhatsappTemplate::EVENTS as $ev)
                <option value="{{ $ev }}" @selected($event === $ev)>{{ \App\Models\WhatsappTemplate::eventLabel($ev) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-3">
        <select name="status" class="form-select form-select-sm">
            <option value="">— semua status —</option>
            <option value="success" @selected($status === 'success')>Sukses</option>
            <option value="failed" @selected($status === 'failed')>Gagal</option>
        </select>
    </div>
    <div class="col-sm-2">
        <button class="btn btn-sm btn-secondary w-100"><i class="bi bi-funnel"></i> Filter</button>
    </div>
</form>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0 small">
            <thead><tr>
                <th>Waktu</th><th>Event</th><th>Tujuan</th><th>Pelanggan</th><th>Status</th><th>Pesan</th>
            </tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->sent_at?->format('d M Y H:i') ?? '-' }}</td>
                    <td><span class="badge bg-light text-dark">{{ $log->event ?? '-' }}</span></td>
                    <td><code>{{ $log->phone }}</code></td>
                    <td>{{ $log->customer?->name ?? '-' }}</td>
                    <td>
                        @if($log->success)
                            <span class="badge bg-success">Sukses</span>
                        @else
                            <span class="badge bg-danger">Gagal</span>
                        @endif
                    </td>
                    <td style="max-width: 360px"><div class="text-truncate" title="{{ $log->message }}">{{ $log->message }}</div></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada log.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $logs->links() }}</div>
@endsection
