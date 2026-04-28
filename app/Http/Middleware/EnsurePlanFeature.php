<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Block routes when the current tenant's plan does not include the given feature.
 *
 * Usage: `Route::middleware('plan.feature:hotspot')`. Multiple features can be
 * passed as separate arguments — all must be allowed.
 */
class EnsurePlanFeature
{
    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;
        if (! $tenant) {
            $tenant = $request->user()?->tenant;
        }

        $plan = $tenant?->plan;

        foreach ($features as $feature) {
            if (! $plan || ! $plan->allows($feature)) {
                abort(403, 'Fitur "'.$feature.'" tidak tersedia di paket berlangganan Anda.');
            }
        }

        return $next($request);
    }
}
