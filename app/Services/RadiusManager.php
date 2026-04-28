<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\NasDevice;
use App\Models\RadiusServer;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Push customers + NAS devices into a FreeRADIUS SQL backend.
 *
 * Uses the standard rlm_sql tables (`radcheck`, `radreply`, `radusergroup`,
 * `radgroupreply`, `nas`). The connection is built dynamically from the
 * `radius_servers` row so a single tenant can target multiple FreeRADIUS
 * instances.
 */
class RadiusManager
{
    public function __construct(protected RadiusServer $server) {}

    public function isConfigured(): bool
    {
        return $this->server->is_active
            && filled($this->server->sql_database)
            && filled($this->server->sql_username);
    }

    protected function connection(): ConnectionInterface
    {
        $name = 'radius_'.$this->server->id;
        $driver = $this->server->sql_driver ?: 'mysql';

        $defaultPort = match ($driver) {
            'pgsql' => 5432,
            'sqlsrv' => 1433,
            default => 3306,
        };
        $charset = match ($driver) {
            'pgsql', 'sqlsrv' => 'utf8',
            default => 'utf8mb4',
        };

        $config = array_filter([
            'driver' => $driver,
            'host' => $this->server->sql_host ?: $this->server->host,
            'port' => $this->server->sql_port ?: $defaultPort,
            'database' => $this->server->sql_database,
            'username' => $this->server->sql_username,
            'password' => (string) $this->server->sql_password,
            'charset' => $charset,
            'collation' => $driver === 'mysql' ? 'utf8mb4_unicode_ci' : null,
            'prefix' => '',
            'strict' => false,
        ], fn ($v) => $v !== null);

        config()->set("database.connections.{$name}", $config);

        // Force a fresh resolve in case config changed since previous call.
        DB::purge($name);

        return DB::connection($name);
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function testConnection(): array
    {
        if (! $this->isConfigured()) {
            $this->markStatus(RadiusServer::STATUS_FAILED, 'Belum dikonfigurasi.');

            return ['ok' => false, 'message' => 'RADIUS belum dikonfigurasi.'];
        }
        try {
            $this->connection()->select('select 1');
            $this->markStatus(RadiusServer::STATUS_OK, 'Koneksi SQL OK.');

            return ['ok' => true, 'message' => 'Koneksi SQL berhasil.'];
        } catch (\Throwable $e) {
            Log::warning('RADIUS test failed', ['server_id' => $this->server->id, 'error' => $e->getMessage()]);
            $this->markStatus(RadiusServer::STATUS_FAILED, $e->getMessage());

            return ['ok' => false, 'message' => 'Gagal: '.$e->getMessage()];
        }
    }

    public function upsertCustomer(Customer $customer): bool
    {
        return $this->safe(function () use ($customer) {
            $username = $customer->pppoe_username;
            if (! $username) {
                return false;
            }
            $conn = $this->connection();

            // radcheck — Cleartext-Password
            $conn->table('radcheck')->updateOrInsert(
                ['username' => $username, 'attribute' => 'Cleartext-Password'],
                ['op' => ':=', 'value' => (string) $customer->pppoe_password]
            );

            // radusergroup — based on package profile or radius_group
            $group = $customer->radius_group ?: ($customer->package?->mikrotik_profile ?: 'default');
            $conn->table('radusergroup')->where('username', $username)->delete();
            $conn->table('radusergroup')->insert([
                'username' => $username,
                'groupname' => $group,
                'priority' => 1,
            ]);

            // radreply — Framed-IP-Address (optional)
            $conn->table('radreply')->where('username', $username)->where('attribute', 'Framed-IP-Address')->delete();
            if (filled($customer->ip_address)) {
                $conn->table('radreply')->insert([
                    'username' => $username,
                    'attribute' => 'Framed-IP-Address',
                    'op' => ':=',
                    'value' => $customer->ip_address,
                ]);
            }

            // disable account if not active — set Auth-Type := Reject
            $conn->table('radcheck')->where('username', $username)->where('attribute', 'Auth-Type')->delete();
            if ($customer->status !== Customer::STATUS_ACTIVE) {
                $conn->table('radcheck')->insert([
                    'username' => $username,
                    'attribute' => 'Auth-Type',
                    'op' => ':=',
                    'value' => 'Reject',
                ]);
            }

            return true;
        }, 'upsertCustomer');
    }

    public function deleteCustomer(string $username): bool
    {
        return $this->safe(function () use ($username) {
            $conn = $this->connection();
            $conn->table('radcheck')->where('username', $username)->delete();
            $conn->table('radreply')->where('username', $username)->delete();
            $conn->table('radusergroup')->where('username', $username)->delete();

            return true;
        }, 'deleteCustomer');
    }

    public function upsertGroupRateLimit(string $group, ?string $rateLimit): bool
    {
        return $this->safe(function () use ($group, $rateLimit) {
            $conn = $this->connection();
            $conn->table('radgroupreply')->where('groupname', $group)->where('attribute', 'Mikrotik-Rate-Limit')->delete();
            if ($rateLimit) {
                $conn->table('radgroupreply')->insert([
                    'groupname' => $group,
                    'attribute' => 'Mikrotik-Rate-Limit',
                    'op' => ':=',
                    'value' => $rateLimit,
                ]);
            }

            return true;
        }, 'upsertGroupRateLimit');
    }

    public function syncNas(NasDevice $device): bool
    {
        return $this->safe(function () use ($device) {
            $conn = $this->connection();
            $secret = (string) ($device->radius_secret ?: 'testing123');
            $row = $conn->table('nas')->where('nasname', $device->host)->first();
            $payload = [
                'nasname' => $device->host,
                'shortname' => substr($device->name, 0, 32),
                'type' => 'other',
                'secret' => $secret,
                'description' => 'tenant-'.$device->tenant_id.' device-'.$device->id,
            ];
            if ($row) {
                $conn->table('nas')->where('id', $row->id)->update($payload);
            } else {
                $conn->table('nas')->insert($payload);
            }

            return true;
        }, 'syncNas');
    }

    protected function safe(\Closure $fn, string $tag): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }
        try {
            $ok = (bool) $fn();
            if ($ok) {
                $this->markStatus(RadiusServer::STATUS_OK, 'OK');
            }

            return $ok;
        } catch (\Throwable $e) {
            Log::warning("RADIUS {$tag} failed", ['server_id' => $this->server->id, 'error' => $e->getMessage()]);
            $this->markStatus(RadiusServer::STATUS_FAILED, $e->getMessage());

            return false;
        }
    }

    protected function markStatus(string $status, string $message): void
    {
        $this->server->forceFill([
            'last_status' => $status,
            'last_status_message' => $message,
            'last_seen_at' => $status === RadiusServer::STATUS_OK ? now() : $this->server->last_seen_at,
        ])->saveQuietly();
    }
}
