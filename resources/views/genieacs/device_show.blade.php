@extends('layouts.app')
@section('title', 'GenieACS Device')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0">Device · {{ $deviceId }}</h3>
        <div class="small text-muted">{{ $server->name }} · {{ $server->nbi_url }}</div>
    </div>
    <div>
        <form method="POST" action="{{ route('genieacs.devices.refresh', ['genieacs' => $server, 'deviceId' => $deviceId]) }}" class="d-inline">@csrf
            <button class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</button>
        </form>
        <form method="POST" action="{{ route('genieacs.devices.reboot', ['genieacs' => $server, 'deviceId' => $deviceId]) }}" class="d-inline" onsubmit="return confirm('Reboot device sekarang?')">@csrf
            <button class="btn btn-outline-warning"><i class="bi bi-power me-1"></i>Reboot</button>
        </form>
        <a href="{{ route('genieacs.devices', $server) }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="card">
    <div class="card-body">
        <div class="small text-muted mb-2">Raw payload (dipotong jika besar)</div>
        <pre class="small" style="max-height:600px;overflow:auto;background:rgba(0,0,0,.04);padding:12px;border-radius:6px;">{{ json_encode($device, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
    </div>
</div>
@endsection
