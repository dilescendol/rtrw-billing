<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Allow only users whose `role` matches one of the given values.
     *
     * Usage in routes: ->middleware('role:admin,teknisi')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $allowed = collect($roles)
            ->flatMap(fn ($r) => array_map('trim', explode('|', $r)))
            ->filter()
            ->all();

        if (! empty($allowed) && ! $user->hasRole(...$allowed)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
