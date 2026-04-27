<?php

namespace App\Services;

use App\Models\RegistrationAttempt;
use Illuminate\Http\Request;

class AntiAbuse
{
    public const COOLDOWN_DAYS = 30;

    public const MAX_PER_IP_PER_DAY = 3;

    public const MAX_PER_FINGERPRINT_PER_DAY = 2;

    public function hash(string $value): string
    {
        return hash('sha256', mb_strtolower(trim($value)).'|'.config('app.key'));
    }

    public function fingerprintFromRequest(Request $request): string
    {
        $payload = implode('|', [
            (string) $request->userAgent(),
            (string) $request->header('Accept-Language'),
            (string) $request->header('Sec-Ch-Ua'),
            (string) $request->cookie('rtrw_fp', ''),
        ]);

        return hash('sha256', $payload);
    }

    /**
     * @return array{allowed: bool, reason?: string}
     */
    public function check(Request $request, string $email, ?string $phone = null): array
    {
        $emailHash = $this->hash($email);
        $ipHash = $this->hash((string) $request->ip());
        $fpHash = $this->fingerprintFromRequest($request);
        $phoneHash = $phone ? $this->hash($phone) : null;

        // 1) Email cooldown — if this email was used to register within the cooldown window
        $emailRecent = RegistrationAttempt::where('email_hash', $emailHash)
            ->where('status', 'registered')
            ->where('created_at', '>', now()->subDays(self::COOLDOWN_DAYS))
            ->exists();
        if ($emailRecent) {
            return ['allowed' => false, 'reason' => 'Email ini sudah pernah dipakai untuk mendaftar dalam '.self::COOLDOWN_DAYS.' hari terakhir.'];
        }

        // 2) IP rate limit per day
        $ipCount = RegistrationAttempt::where('ip_hash', $ipHash)
            ->where('created_at', '>', now()->subDay())
            ->count();
        if ($ipCount >= self::MAX_PER_IP_PER_DAY) {
            return ['allowed' => false, 'reason' => 'Terlalu banyak pendaftaran dari jaringan Anda. Coba lagi nanti.'];
        }

        // 3) Fingerprint rate limit per day
        $fpCount = RegistrationAttempt::where('fingerprint_hash', $fpHash)
            ->where('created_at', '>', now()->subDay())
            ->count();
        if ($fpCount >= self::MAX_PER_FINGERPRINT_PER_DAY) {
            return ['allowed' => false, 'reason' => 'Pendaftaran dari perangkat ini sudah mencapai batas harian.'];
        }

        // 4) Phone cooldown if provided
        if ($phoneHash) {
            $phoneRecent = RegistrationAttempt::where('phone_hash', $phoneHash)
                ->where('status', 'registered')
                ->where('created_at', '>', now()->subDays(self::COOLDOWN_DAYS))
                ->exists();
            if ($phoneRecent) {
                return ['allowed' => false, 'reason' => 'Nomor WhatsApp ini sudah dipakai untuk mendaftar.'];
            }
        }

        return ['allowed' => true];
    }

    public function record(Request $request, string $email, ?string $phone, string $status): void
    {
        RegistrationAttempt::create([
            'email_hash' => $this->hash($email),
            'ip_hash' => $this->hash((string) $request->ip()),
            'fingerprint_hash' => $this->fingerprintFromRequest($request),
            'phone_hash' => $phone ? $this->hash($phone) : null,
            'status' => $status,
        ]);
    }
}
