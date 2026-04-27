<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Services\PakasirService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function suspended()
    {
        $user = auth()->user();
        $tenant = $user->tenant;
        $plans = Plan::where('is_active', true)
            ->where('code', '!=', Plan::CODE_TRIAL)
            ->orderBy('sort_order')
            ->get();

        return view('subscription.suspended', compact('tenant', 'plans'));
    }

    public function plans()
    {
        $user = auth()->user();
        $tenant = $user->tenant;
        if ($tenant) {
            app()->instance('current_tenant', $tenant);
            app()->instance('current_tenant_id', $tenant->id);
        }
        $plans = Plan::where('is_active', true)
            ->where('code', '!=', Plan::CODE_TRIAL)
            ->orderBy('sort_order')
            ->get();

        return view('subscription.plans', compact('tenant', 'plans'));
    }

    public function checkout(Request $request, Plan $plan)
    {
        $user = auth()->user();
        $tenant = $user->tenant;

        if ($plan->price_idr <= 0) {
            return back()->withErrors(['plan' => 'Plan tidak valid.']);
        }

        $service = PakasirService::forPlatform();
        if (! $service->isConfigured()) {
            // Fallback: pretend paid for development. In production this is required.
            if (app()->environment('local')) {
                $sub = TenantSubscription::create([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'amount_idr' => $plan->price_idr,
                    'status' => TenantSubscription::STATUS_PAID,
                    'started_at' => now(),
                    'ends_at' => now()->addMonth(),
                    'paid_at' => now(),
                ]);
                $tenant->update([
                    'plan_id' => $plan->id,
                    'status' => Tenant::STATUS_ACTIVE,
                    'plan_ends_at' => $sub->ends_at,
                    'suspended_reason' => null,
                ]);

                return redirect()->route('dashboard')->with('success', '[DEV] Subscription aktif tanpa pembayaran.');
            }

            return back()->withErrors(['plan' => 'Pembayaran subscription belum tersedia. Hubungi admin platform.']);
        }

        $orderId = PakasirService::generateOrderId('SUB-'.$tenant->id.'-'.$plan->code);
        $url = $service->buildCheckoutUrl(
            $orderId,
            (int) $plan->price_idr,
            route('subscription.return', ['plan' => $plan->id])
        );

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'amount_idr' => $plan->price_idr,
            'status' => TenantSubscription::STATUS_PENDING,
            'pakasir_order_id' => $orderId,
            'pakasir_payment_url' => $url,
        ]);

        return redirect($url);
    }

    public function return(Request $request, Plan $plan)
    {
        $user = auth()->user();
        $tenant = $user->tenant;

        // Re-check status — webhook should already have marked the subscription paid.
        $sub = $tenant->subscriptions()
            ->where('plan_id', $plan->id)
            ->latest()
            ->first();

        if ($sub && $sub->status === TenantSubscription::STATUS_PAID) {
            return redirect()->route('dashboard')->with('success', 'Subscription aktif.');
        }

        return redirect()->route('subscription.plans')->with('info', 'Menunggu konfirmasi pembayaran.');
    }
}
