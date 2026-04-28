<?php

namespace App\Services;

use App\Models\GenieacsServer;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Lightweight wrapper for the GenieACS NBI (https://docs.genieacs.com/en/latest/api.html).
 *
 * Authenticates with HTTP Basic if credentials are configured, otherwise
 * issues unauthenticated requests (useful for self-hosted single-tenant
 * deployments).
 */
class GenieacsService
{
    public function __construct(protected GenieacsServer $server) {}

    public function isConfigured(): bool
    {
        return $this->server->is_active && filled($this->server->nbi_url);
    }

    protected function client(): PendingRequest
    {
        $req = Http::baseUrl(rtrim($this->server->nbi_url, '/'))
            ->timeout(8)
            ->acceptJson();

        if (filled($this->server->auth_user)) {
            $req = $req->withBasicAuth(
                (string) $this->server->auth_user,
                (string) $this->server->auth_password,
            );
        }

        return $req;
    }

    /**
     * @return array{ok: bool, message: string, count?: int}
     */
    public function testConnection(): array
    {
        if (! $this->isConfigured()) {
            $this->markStatus(GenieacsServer::STATUS_FAILED, 'Belum dikonfigurasi.');

            return ['ok' => false, 'message' => 'GenieACS belum dikonfigurasi.'];
        }
        try {
            $resp = $this->client()->get('/devices', [
                'projection' => '_id',
                'limit' => 1,
            ]);
            if (! $resp->ok()) {
                $this->markStatus(GenieacsServer::STATUS_FAILED, "HTTP {$resp->status()}");

                return ['ok' => false, 'message' => "HTTP {$resp->status()}"];
            }
            $this->markStatus(GenieacsServer::STATUS_OK, 'Koneksi NBI OK.');

            return ['ok' => true, 'message' => 'Koneksi GenieACS OK.', 'count' => count($resp->json() ?? [])];
        } catch (\Throwable $e) {
            Log::warning('GenieACS test failed', ['server_id' => $this->server->id, 'error' => $e->getMessage()]);
            $this->markStatus(GenieacsServer::STATUS_FAILED, $e->getMessage());

            return ['ok' => false, 'message' => 'Gagal: '.$e->getMessage()];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listDevices(?string $serial = null, int $limit = 100): array
    {
        if (! $this->isConfigured()) {
            return [];
        }
        $params = [
            'limit' => $limit,
            'projection' => implode(',', [
                '_id',
                '_lastInform',
                'InternetGatewayDevice.DeviceInfo.SerialNumber',
                'InternetGatewayDevice.DeviceInfo.SoftwareVersion',
                'InternetGatewayDevice.DeviceInfo.HardwareVersion',
                'InternetGatewayDevice.LANDevice.1.LANHostConfigManagement.IPInterface.1.IPInterfaceIPAddress',
            ]),
        ];
        if ($serial) {
            $params['query'] = json_encode([
                'InternetGatewayDevice.DeviceInfo.SerialNumber._value' => $serial,
            ]);
        }
        try {
            $resp = $this->client()->get('/devices', $params);
            $this->markStatus(
                $resp->ok() ? GenieacsServer::STATUS_OK : GenieacsServer::STATUS_FAILED,
                $resp->ok() ? 'OK' : "HTTP {$resp->status()}"
            );

            return $resp->ok() ? (array) $resp->json() : [];
        } catch (\Throwable $e) {
            Log::warning('GenieACS listDevices failed', ['error' => $e->getMessage()]);
            $this->markStatus(GenieacsServer::STATUS_FAILED, $e->getMessage());

            return [];
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDevice(string $deviceId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }
        try {
            $resp = $this->client()->get('/devices', [
                'query' => json_encode(['_id' => $deviceId]),
                'limit' => 1,
            ]);
            if (! $resp->ok()) {
                return null;
            }
            $rows = (array) $resp->json();

            return $rows[0] ?? null;
        } catch (\Throwable $e) {
            Log::warning('GenieACS getDevice failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function reboot(string $deviceId): bool
    {
        return $this->postTask($deviceId, ['name' => 'reboot']);
    }

    /**
     * @param  array<int, array{string, mixed, ?string}>  $params
     */
    public function setParameterValues(string $deviceId, array $params): bool
    {
        return $this->postTask($deviceId, [
            'name' => 'setParameterValues',
            'parameterValues' => $params,
        ]);
    }

    public function refresh(string $deviceId, string $path = 'InternetGatewayDevice'): bool
    {
        return $this->postTask($deviceId, [
            'name' => 'refreshObject',
            'objectName' => $path,
        ]);
    }

    /**
     * @param  array<string, mixed>  $task
     */
    protected function postTask(string $deviceId, array $task): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }
        try {
            $resp = $this->client()
                ->withQueryParameters(['connection_request' => true])
                ->post('/devices/'.rawurlencode($deviceId).'/tasks', $task);
            $ok = $resp->successful();
            $this->markStatus(
                $ok ? GenieacsServer::STATUS_OK : GenieacsServer::STATUS_FAILED,
                $ok ? 'OK' : "HTTP {$resp->status()}"
            );

            return $ok;
        } catch (\Throwable $e) {
            Log::warning('GenieACS postTask failed', ['error' => $e->getMessage()]);
            $this->markStatus(GenieacsServer::STATUS_FAILED, $e->getMessage());

            return false;
        }
    }

    protected function markStatus(string $status, string $message): void
    {
        $this->server->forceFill([
            'last_status' => $status,
            'last_status_message' => $message,
            'last_seen_at' => $status === GenieacsServer::STATUS_OK ? now() : $this->server->last_seen_at,
        ])->saveQuietly();
    }
}
