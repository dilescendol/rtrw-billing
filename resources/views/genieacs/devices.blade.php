@extends('layouts.app')
@section('title', 'GenieACS Devices')
@section('content')
@php
    function gv($d, $path) {
        $cur = $d;
        foreach (explode('.', $path) as $seg) {
            if (! is_array($cur) || ! array_key_exists($seg, $cur)) return null;
            $cur = $cur[$seg];
        }
        return is_array($cur) ? ($cur['_value'] ?? null) : $cur;
    }
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0">Devices · {{ $server->name }}</h3>
        <div class="small text-muted">{{ $server->nbi_url }}</div>
    </div>
    <a href="{{ route('genieacs.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2">
            <div class="col-md-6"><input type="search" name="serial" value="{{ $serial }}" class="form-control" placeholder="Cari serial number"></div>
            <div class="col-md-2"><button class="btn btn-outline-secondary w-100"><i class="bi bi-search"></i> Cari</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>ID</th><th>Serial</th><th>Software</th><th>LAN IP</th><th>Last Inform</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    @forelse($devices as $d)
                        @php
                            $id = $d['_id'] ?? '?';
                            $serial = gv($d, 'InternetGatewayDevice.DeviceInfo.SerialNumber');
                            $sw = gv($d, 'InternetGatewayDevice.DeviceInfo.SoftwareVersion');
                            $ip = gv($d, 'InternetGatewayDevice.LANDevice.1.LANHostConfigManagement.IPInterface.1.IPInterfaceIPAddress');
                            $li = $d['_lastInform'] ?? null;
                        @endphp
                        <tr>
                            <td class="small"><code>{{ $id }}</code></td>
                            <td class="small">{{ $serial ?? '-' }}</td>
                            <td class="small">{{ $sw ?? '-' }}</td>
                            <td class="small">{{ $ip ?? '-' }}</td>
                            <td class="small">{{ $li ? \Carbon\Carbon::parse($li)->diffForHumans() : '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('genieacs.devices.show', ['genieacs' => $server, 'deviceId' => $id]) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada perangkat atau server tidak merespons.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
