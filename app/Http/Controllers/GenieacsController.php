<?php

namespace App\Http\Controllers;

use App\Models\GenieacsServer;
use App\Services\GenieacsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenieacsController extends Controller
{
    public function index()
    {
        $servers = GenieacsServer::orderBy('name')->paginate(20);

        return view('genieacs.index', compact('servers'));
    }

    public function create()
    {
        return view('genieacs.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->ensureSingleDefault($data);
        GenieacsServer::create($data);

        return redirect()->route('genieacs.index')->with('success', 'Server GenieACS dibuat.');
    }

    public function edit(GenieacsServer $genieacs)
    {
        return view('genieacs.edit', ['server' => $genieacs]);
    }

    public function update(Request $request, GenieacsServer $genieacs)
    {
        $data = $this->validateData($request, $genieacs->id);
        if (empty($data['auth_password'])) {
            unset($data['auth_password']);
        }
        $this->ensureSingleDefault($data, $genieacs->id);
        $genieacs->update($data);

        return redirect()->route('genieacs.index')->with('success', 'Server GenieACS diperbarui.');
    }

    public function destroy(GenieacsServer $genieacs)
    {
        $genieacs->delete();

        return redirect()->route('genieacs.index')->with('success', 'Server GenieACS dihapus.');
    }

    public function test(GenieacsServer $genieacs)
    {
        $result = (new GenieacsService($genieacs))->testConnection();

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function devices(Request $request, GenieacsServer $genieacs)
    {
        $serial = trim((string) $request->query('serial', ''));
        $devices = (new GenieacsService($genieacs))->listDevices($serial !== '' ? $serial : null, 100);

        return view('genieacs.devices', [
            'server' => $genieacs,
            'devices' => $devices,
            'serial' => $serial,
        ]);
    }

    public function deviceShow(GenieacsServer $genieacs, string $deviceId)
    {
        $device = (new GenieacsService($genieacs))->getDevice($deviceId);
        if (! $device) {
            return redirect()->route('genieacs.devices', $genieacs)
                ->with('error', 'Perangkat tidak ditemukan atau server tidak merespons.');
        }

        return view('genieacs.device_show', [
            'server' => $genieacs,
            'deviceId' => $deviceId,
            'device' => $device,
        ]);
    }

    public function deviceReboot(GenieacsServer $genieacs, string $deviceId)
    {
        $ok = (new GenieacsService($genieacs))->reboot($deviceId);

        return back()->with($ok ? 'success' : 'error', $ok ? 'Perintah reboot dikirim.' : 'Gagal mengirim perintah reboot.');
    }

    public function deviceRefresh(GenieacsServer $genieacs, string $deviceId)
    {
        $ok = (new GenieacsService($genieacs))->refresh($deviceId);

        return back()->with($ok ? 'success' : 'error', $ok ? 'Perintah refresh dikirim.' : 'Gagal mengirim perintah refresh.');
    }

    protected function validateData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'nbi_url' => ['required', 'url', 'max:255'],
            'auth_user' => ['nullable', 'string', 'max:120'],
            'auth_password' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    protected function ensureSingleDefault(array &$data, ?int $ignoreId = null): void
    {
        if (! ($data['is_default'] ?? false)) {
            return;
        }
        DB::table('genieacs_servers')
            ->where('tenant_id', app('current_tenant_id'))
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->update(['is_default' => false]);
    }
}
