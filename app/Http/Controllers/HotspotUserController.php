<?php

namespace App\Http\Controllers;

use App\Models\HotspotUser;
use App\Models\NasDevice;
use App\Models\Package;
use App\Services\NasDeviceService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HotspotUserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $users = HotspotUser::with(['package', 'nasDevice'])
            ->when($q !== '', fn ($qb) => $qb->where(function ($w) use ($q) {
                $w->where('username', 'like', "%{$q}%")
                    ->orWhere('mac_address', 'like', "%{$q}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('hotspot.index', compact('users', 'q'));
    }

    public function create()
    {
        $packages = Package::where('is_active', true)
            ->where('type', Package::TYPE_HOTSPOT)
            ->orderBy('name')->get();
        $nasDevices = NasDevice::where('is_active', true)->orderBy('name')->get();

        return view('hotspot.create', compact('packages', 'nasDevices'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if (empty($data['password'])) {
            $data['password'] = Str::lower(Str::random(8));
        }
        $hotspotUser = HotspotUser::create($data);

        $this->push($hotspotUser);

        return redirect()->route('hotspot.index')->with('success', 'Hotspot user dibuat.');
    }

    public function edit(HotspotUser $hotspot)
    {
        $packages = Package::where('is_active', true)
            ->where('type', Package::TYPE_HOTSPOT)
            ->orderBy('name')->get();
        $nasDevices = NasDevice::where('is_active', true)->orderBy('name')->get();

        return view('hotspot.edit', ['hotspotUser' => $hotspot, 'packages' => $packages, 'nasDevices' => $nasDevices]);
    }

    public function update(Request $request, HotspotUser $hotspot)
    {
        $data = $this->validateData($request, $hotspot->id);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $hotspot->update($data);
        $this->push($hotspot->refresh());

        return redirect()->route('hotspot.index')->with('success', 'Hotspot user diperbarui.');
    }

    public function destroy(HotspotUser $hotspot)
    {
        if ($hotspot->nasDevice) {
            (new NasDeviceService($hotspot->nasDevice))->removeHotspotUser($hotspot->username);
        }
        $hotspot->delete();

        return redirect()->route('hotspot.index')->with('success', 'Hotspot user dihapus.');
    }

    protected function validateData(Request $request, ?int $ignoreId = null): array
    {
        $tenantId = app('current_tenant_id');

        return $request->validate([
            'nas_device_id' => ['nullable', "exists:nas_devices,id,tenant_id,{$tenantId}"],
            'package_id' => ['nullable', "exists:packages,id,tenant_id,{$tenantId}"],
            'username' => ['required', 'string', 'max:64', "unique:hotspot_users,username,{$ignoreId},id,tenant_id,{$tenantId}"],
            'password' => ['nullable', 'string', 'max:64'],
            'mac_address' => ['nullable', 'string', 'max:32'],
            'profile' => ['nullable', 'string', 'max:64'],
            'expires_at' => ['nullable', 'date'],
            'status' => ['required', 'in:active,disabled,expired'],
            'comment' => ['nullable', 'string', 'max:255'],
        ]);
    }

    protected function push(HotspotUser $user): void
    {
        if (! $user->nasDevice) {
            return;
        }
        $service = new NasDeviceService($user->nasDevice);
        if ($user->status === HotspotUser::STATUS_ACTIVE) {
            $service->upsertHotspotUser(
                $user->username,
                $user->password,
                $user->profile ?: ($user->package?->mikrotik_profile),
                $user->mac_address,
            );
        } else {
            $service->removeHotspotUser($user->username);
        }
    }
}
