<?php

namespace App\Services;

use App\Models\NasDevice;
use Illuminate\Support\Facades\Log;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

/**
 * Wrapper around routeros-api-php for a single NAS device row.
 *
 * Existing tenant-level credentials (`tenant.mikrotik_*`) are still honoured
 * via {@see MikrotikService}; this service is for the per-device records
 * introduced in Phase 2 so a single tenant can have multiple routers.
 */
class NasDeviceService
{
    public function __construct(protected NasDevice $device) {}

    public function isConfigured(): bool
    {
        return $this->device->is_active
            && filled($this->device->host)
            && filled($this->device->api_user);
    }

    protected function client(): Client
    {
        $config = (new Config)
            ->set('host', $this->device->host)
            ->set('user', $this->device->api_user)
            ->set('pass', (string) $this->device->api_password)
            ->set('port', (int) ($this->device->api_port ?: 8728))
            ->set('timeout', 5);

        return new Client($config);
    }

    /**
     * @return array{ok: bool, message: string, identity?: string|null}
     */
    public function testConnection(): array
    {
        if (! $this->isConfigured()) {
            $this->markStatus(NasDevice::STATUS_FAILED, 'Belum dikonfigurasi.');

            return ['ok' => false, 'message' => 'NAS belum dikonfigurasi.'];
        }
        try {
            $rows = $this->client()->query(new Query('/system/identity/print'))->read();
            $identity = $rows[0]['name'] ?? null;
            $this->device->update([
                'identity' => $identity,
                'last_status' => NasDevice::STATUS_OK,
                'last_status_message' => 'Koneksi berhasil.',
                'last_seen_at' => now(),
            ]);

            return ['ok' => true, 'message' => 'Koneksi berhasil.', 'identity' => $identity];
        } catch (\Throwable $e) {
            Log::warning('NAS test failed', ['device_id' => $this->device->id, 'error' => $e->getMessage()]);
            $this->markStatus(NasDevice::STATUS_FAILED, $e->getMessage());

            return ['ok' => false, 'message' => 'Gagal: '.$e->getMessage()];
        }
    }

    public function createPppoeSecret(string $username, string $password, string $profile, ?string $comment = null): bool
    {
        return $this->safeWrite(function () use ($username, $password, $profile, $comment) {
            $client = $this->client();
            $existing = $client->query(
                (new Query('/ppp/secret/print'))->where('name', $username)
            )->read();
            if (! empty($existing)) {
                $client->query(
                    (new Query('/ppp/secret/set'))
                        ->equal('.id', $existing[0]['.id'])
                        ->equal('password', $password)
                        ->equal('profile', $profile)
                        ->equal('disabled', 'no')
                )->read();

                return true;
            }

            $client->query(
                (new Query('/ppp/secret/add'))
                    ->equal('name', $username)
                    ->equal('password', $password)
                    ->equal('service', 'pppoe')
                    ->equal('profile', $profile)
                    ->equal('comment', $comment ?? '')
            )->read();

            return true;
        }, 'createPppoeSecret');
    }

    public function setPppoeEnabled(string $username, bool $enabled): bool
    {
        return $this->safeWrite(function () use ($username, $enabled) {
            $client = $this->client();
            $found = $client->query(
                (new Query('/ppp/secret/print'))->where('name', $username)
            )->read();
            if (empty($found)) {
                return false;
            }
            $client->query(
                (new Query('/ppp/secret/set'))
                    ->equal('.id', $found[0]['.id'])
                    ->equal('disabled', $enabled ? 'no' : 'yes')
            )->read();
            if (! $enabled) {
                $active = $client->query(
                    (new Query('/ppp/active/print'))->where('name', $username)
                )->read();
                foreach ($active as $a) {
                    $client->query(
                        (new Query('/ppp/active/remove'))->equal('.id', $a['.id'])
                    )->read();
                }
            }

            return true;
        }, 'setPppoeEnabled');
    }

    public function upsertHotspotUser(string $username, string $password, ?string $profile = null, ?string $mac = null, ?string $limitUptime = null): bool
    {
        return $this->safeWrite(function () use ($username, $password, $profile, $mac, $limitUptime) {
            $client = $this->client();
            $existing = $client->query(
                (new Query('/ip/hotspot/user/print'))->where('name', $username)
            )->read();
            if (! empty($existing)) {
                $q = (new Query('/ip/hotspot/user/set'))
                    ->equal('.id', $existing[0]['.id'])
                    ->equal('password', $password);
                if ($profile) {
                    $q->equal('profile', $profile);
                }
                if ($mac) {
                    $q->equal('mac-address', $mac);
                }
                if ($limitUptime) {
                    $q->equal('limit-uptime', $limitUptime);
                }
                $client->query($q)->read();

                return true;
            }
            $q = (new Query('/ip/hotspot/user/add'))
                ->equal('name', $username)
                ->equal('password', $password);
            if ($profile) {
                $q->equal('profile', $profile);
            }
            if ($mac) {
                $q->equal('mac-address', $mac);
            }
            if ($limitUptime) {
                $q->equal('limit-uptime', $limitUptime);
            }
            $client->query($q)->read();

            return true;
        }, 'upsertHotspotUser');
    }

    public function removeHotspotUser(string $username): bool
    {
        return $this->safeWrite(function () use ($username) {
            $client = $this->client();
            $found = $client->query(
                (new Query('/ip/hotspot/user/print'))->where('name', $username)
            )->read();
            foreach ($found as $row) {
                $client->query(
                    (new Query('/ip/hotspot/user/remove'))->equal('.id', $row['.id'])
                )->read();
            }

            return true;
        }, 'removeHotspotUser');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listPppoeActive(): array
    {
        return $this->safeRead(function () {
            return $this->client()->query(new Query('/ppp/active/print'))->read();
        }, 'listPppoeActive');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listHotspotActive(): array
    {
        return $this->safeRead(function () {
            return $this->client()->query(new Query('/ip/hotspot/active/print'))->read();
        }, 'listHotspotActive');
    }

    protected function safeWrite(\Closure $fn, string $tag): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }
        try {
            $ok = (bool) $fn();
            if ($ok) {
                $this->markStatus(NasDevice::STATUS_OK, 'OK');
            }

            return $ok;
        } catch (\Throwable $e) {
            Log::warning("NAS {$tag} failed", ['device_id' => $this->device->id, 'error' => $e->getMessage()]);
            $this->markStatus(NasDevice::STATUS_FAILED, $e->getMessage());

            return false;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function safeRead(\Closure $fn, string $tag): array
    {
        if (! $this->isConfigured()) {
            return [];
        }
        try {
            $rows = $fn();
            $this->markStatus(NasDevice::STATUS_OK, 'OK');

            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            Log::warning("NAS {$tag} failed", ['device_id' => $this->device->id, 'error' => $e->getMessage()]);
            $this->markStatus(NasDevice::STATUS_FAILED, $e->getMessage());

            return [];
        }
    }

    protected function markStatus(string $status, string $message): void
    {
        $this->device->forceFill([
            'last_status' => $status,
            'last_status_message' => $message,
            'last_seen_at' => $status === NasDevice::STATUS_OK ? now() : $this->device->last_seen_at,
        ])->saveQuietly();
    }
}
