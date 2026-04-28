<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function tenants(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $tenants = Tenant::with(['plan', 'owner'])
            ->withCount(['users', 'customers', 'invoices'])
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('business_name', 'like', "%{$q}%")
                        ->orWhere('slug', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'tenants_total' => Tenant::count(),
            'tenants_trial' => Tenant::where('status', Tenant::STATUS_TRIAL)->count(),
            'tenants_active' => Tenant::where('status', Tenant::STATUS_ACTIVE)->count(),
            'tenants_suspended' => Tenant::where('status', Tenant::STATUS_SUSPENDED)->count(),
        ];

        return view('superadmin.tenants', compact('tenants', 'stats', 'q'));
    }

    public function plans()
    {
        $plans = Plan::orderBy('sort_order')->get();

        return view('superadmin.plans', compact('plans'));
    }
}
