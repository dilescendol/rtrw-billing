@extends('layouts.app')
@section('title', 'WhatsApp Templates')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">WhatsApp Templates</h1>
    <a href="{{ route('whatsapp.logs.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-list-ul"></i> Lihat Log
    </a>
</div>
<p class="text-muted small">Pesan dikirim otomatis lewat Fonnte. Placeholder yang tersedia:
    <code>@{{nama}}</code>, <code>@{{kode}}</code>, <code>@{{nomor_invoice}}</code>,
    <code>@{{jumlah}}</code>, <code>@{{jatuh_tempo}}</code>, <code>@{{link_bayar}}</code>.
</p>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>Event</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @foreach(\App\Models\WhatsappTemplate::EVENTS as $event)
                @php($t = $templates[$event] ?? null)
                <tr>
                    <td>
                        <strong>{{ \App\Models\WhatsappTemplate::eventLabel($event) }}</strong>
                        <div class="text-muted small">{{ $event }}</div>
                    </td>
                    <td>
                        @if(! $t)
                            <span class="badge bg-secondary">Default bawaan</span>
                        @elseif($t->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-warning text-dark">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('whatsapp.templates.edit', $event) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
