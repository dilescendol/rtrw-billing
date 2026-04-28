<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $customer = $request->user()->customer()->with('package')->first();
        $invoices = Invoice::where('customer_id', $customer->id)
            ->latest('period_start')
            ->limit(6)
            ->get();
        $unpaidTotal = Invoice::where('customer_id', $customer->id)
            ->whereIn('status', [Invoice::STATUS_UNPAID, Invoice::STATUS_OVERDUE])
            ->sum('amount_idr');
        $latestPayment = Payment::whereIn('invoice_id', $customer->invoices()->select('id'))
            ->latest('paid_at')
            ->first();

        return view('portal.dashboard', compact('customer', 'invoices', 'unpaidTotal', 'latestPayment'));
    }

    public function invoices(Request $request)
    {
        $customer = $request->user()->customer;
        $invoices = Invoice::where('customer_id', $customer->id)
            ->latest('period_start')
            ->paginate(15);

        return view('portal.invoices.index', compact('customer', 'invoices'));
    }

    public function invoiceShow(Request $request, Invoice $invoice)
    {
        $customer = $request->user()->customer;
        abort_unless((int) $invoice->customer_id === (int) $customer->id, 404);

        return view('portal.invoices.show', compact('customer', 'invoice'));
    }

    public function payments(Request $request)
    {
        $customer = $request->user()->customer;
        $payments = Payment::with('invoice')
            ->whereIn('invoice_id', $customer->invoices()->select('id'))
            ->latest('paid_at')
            ->paginate(15);

        return view('portal.payments.index', compact('customer', 'payments'));
    }

    public function profile(Request $request)
    {
        $customer = $request->user()->customer()->with('package')->first();

        return view('portal.profile', compact('customer'));
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        $customer = $user->customer;
        $customer->fill(array_filter([
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
        ], fn ($v) => $v !== null && $v !== ''))->save();

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $user->save();
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
