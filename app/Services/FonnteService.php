<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp gateway via Fonnte.com — POST https://api.fonnte.com/send
 * Header: Authorization: <token>
 * Body: target, message
 */
class FonnteService
{
    public function __construct(protected Tenant $tenant) {}

    public function isConfigured(): bool
    {
        return $this->tenant->fonnte_enabled && filled($this->tenant->fonnte_token);
    }

    public function send(string $phone, string $message): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }
        $target = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($target, '08')) {
            $target = '62'.substr($target, 1);
        }
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
            Log::warning('Fonnte send failed', ['e' => $e->getMessage()]);

            return false;
        }
    }
}
