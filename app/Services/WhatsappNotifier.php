<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\WhatsappLog;
use App\Models\WhatsappTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Higher-level WhatsApp notifier — renders a per-tenant template for an event,
 * sends via Fonnte, and logs every attempt to `whatsapp_logs`.
 */
class WhatsappNotifier
{
    public function __construct(protected Tenant $tenant) {}

    public function isConfigured(): bool
    {
        return $this->tenant->fonnte_enabled
            && filled($this->tenant->fonnte_token)
            && (bool) $this->tenant->plan?->allows('whatsapp');
    }

    /**
     * @param  array<string, string|int|float|null>  $context
     */
    public function send(string $event, ?Customer $customer, array $context = [], ?Invoice $invoice = null): bool
    {
        if (! $this->isConfigured() || ! $customer || ! $customer->phone) {
            return false;
        }
        $rendered = $this->render($event, $customer, $context, $invoice);
        if (! $rendered) {
            return false;
        }

        $phone = $this->normalizePhone($customer->phone);
        $ok = $this->dispatchToFonnte($phone, $rendered);

        WhatsappLog::create([
            'tenant_id' => $this->tenant->id,
            'event' => $event,
            'phone' => $phone,
            'message' => $rendered,
            'success' => $ok,
            'response' => null,
            'customer_id' => $customer->id,
            'invoice_id' => $invoice?->id,
            'sent_at' => now(),
        ]);

        return $ok;
    }

    public function sendRaw(string $phone, string $message, ?string $event = null): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }
        $phone = $this->normalizePhone($phone);
        $ok = $this->dispatchToFonnte($phone, $message);

        WhatsappLog::create([
            'tenant_id' => $this->tenant->id,
            'event' => $event,
            'phone' => $phone,
            'message' => $message,
            'success' => $ok,
            'response' => null,
            'customer_id' => null,
            'invoice_id' => null,
            'sent_at' => now(),
        ]);

        return $ok;
    }

    /**
     * @param  array<string, string|int|float|null>  $context
     */
    public function render(string $event, ?Customer $customer, array $context = [], ?Invoice $invoice = null): ?string
    {
        $template = WhatsappTemplate::where('tenant_id', $this->tenant->id)
            ->where('event', $event)
            ->where('is_active', true)
            ->first();

        $body = $template?->message ?? (WhatsappTemplate::DEFAULTS[$event] ?? null);
        if (! $body) {
            return null;
        }

        $vars = array_merge($this->customerContext($customer), $this->invoiceContext($invoice), $context);
        foreach ($vars as $key => $value) {
            $body = str_replace('{{'.$key.'}}', (string) $value, $body);
        }

        return $body;
    }

    /**
     * @return array<string, string>
     */
    protected function customerContext(?Customer $customer): array
    {
        if (! $customer) {
            return ['nama' => '', 'kode' => ''];
        }

        return [
            'nama' => (string) $customer->name,
            'kode' => (string) $customer->code,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function invoiceContext(?Invoice $invoice): array
    {
        if (! $invoice) {
            return [];
        }

        return [
            'nomor_invoice' => (string) $invoice->invoice_no,
            'jumlah' => number_format((float) $invoice->amount_idr, 0, ',', '.'),
            'jatuh_tempo' => optional($invoice->due_date)->format('d M Y') ?? '-',
            'link_bayar' => (string) ($invoice->pakasir_payment_url ?? config('app.url').'/portal/invoices/'.$invoice->id),
        ];
    }

    protected function normalizePhone(string $phone): string
    {
        $target = preg_replace('/[^0-9]/', '', $phone) ?? '';
        if (str_starts_with($target, '08')) {
            $target = '62'.substr($target, 1);
        }
        if (str_starts_with($target, '+')) {
            $target = ltrim($target, '+');
        }

        return $target;
    }

    protected function dispatchToFonnte(string $target, string $message): bool
    {
        try {
            $resp = Http::withHeaders(['Authorization' => $this->tenant->fonnte_token])
                ->asForm()
                ->timeout(10)
                ->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                ]);

            return $resp->ok();
        } catch (\Throwable $e) {
            Log::warning('WhatsappNotifier dispatch failed', [
                'tenant_id' => $this->tenant->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
