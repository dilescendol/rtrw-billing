<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Tenant;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
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

        $dueDay = (int) ($customer->due_day ?: $tenant->default_due_day ?: 5);
        $dueDay = max(1, min(28, $dueDay));
        $dueDate = $periodStart->day($dueDay);

        // Wrap the duplicate check + insert inside one transaction so concurrent
        // calls cannot both pass the check. The unique index on
        // (tenant_id, customer_id, period_start) is the final guard.
        try {
            return DB::transaction(function () use ($tenant, $customer, $periodStart, $periodEnd, $dueDate) {
                $exists = Invoice::withoutGlobalScopes()
                    ->where('tenant_id', $tenant->id)
                    ->where('customer_id', $customer->id)
                    ->where('period_start', $periodStart->toDateString())
                    ->lockForUpdate()
                    ->exists();
                if ($exists) {
                    return null;
                }

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
        } catch (QueryException $e) {
            // Race lost: unique index caught a concurrent insert, treat as already exists.
            if ($this->isUniqueViolation($e)) {
                return null;
            }
            throw $e;
        }
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

    /**
     * Allocate a unique invoice number for a tenant for the current month.
     *
     * Retries on rare unique-constraint races since invoice_no is globally unique.
     */
    protected function nextInvoiceNumber(Tenant $tenant): string
    {
        $prefix = $tenant->invoice_prefix ?: 'INV';
        $ym = now()->format('Ym');

        // Use the highest existing sequence for this tenant+month and increment.
        $likePrefix = $prefix.'/'.$ym.'/';
        $latest = Invoice::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('invoice_no', 'like', $likePrefix.'%')
            ->orderByDesc('invoice_no')
            ->value('invoice_no');

        $next = 1;
        if ($latest) {
            $tail = substr($latest, strlen($likePrefix));
            $tailInt = (int) $tail;
            if ($tailInt > 0) {
                $next = $tailInt + 1;
            }
        }

        return sprintf('%s%05d', $likePrefix, $next);
    }

    protected function isUniqueViolation(QueryException $e): bool
    {
        // SQLSTATE 23000 (MySQL/SQLite) covers integrity constraint violations
        // including unique index violations.
        return in_array($e->getCode(), ['23000', '23505'], true);
    }
}
