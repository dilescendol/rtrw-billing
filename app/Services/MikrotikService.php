<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

class MikrotikService
{
    public function __construct(protected Tenant $tenant) {}

    public function isConfigured(): bool
    {
        return $this->tenant->mikrotik_enabled
            && filled($this->tenant->mikrotik_host)
            && filled($this->tenant->mikrotik_user)
            && filled($this->tenant->mikrotik_password);
    }

    protected function client(): Client
    {
        $config = (new Config)
            ->set('host', $this->tenant->mikrotik_host)
            ->set('user', $this->tenant->mikrotik_user)
            ->set('pass', $this->tenant->mikrotik_password)
            ->set('port', (int) ($this->tenant->mikrotik_port ?: 8728))
            ->set('timeout', 5);

        return new Client($config);
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function testConnection(): array
    {
        if (! $this->isConfigured()) {
            return ['ok' => false, 'message' => 'MikroTik belum dikonfigurasi.'];
        }
        try {
            $client = $this->client();
            $client->query(new Query('/system/identity/print'))->read();

            return ['ok' => true, 'message' => 'Koneksi berhasil.'];
        } catch (\Throwable $e) {
            Log::warning('MikroTik test failed', ['e' => $e->getMessage()]);

            return ['ok' => false, 'message' => 'Gagal: '.$e->getMessage()];
        }
    }

    public function createPppoeUser(Customer $customer): bool
    {
        if (! $this->isConfigured() || ! $customer->pppoe_username) {
            return false;
        }
        try {
            $client = $this->client();
            $query = (new Query('/ppp/secret/add'))
                ->equal('name', $customer->pppoe_username)
                ->equal('password', $customer->pppoe_password ?: 'pass'.$customer->id)
                ->equal('service', 'pppoe')
                ->equal('profile', $customer->package?->mikrotik_profile ?: 'default')
                ->equal('comment', 'tenant:'.$this->tenant->id.' cust:'.$customer->id);
            $client->query($query)->read();

            return true;
        } catch (\Throwable $e) {
            Log::warning('MikroTik createPppoeUser failed', ['e' => $e->getMessage()]);

            return false;
        }
    }

    public function setCustomerEnabled(Customer $customer, bool $enabled): bool
    {
        if (! $this->isConfigured() || ! $customer->pppoe_username) {
            return false;
        }
        try {
            $client = $this->client();
            $found = $client->query(
                (new Query('/ppp/secret/print'))->where('name', $customer->pppoe_username)
            )->read();
            if (empty($found)) {
                return false;
            }
            $id = $found[0]['.id'];
            $client->query(
                (new Query('/ppp/secret/set'))
                    ->equal('.id', $id)
                    ->equal('disabled', $enabled ? 'no' : 'yes')
            )->read();
            // If disabling, also kick active session
            if (! $enabled) {
                $active = $client->query(
                    (new Query('/ppp/active/print'))->where('name', $customer->pppoe_username)
                )->read();
                foreach ($active as $a) {
                    $client->query(
                        (new Query('/ppp/active/remove'))->equal('.id', $a['.id'])
                    )->read();
                }
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('MikroTik setCustomerEnabled failed', ['e' => $e->getMessage()]);

            return false;
        }
    }
}
