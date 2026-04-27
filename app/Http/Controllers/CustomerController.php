<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Package;
use App\Services\MikrotikService;
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

        return redirect()->route('customers.show', $customer)->with('success', 'Data pelanggan diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Pelanggan dihapus.');
    }

    protected function validateData(Request $request, ?int $ignoreId = null): array
    {
        $tenantId = app('current_tenant_id');

        return $request->validate([
            'package_id' => ['nullable', 'exists:packages,id'],
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
        $count = Customer::withoutGlobalScopes()->where('tenant_id', $tenantId)->count() + 1;

        return 'C'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
