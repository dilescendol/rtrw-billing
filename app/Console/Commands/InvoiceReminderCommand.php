<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\WhatsappTemplate;
use App\Services\WhatsappNotifier;
use Illuminate\Console\Command;

/**
 * Daily WhatsApp reminders for invoices.
 *
 * - H-3 / H-1 before due_date → invoice_due_soon
 * - H+1 / H+3 / H+7 after due_date → invoice_overdue
 *
 * Honors `Plan.allow_whatsapp` and `Tenant.fonnte_enabled` (skips silently
 * when not configured).
 */
class InvoiceReminderCommand extends Command
{
    protected $signature = 'invoice:remind {--tenant= : Process only this tenant id} {--dry-run}';

    protected $description = 'Send WhatsApp reminders for invoices that are due soon or overdue.';

    public function handle(): int
    {
        $tenants = Tenant::query()
            ->when($this->option('tenant'), fn ($q, $id) => $q->whereKey($id))
            ->get();

        $sent = 0;
        $skipped = 0;
        $today = now()->startOfDay();
        $dryRun = (bool) $this->option('dry-run');

        foreach ($tenants as $tenant) {
            $notifier = new WhatsappNotifier($tenant);
            if (! $notifier->isConfigured()) {
                $skipped++;

                continue;
            }

            $invoices = Invoice::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->whereIn('status', [Invoice::STATUS_UNPAID, Invoice::STATUS_OVERDUE])
                ->whereNotNull('due_date')
                ->with('customer')
                ->get();

            foreach ($invoices as $invoice) {
                if (! $invoice->customer || ! $invoice->customer->phone) {
                    continue;
                }
                $diff = $invoice->due_date->startOfDay()->diffInDays($today, false);
                $event = $this->resolveEvent($diff);
                if (! $event) {
                    continue;
                }
                if (! $this->shouldSendToday($invoice)) {
                    continue;
                }

                if ($dryRun) {
                    $this->line("[dry] tenant={$tenant->id} invoice={$invoice->invoice_no} event={$event}");
                    $sent++;

                    continue;
                }

                $ok = $notifier->send($event, $invoice->customer, [], $invoice);
                if ($ok) {
                    $invoice->forceFill([
                        'reminder_count' => (int) $invoice->reminder_count + 1,
                        'last_reminder_at' => now(),
                    ])->saveQuietly();
                    $sent++;
                }
            }
        }

        $this->info("Reminders processed: sent={$sent} skipped_tenants={$skipped}");

        return self::SUCCESS;
    }

    /**
     * Convert "due_date - today" days difference into an event slug, or
     * null if today is not a reminder day.
     */
    protected function resolveEvent(int $daysSinceDue): ?string
    {
        // diffInDays($a, $b, false) returns ($b - $a)/days, so positive
        // means today is AFTER due_date (overdue), negative means before.
        return match (true) {
            $daysSinceDue === -3, $daysSinceDue === -1 => WhatsappTemplate::EVENT_INVOICE_DUE_SOON,
            $daysSinceDue === 1, $daysSinceDue === 3, $daysSinceDue === 7 => WhatsappTemplate::EVENT_INVOICE_OVERDUE,
            default => null,
        };
    }

    protected function shouldSendToday(Invoice $invoice): bool
    {
        // Avoid spamming: skip if a reminder was already sent today.
        if (! $invoice->last_reminder_at) {
            return true;
        }

        return ! $invoice->last_reminder_at->isToday();
    }
}
