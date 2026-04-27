<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\CarbonImmutable;

class DashboardController extends Controller
{
    public function index()
    {
        $tenant = app('current_tenant');

        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', Customer::STATUS_ACTIVE)->count();
        $isolatedCustomers = Customer::where('status', Customer::STATUS_ISOLATED)->count();

        $unpaidThisMonth = Invoice::where('status', Invoice::STATUS_UNPAID)->sum('amount_idr');
        $overdue = Invoice::where(function ($q) {
            $q->where('status', Invoice::STATUS_OVERDUE)
                ->orWhere(function ($inner) {
                    $inner->where('status', Invoice::STATUS_UNPAID)->where('due_date', '<', now());
                });
        })->count();

        $thisMonthRevenue = Invoice::where('status', Invoice::STATUS_PAID)
            ->whereBetween('paid_at', [
                CarbonImmutable::now()->startOfMonth(),
                CarbonImmutable::now()->endOfMonth(),
            ])->sum('amount_idr');

        // Last 6 months revenue chart
        $months = collect(range(5, 0))->map(function ($i) {
            $start = CarbonImmutable::now()->subMonths($i)->startOfMonth();
            $end = $start->endOfMonth();
            $sum = Invoice::where('status', Invoice::STATUS_PAID)
                ->whereBetween('paid_at', [$start, $end])
                ->sum('amount_idr');

            return [
                'label' => $start->translatedFormat('M Y'),
                'value' => (int) $sum,
            ];
        });

        $recentInvoices = Invoice::with('customer')->latest()->limit(8)->get();
        $recentPayments = Payment::with('invoice.customer')->latest()->limit(6)->get();

        return view('dashboard.index', [
            'tenant' => $tenant,
            'totalCustomers' => $totalCustomers,
            'activeCustomers' => $activeCustomers,
            'isolatedCustomers' => $isolatedCustomers,
            'unpaidThisMonth' => $unpaidThisMonth,
            'overdue' => $overdue,
            'thisMonthRevenue' => $thisMonthRevenue,
            'months' => $months,
            'recentInvoices' => $recentInvoices,
            'recentPayments' => $recentPayments,
        ]);
    }
}
