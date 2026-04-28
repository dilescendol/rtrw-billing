<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Package;
use App\Models\RadiusServer;
use App\Models\Tenant;
use App\Services\MikrotikService;
use App\Services\RadiusManager;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $status = $request->string('status')->toString();

        $customers = Customer::with('package')
            ->when($q, fn ($qb) => $qb->where(function ($x) use ($q) {
                $x->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            }))
            ->when($status, fn ($qb) => $qb->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers', 'q', 'status'));
    }

    public function create()
    {
        $packages = Package::where('is_active', true)->orderBy('name')->get();

        return view('customers.create', compact('packages'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $tenant = app('current_tenant');

        // Plan limit enforcement — count only non-terminated customers.
        $plan = $tenant->plan;
        if ($plan && $plan->max_customers > 0) {
            $active = Customer::where('status', '!=', Customer::STATUS_TERMINATED)->count();
            if ($active >= $plan->max_customers) {
                return back()->withErrors(['package_id' => 'Batas pelanggan paket Anda tercapai. Silakan upgrade.'])->withInput();
            }
        }

        $data['code'] = ($data['code'] ?? null) ?: $this->nextCode($tenant->id);
        $customer = Customer::create($data);

        if ($tenant->mikrotik_enabled) {
            (new MikrotikService($tenant))->createPppoeUser($customer);
        }

        $this->pushToRadius($customer, $tenant);

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(Customer $customer)
    {
        $customer->load('package', 'invoices.payments');

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $packages = Package::where('is_active', true)->orderBy('name')->get();

        return view('customers.edit', compact('customer', 'packages'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $this->validateData($request, $customer->id);
        $previousStatus = $customer->status;
        $customer->update($data);

        if ($previousStatus !== $customer->status) {
            $tenant = app('current_tenant');
            if ($tenant->mikrotik_enabled) {
                (new MikrotikService($tenant))->setCustomerEnabled(
                    $customer,
                    $customer->status === Customer::STATUS_ACTIVE
                );
            }
        }

        $this->pushToRadius($customer, app('current_tenant'));

        return redirect()->route('customers.show', $customer)->with('success', 'Data pelanggan diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $username = $customer->pppoe_username;
        $tenant = app('current_tenant');
        $customer->delete();
        if ($username) {
            $this->forEachRadius($tenant, fn (RadiusManager $r) => $r->deleteCustomer($username));
        }

        return redirect()->route('customers.index')->with('success', 'Pelanggan dihapus.');
    }

    protected function pushToRadius(Customer $customer, ?Tenant $tenant): void
    {
        if (! $tenant || ! $tenant->plan?->allows('radius') || ! $customer->pppoe_username) {
            return;
        }
        $this->forEachRadius($tenant, fn (RadiusManager $r) => $r->upsertCustomer($customer));
    }

    protected function forEachRadius(Tenant $tenant, \Closure $fn): void
    {
        RadiusServer::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->get()
            ->each(fn (RadiusServer $s) => $fn(new RadiusManager($s)));
    }

    protected function validateData(Request $request, ?int $ignoreId = null): array
    {
        $tenantId = app('current_tenant_id');

        return $request->validate([
            'package_id' => ['nullable', "exists:packages,id,tenant_id,{$tenantId}"],
            'code' => ['nullable', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:active,isolated,terminated'],
            'pppoe_username' => ['nullable', 'string', 'max:64'],
            'pppoe_password' => ['nullable', 'string', 'max:64'],
            'due_day' => ['nullable', 'integer', 'min:1', 'max:28'],
            'installed_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    protected function nextCode(int $tenantId): string
    {
        // Only consider auto-formatted codes (C followed by digits) so a manually
        // set code like "CUST01" doesn't poison the sequence.
        $rows = Customer::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('code', 'like', 'C%')
            ->pluck('code');
        $maxN = 0;
        foreach ($rows as $code) {
            if (preg_match('/^C(\d+)$/', $code, $m)) {
                $n = (int) $m[1];
                if ($n > $maxN) {
                    $maxN = $n;
                }
            }
        }

        return 'C'.str_pad((string) ($maxN + 1), 4, '0', STR_PAD_LEFT);
    }
}
