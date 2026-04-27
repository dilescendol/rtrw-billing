<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AntiAbuse;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function show()
    {
        // Set fingerprint cookie if missing — used by AntiAbuse
        $cookie = request()->cookie('rtrw_fp');
        $response = response()->view('auth.register');
        if (! $cookie) {
            $response->withCookie(cookie('rtrw_fp', Str::random(40), 60 * 24 * 365, null, null, false, true));
        }

        return $response;
    }

    public function store(Request $request, AntiAbuse $antiAbuse)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:32'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'agree' => ['accepted'],
        ]);

        $check = $antiAbuse->check($request, $data['email'], $data['phone']);
        if (! $check['allowed']) {
            $antiAbuse->record($request, $data['email'], $data['phone'], 'blocked');

            return back()->withErrors(['email' => $check['reason']])->onlyInput('email', 'name', 'business_name', 'phone');
        }

        $user = DB::transaction(function () use ($data, $request, $antiAbuse) {
            $trialPlan = Plan::where('code', Plan::CODE_TRIAL)->first();
            $tenant = Tenant::create([
                'name' => $data['business_name'],
                'slug' => Str::slug($data['business_name']).'-'.Str::lower(Str::random(5)),
                'plan_id' => $trialPlan?->id,
                'status' => Tenant::STATUS_TRIAL,
                'trial_ends_at' => now()->addDays((int) config('services.app_settings.trial_days', 3)),
                'business_name' => $data['business_name'],
                'business_phone' => $data['phone'],
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'role' => 'owner',
                'fingerprint_hash' => $antiAbuse->fingerprintFromRequest($request),
                'register_ip_hash' => $antiAbuse->hash((string) $request->ip()),
            ]);

            $tenant->update(['owner_user_id' => $user->id]);

            return $user;
        });

        $antiAbuse->record($request, $data['email'], $data['phone'], 'registered');
        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Pendaftaran berhasil! Trial 3 hari sudah aktif.');
    }
}
