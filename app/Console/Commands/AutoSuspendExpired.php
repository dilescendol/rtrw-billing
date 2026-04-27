<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Console\Command;

class AutoSuspendExpired extends Command
{
    protected $signature = 'billing:auto-suspend';

    protected $description = 'Suspend tenants whose trial/plan has expired and mark overdue invoices.';

    public function handle(): int
    {
        // Suspend expired trials
        $trial = Tenant::where('status', Tenant::STATUS_TRIAL)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->update([
                'status' => Tenant::STATUS_SUSPENDED,
                'suspended_reason' => 'Trial 3 hari telah berakhir.',
            ]);
        $this->line("Suspended trial tenants: {$trial}");

        // Suspend expired paid plans
        $active = Tenant::where('status', Tenant::STATUS_ACTIVE)
            ->whereNotNull('plan_ends_at')
            ->where('plan_ends_at', '<', now())
            ->update([
                'status' => Tenant::STATUS_SUSPENDED,
                'suspended_reason' => 'Masa berlaku paket telah habis.',
            ]);
        $this->line("Suspended expired paid tenants: {$active}");

        // Mark overdue invoices
        $overdue = Invoice::withoutGlobalScopes()
            ->where('status', Invoice::STATUS_UNPAID)
            ->where('due_date', '<', now())
            ->update(['status' => Invoice::STATUS_OVERDUE]);
        $this->line("Marked overdue invoices: {$overdue}");

        return self::SUCCESS;
    }
}
