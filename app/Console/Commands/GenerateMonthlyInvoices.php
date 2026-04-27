<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\InvoiceGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'billing:generate-monthly {--tenant=} {--period=}';

    protected $description = 'Generate monthly invoices for all active customers across active tenants.';

    public function handle(InvoiceGenerator $generator): int
    {
        $period = $this->option('period');
        $for = $period
            ? CarbonImmutable::createFromFormat('!Y-m', $period)->startOfMonth()
            : CarbonImmutable::now()->startOfMonth();

        $query = Tenant::query()->where('status', '!=', Tenant::STATUS_SUSPENDED);
        if ($this->option('tenant')) {
            $query->where('id', $this->option('tenant'));
        }

        $total = 0;
        $query->chunk(50, function ($tenants) use ($generator, $for, &$total) {
            foreach ($tenants as $tenant) {
                $count = $generator->generateForTenant($tenant, $for);
                $total += $count;
                $this->line("Tenant {$tenant->id} ({$tenant->name}): {$count} invoice baru.");
            }
        });

        $this->info("Selesai. Total invoice baru: {$total}");

        return self::SUCCESS;
    }
}
