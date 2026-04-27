<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Pakasir payment gateway client.
 *
 * Pakasir is a simple Indonesian payment gateway with two endpoints we care about:
 *   - Hosted checkout URL of shape: https://pakasir.zone.id/pay/{project}/{amount}?order_id=...&ui=...&qris_only=...&redirect=...
 *   - Webhook callback (POST JSON) → contains order_id, project, amount, status, payment_method, signature
 *
 * Each tenant configures its own (project, api_key, signature) so that end-customer payments
 * settle directly into the tenant's Pakasir account. The platform also has its own credentials
 * (PAKASIR_PLATFORM_*) for SaaS subscription billing.
 */
class PakasirService
{
    public const SCOPE_PLATFORM = 'platform';

    public const SCOPE_TENANT = 'tenant';

    public function __construct(protected ?Tenant $tenant = null, protected string $scope = self::SCOPE_TENANT) {}

    public static function forTenant(Tenant $tenant): self
    {
        return new self($tenant, self::SCOPE_TENANT);
    }

    public static function forPlatform(): self
    {
        return new self(null, self::SCOPE_PLATFORM);
    }

    public function isConfigured(): bool
    {
        if ($this->scope === self::SCOPE_PLATFORM) {
            return filled(config('services.pakasir.platform_project'))
                && filled(config('services.pakasir.platform_api_key'));
        }

        return $this->tenant && $this->tenant->pakasir_enabled
            && filled($this->tenant->pakasir_project)
            && filled($this->tenant->pakasir_api_key);
    }

    public function project(): ?string
    {
        return $this->scope === self::SCOPE_PLATFORM
            ? config('services.pakasir.platform_project')
            : $this->tenant?->pakasir_project;
    }

    public function apiKey(): ?string
    {
        return $this->scope === self::SCOPE_PLATFORM
            ? config('services.pakasir.platform_api_key')
            : $this->tenant?->pakasir_api_key;
    }

    public function signature(): ?string
    {
        return $this->scope === self::SCOPE_PLATFORM
            ? config('services.pakasir.platform_signature')
            : $this->tenant?->pakasir_signature;
    }

    /**
     * Build hosted checkout URL.
     *
     * @param  string  $orderId  External order id (invoice or subscription id)
     * @param  int  $amountIdr  Amount in IDR (integer)
     * @param  string  $redirectUrl  URL to send user to after success
     */
    public function buildCheckoutUrl(string $orderId, int $amountIdr, string $redirectUrl): string
    {
        $project = $this->project();
        $base = rtrim(config('services.pakasir.base_url'), '/');

        return sprintf(
            '%s/pay/%s/%d?order_id=%s&redirect=%s',
            $base,
            urlencode($project),
            $amountIdr,
            urlencode($orderId),
            urlencode($redirectUrl)
        );
    }

    /**
     * Verify a transaction directly with Pakasir's API.
     *
     * @return array{verified: bool, raw?: array}
     */
    public function verifyTransaction(string $orderId, int $amountIdr): array
    {
        if (! $this->isConfigured()) {
            return ['verified' => false];
        }
        $base = rtrim(config('services.pakasir.base_url'), '/');
        try {
            $resp = Http::timeout(10)->get($base.'/api/transactiondetail', [
                'project' => $this->project(),
                'amount' => $amountIdr,
                'order_id' => $orderId,
                'api_key' => $this->apiKey(),
            ]);
            if (! $resp->ok()) {
                Log::warning('Pakasir verify failed', ['status' => $resp->status(), 'body' => $resp->body()]);

                return ['verified' => false];
            }
            $data = $resp->json();
            $tx = $data['transaction'] ?? null;
            $verified = $tx && (($tx['status'] ?? null) === 'completed');

            return ['verified' => (bool) $verified, 'raw' => $data];
        } catch (\Throwable $e) {
            Log::error('Pakasir verify exception', ['e' => $e->getMessage()]);

            return ['verified' => false];
        }
    }

    /**
     * Check webhook signature/secret. Pakasir lets you set a "signature" string in the
     * dashboard which it sends along with each webhook so the receiver can verify the
     * payload originates from Pakasir.
     */
    public function verifyWebhookSignature(?string $providedSignature): bool
    {
        $expected = $this->signature();
        if (! $expected) {
            // If no signature configured, fall back to verifying via API call upstream.
            return false;
        }

        return is_string($providedSignature) && hash_equals($expected, $providedSignature);
    }

    public static function generateOrderId(string $prefix = 'INV'): string
    {
        return $prefix.'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));
    }
}
