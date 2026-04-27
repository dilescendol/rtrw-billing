<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantUsable
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->tenant_id) {
            return redirect()->route('login');
        }

        $tenant = $user->tenant;
        if (! $tenant) {
            auth()->logout();

            return redirect()->route('login')->withErrors(['email' => 'Tenant tidak ditemukan.']);
        }

        // Auto-suspend on access if trial expired or plan expired
        if ($tenant->isTrial() && $tenant->trial_ends_at && $tenant->trial_ends_at->isPast()) {
            $tenant->update([
                'status' => Tenant::STATUS_SUSPENDED,
                'suspended_reason' => 'Trial 3 hari telah berakhir.',
            ]);
        } elseif ($tenant->isActive() && $tenant->plan_ends_at && $tenant->plan_ends_at->isPast()) {
            $tenant->update([
                'status' => Tenant::STATUS_SUSPENDED,
                'suspended_reason' => 'Masa berlaku paket telah habis.',
            ]);
        }

        if ($tenant->isSuspended() && ! $request->routeIs('subscription.*', 'logout', 'profile.*')) {
            return redirect()->route('subscription.suspended');
        }

        app()->instance('current_tenant', $tenant);
        app()->instance('current_tenant_id', $tenant->id);

        return $next($request);
    }
}
