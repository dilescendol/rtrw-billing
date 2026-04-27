<?php

namespace App\Http\Controllers;

use App\Services\FonnteService;
use App\Services\MikrotikService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $tenant = app('current_tenant');

        return view('settings.index', compact('tenant'));
    }

    public function updateBusiness(Request $request)
    {
        $tenant = app('current_tenant');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'business_phone' => ['nullable', 'string', 'max:32'],
            'business_address' => ['nullable', 'string'],
            'invoice_prefix' => ['nullable', 'string', 'max:16'],
            'default_due_day' => ['required', 'integer', 'min:1', 'max:28'],
        ]);
        $tenant->update($data);

        return back()->with('success', 'Pengaturan bisnis disimpan.');
    }

    public function updatePakasir(Request $request)
    {
        $tenant = app('current_tenant');
        $data = $request->validate([
            'pakasir_project' => ['nullable', 'string', 'max:255'],
            'pakasir_api_key' => ['nullable', 'string', 'max:255'],
            'pakasir_signature' => ['nullable', 'string', 'max:255'],
            'pakasir_enabled' => ['nullable', 'boolean'],
        ]);
        $data['pakasir_enabled'] = $request->boolean('pakasir_enabled');
        // Don't overwrite existing keys with empty strings
        foreach (['pakasir_api_key', 'pakasir_signature'] as $k) {
            if (empty($data[$k])) {
                unset($data[$k]);
            }
        }
        $tenant->update($data);

        return back()->with('success', 'Pengaturan Pakasir disimpan.');
    }

    public function updateMikrotik(Request $request)
    {
        $tenant = app('current_tenant');
        $data = $request->validate([
            'mikrotik_host' => ['nullable', 'string', 'max:255'],
            'mikrotik_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mikrotik_user' => ['nullable', 'string', 'max:255'],
            'mikrotik_password' => ['nullable', 'string', 'max:255'],
            'mikrotik_enabled' => ['nullable', 'boolean'],
        ]);
        $data['mikrotik_enabled'] = $request->boolean('mikrotik_enabled');
        if (empty($data['mikrotik_password'])) {
            unset($data['mikrotik_password']);
        }
        $tenant->update($data);

        return back()->with('success', 'Pengaturan MikroTik disimpan.');
    }

    public function testMikrotik()
    {
        $tenant = app('current_tenant');
        $result = (new MikrotikService($tenant))->testConnection();

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function updateFonnte(Request $request)
    {
        $tenant = app('current_tenant');
        $data = $request->validate([
            'fonnte_token' => ['nullable', 'string', 'max:255'],
            'fonnte_enabled' => ['nullable', 'boolean'],
        ]);
        $data['fonnte_enabled'] = $request->boolean('fonnte_enabled');
        if (empty($data['fonnte_token'])) {
            unset($data['fonnte_token']);
        }
        $tenant->update($data);

        return back()->with('success', 'Pengaturan WhatsApp disimpan.');
    }

    public function testFonnte(Request $request)
    {
        $tenant = app('current_tenant');
        $request->validate(['target' => ['required', 'string']]);
        $ok = (new FonnteService($tenant))->send($request->input('target'), 'Tes notifikasi dari '.$tenant->business_name);

        return back()->with($ok ? 'success' : 'error', $ok ? 'Pesan WA terkirim.' : 'Gagal mengirim pesan WA.');
    }
}
