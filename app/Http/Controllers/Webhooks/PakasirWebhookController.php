<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\WhatsappTemplate;
use App\Services\PakasirService;
use App\Services\WhatsappNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PakasirWebhookController extends Controller
{
    /**
     * Pakasir webhook for tenant-level (end-customer) invoice payments.
     * URL contains the tenant id so we can scope the lookup.
     */
    public function tenant(Request $request, Tenant $tenant)
    {
        $payload = $request->all();
        Log::info('Pakasir tenant webhook', ['tenant' => $tenant->id, 'payload' => $payload]);

        $orderId = (string) ($payload['order_id'] ?? '');
        $amount = (int) ($payload['amount'] ?? 0);
        $status = (string) ($payload['status'] ?? '');
        $signature = $request->header('X-Pakasir-Signature') ?? ($payload['signature'] ?? null);

        if (! $orderId) {
            return response()->json(['ok' => false, 'reason' => 'missing order_id'], 400);
        }

        $service = PakasirService::forTenant($tenant);

        // Verify either by signature or by callback to Pakasir
        $signatureOk = $service->verifyWebhookSignature($signature);
        if (! $signatureOk) {
            $verify = $service->verifyTransaction($orderId, $amount);
            if (! $verify['verified']) {
                return response()->json(['ok' => false, 'reason' => 'unverified'], 401);
            }
        }

        if ($status !== 'completed') {
            return response()->json(['ok' => true, 'note' => 'ignored non-completed']);
        }

        $invoice = Invoice::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('pakasir_order_id', $orderId)
            ->first();

        if (! $invoice) {
            return response()->json(['ok' => false, 'reason' => 'invoice not found'], 404);
        }

        if (! $invoice->isPaid()) {
            DB::transaction(function () use ($invoice, $tenant, $orderId) {
                // Re-check inside transaction with row lock to drop duplicate webhooks.
                $fresh = Invoice::withoutGlobalScopes()->lockForUpdate()->find($invoice->id);
                if (! $fresh || $fresh->isPaid()) {
                    return;
                }
                $fresh->update([
                    'status' => Invoice::STATUS_PAID,
                    'paid_at' => now(),
                    'payment_method' => 'pakasir',
                ]);
                $fresh->payments()->create([
                    'tenant_id' => $tenant->id,
                    'amount_idr' => $fresh->amount_idr,
                    'method' => 'pakasir',
                    'paid_at' => now(),
                    'reference' => $orderId,
                    'status' => 'verified',
                ]);
                $fresh->load('customer');
                if ($fresh->customer) {
                    (new WhatsappNotifier($tenant))
                        ->send(WhatsappTemplate::EVENT_PAYMENT_RECEIVED, $fresh->customer, [], $fresh);
                }
            });
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Pakasir webhook for SaaS subscription payments (platform-level).
     */
    public function platform(Request $request)
    {
        $payload = $request->all();
        Log::info('Pakasir platform webhook', ['payload' => $payload]);

        $orderId = (string) ($payload['order_id'] ?? '');
        $amount = (int) ($payload['amount'] ?? 0);
        $status = (string) ($payload['status'] ?? '');
        $signature = $request->header('X-Pakasir-Signature') ?? ($payload['signature'] ?? null);

        if (! $orderId) {
            return response()->json(['ok' => false], 400);
        }

        $service = PakasirService::forPlatform();
        if (! $service->verifyWebhookSignature($signature)) {
            $verify = $service->verifyTransaction($orderId, $amount);
            if (! $verify['verified']) {
                return response()->json(['ok' => false], 401);
            }
        }

        if ($status !== 'completed') {
            return response()->json(['ok' => true]);
        }

        $sub = TenantSubscription::where('pakasir_order_id', $orderId)->first();
        if (! $sub) {
            return response()->json(['ok' => false], 404);
        }
        if ($sub->status !== TenantSubscription::STATUS_PAID) {
            DB::transaction(function () use ($sub) {
                $fresh = TenantSubscription::lockForUpdate()->find($sub->id);
                if (! $fresh || $fresh->status === TenantSubscription::STATUS_PAID) {
                    return;
                }
                $fresh->update([
                    'status' => TenantSubscription::STATUS_PAID,
                    'started_at' => now(),
                    'ends_at' => now()->addMonth(),
                    'paid_at' => now(),
                ]);
                $fresh->tenant->update([
                    'plan_id' => $fresh->plan_id,
                    'status' => Tenant::STATUS_ACTIVE,
                    'plan_ends_at' => $fresh->ends_at,
                    'suspended_reason' => null,
                ]);
            });
        }

        return response()->json(['ok' => true]);
    }
}
