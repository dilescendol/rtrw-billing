<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $key = $this->throttleKey($request, $data['email']);
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']], (bool) ($data['remember'] ?? false))) {
            RateLimiter::hit($key, 60);

            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended($this->landingRouteFor($request->user()));
    }

    /**
     * Where to send the user immediately after a successful login,
     * based on their role.
     */
    protected function landingRouteFor(?User $user): string
    {
        if (! $user) {
            return route('home');
        }
        if ($user->isCustomer()) {
            return route('portal.dashboard');
        }
        if ($user->isSuperAdmin()) {
            return route('superadmin.tenants.index');
        }

        return route('dashboard');
    }

    protected function throttleKey(Request $request, string $email): string
    {
        return Str::lower($email).'|'.$request->ip();
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
