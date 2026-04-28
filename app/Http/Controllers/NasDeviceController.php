<?php

namespace App\Http\Controllers;

use App\Models\NasDevice;
use App\Services\NasDeviceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NasDeviceController extends Controller
{
    public function index()
    {
        $devices = NasDevice::orderBy('name')->paginate(20);

        return view('nas.index', compact('devices'));
    }

    public function create()
    {
        return view('nas.create');
    }

    public function store(Request $request)
    {
        $tenant = app('current_tenant');
        $plan = $tenant->plan;
        if ($plan && $plan->max_nas > 0 && NasDevice::count() >= $plan->max_nas) {
            return back()
                ->withErrors(['name' => 'Batas NAS untuk paket Anda tercapai ('.$plan->max_nas.'). Silakan upgrade.'])
                ->withInput();
        }

        $data = $this->validateData($request);
        $this->ensureSingleDefault($data);

        NasDevice::create($data);

        return redirect()->route('nas.index')->with('success', 'NAS device dibuat.');
    }

    public function edit(NasDevice $nas)
    {
        return view('nas.edit', ['device' => $nas]);
    }

    public function update(Request $request, NasDevice $nas)
    {
        $data = $this->validateData($request, $nas->id);
        if (empty($data['api_password'])) {
            unset($data['api_password']);
        }
        $this->ensureSingleDefault($data, $nas->id);
        $nas->update($data);

        return redirect()->route('nas.index')->with('success', 'NAS device diperbarui.');
    }

    public function destroy(NasDevice $nas)
    {
        $nas->delete();

        return redirect()->route('nas.index')->with('success', 'NAS device dihapus.');
    }

    public function test(NasDevice $nas)
    {
        $result = (new NasDeviceService($nas))->testConnection();

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    protected function validateData(Request $request, ?int $ignoreId = null): array
    {
        // On create the password column is NOT NULL — require it. On update,
        // empty means "keep current" and the controller unsets it.
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'host' => ['required', 'string', 'max:255'],
            'api_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'api_user' => ['required', 'string', 'max:120'],
            'api_password' => [$ignoreId ? 'nullable' : 'required', 'string', 'max:255'],
            'identity' => ['nullable', 'string', 'max:120'],
            'type' => ['required', 'in:mikrotik,other'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['api_port'] = $data['api_port'] ?: 8728;
        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    protected function ensureSingleDefault(array &$data, ?int $ignoreId = null): void
    {
        if (! ($data['is_default'] ?? false)) {
            return;
        }
        DB::table('nas_devices')
            ->where('tenant_id', app('current_tenant_id'))
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->update(['is_default' => false]);
    }
}
