<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Customer portal guard: only customer-role users with a linked Customer
 * record (and an active tenant) may access /portal/*.
 */
class EnsureCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }
        if (! $user->isCustomer()) {
            return redirect()->route('dashboard');
        }
        if (! $user->customer_id || ! $user->customer) {
            abort(403, 'Akun pelanggan tidak terhubung ke data pelanggan.');
        }
        if ($user->tenant_id) {
            app()->instance('current_tenant_id', $user->tenant_id);
            app()->instance('current_tenant', $user->tenant);
        }

        return $next($request);
    }
}
