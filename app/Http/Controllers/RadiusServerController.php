<?php

namespace App\Http\Controllers;

use App\Models\RadiusServer;
use App\Services\RadiusManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RadiusServerController extends Controller
{
    public function index()
    {
        $servers = RadiusServer::orderBy('name')->paginate(20);

        return view('radius.index', compact('servers'));
    }

    public function create()
    {
        return view('radius.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->ensureSingleDefault($data);
        RadiusServer::create($data);

        return redirect()->route('radius.index')->with('success', 'Server RADIUS dibuat.');
    }

    public function edit(RadiusServer $radius)
    {
        return view('radius.edit', ['server' => $radius]);
    }

    public function update(Request $request, RadiusServer $radius)
    {
        $data = $this->validateData($request, $radius->id);
        foreach (['shared_secret', 'sql_password'] as $k) {
            if (empty($data[$k])) {
                unset($data[$k]);
            }
        }
        $this->ensureSingleDefault($data, $radius->id);
        $radius->update($data);

        return redirect()->route('radius.index')->with('success', 'Server RADIUS diperbarui.');
    }

    public function destroy(RadiusServer $radius)
    {
        $radius->delete();

        return redirect()->route('radius.index')->with('success', 'Server RADIUS dihapus.');
    }

    public function test(RadiusServer $radius)
    {
        $result = (new RadiusManager($radius))->testConnection();

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    protected function validateData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'host' => ['required', 'string', 'max:255'],
            'auth_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'acct_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'shared_secret' => ['nullable', 'string', 'max:255'],
            'sql_driver' => ['required', 'in:mysql,pgsql,sqlite,sqlsrv'],
            'sql_host' => ['nullable', 'string', 'max:255'],
            'sql_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'sql_database' => ['nullable', 'string', 'max:120'],
            'sql_username' => ['nullable', 'string', 'max:120'],
            'sql_password' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['auth_port'] = $data['auth_port'] ?: 1812;
        $data['acct_port'] = $data['acct_port'] ?: 1813;
        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    protected function ensureSingleDefault(array &$data, ?int $ignoreId = null): void
    {
        if (! ($data['is_default'] ?? false)) {
            return;
        }
        DB::table('radius_servers')
            ->where('tenant_id', app('current_tenant_id'))
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->update(['is_default' => false]);
    }
}
