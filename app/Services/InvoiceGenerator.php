<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Tenant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class InvoiceGenerator
{
    /**
     * Generate next invoice for the given customer for the upcoming billing month.
     *
     * Returns the created Invoice or null if package missing / already exists.
     */
    public function generateForCustomer(Customer $customer, ?CarbonImmutable $for = null): ?Invoice
    {
        $tenant = $customer->tenant;
        if (! $customer->package || $customer->status === Customer::STATUS_TERMINATED) {
            return null;
        }
        $for = $for ?: CarbonImmutable::now()->startOfMonth();
        $periodStart = $for->startOfMonth();
        $periodEnd = $for->endOfMonth();

        // Skip if invoice already exists for this period
        $exists = Invoice::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('customer_id', $customer->id)
            ->where('period_start', $periodStart->toDateString())
            ->exists();
        if ($exists) {
            return null;
        }

        $dueDay = (int) ($customer->due_day ?: $tenant->default_due_day ?: 5);
        $dueDay = max(1, min(28, $dueDay));
        $dueDate = $periodStart->day($dueDay);

        return DB::transaction(function () use ($tenant, $customer, $periodStart, $periodEnd, $dueDate) {
            $invoice = new Invoice;
            $invoice->tenant_id = $tenant->id;
            $invoice->customer_id = $customer->id;
            $invoice->package_id = $customer->package_id;
            $invoice->invoice_no = $this->nextInvoiceNumber($tenant);
            $invoice->period_start = $periodStart->toDateString();
            $invoice->period_end = $periodEnd->toDateString();
            $invoice->due_date = $dueDate->toDateString();
            $invoice->amount_idr = $customer->package->price_idr;
            $invoice->status = Invoice::STATUS_UNPAID;
            $invoice->save();

            return $invoice;
        });
    }

    public function generateForTenant(Tenant $tenant, ?CarbonImmutable $for = null): int
    {
        $count = 0;
        Customer::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('status', Customer::STATUS_ACTIVE)
            ->whereNotNull('package_id')
            ->chunk(100, function ($customers) use (&$count, $for) {
                foreach ($customers as $customer) {
                    if ($this->generateForCustomer($customer, $for)) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    protected function nextInvoiceNumber(Tenant $tenant): string
    {
        $prefix = $tenant->invoice_prefix ?: 'INV';
        $ym = now()->format('Ym');
        $count = Invoice::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count() + 1;

        return sprintf('%s/%s/%05d', $prefix, $ym, $count);
    }
}
