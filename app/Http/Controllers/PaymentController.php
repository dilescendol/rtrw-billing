<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $payments = Payment::with('invoice.customer')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('payments.index', compact('payments', 'status'));
    }

    public function create(Invoice $invoice)
    {
        return view('payments.create', compact('invoice'));
    }

    public function store(Request $request, Invoice $invoice)
    {
        if ($invoice->isPaid()) {
            return back()->withErrors(['invoice' => 'Invoice sudah lunas, tidak bisa mencatat pembayaran lagi.']);
        }

        $data = $request->validate([
            'amount_idr' => ['required', 'integer', 'min:1'],
            'method' => ['required', 'in:transfer,cash,other'],
            'paid_at' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'notes' => ['nullable', 'string'],
        ]);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('proofs', 'public');
        }

        DB::transaction(function () use ($invoice, $data, $proofPath) {
            Payment::create([
                'tenant_id' => $invoice->tenant_id,
                'invoice_id' => $invoice->id,
                'amount_idr' => $data['amount_idr'],
                'method' => $data['method'],
                'paid_at' => $data['paid_at'],
                'reference' => $data['reference'] ?? null,
                'proof_path' => $proofPath,
                'notes' => $data['notes'] ?? null,
                'status' => Payment::STATUS_PENDING,
            ]);
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Pembayaran dicatat, menunggu verifikasi.');
    }

    public function verify(Payment $payment)
    {
        if ($payment->status === Payment::STATUS_VERIFIED) {
            return back()->with('info', 'Pembayaran sudah diverifikasi sebelumnya.');
        }

        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => Payment::STATUS_VERIFIED,
                'verified_by' => auth()->id(),
            ]);
            $invoice = $payment->invoice;
            if (! $invoice || $invoice->isPaid()) {
                return;
            }
            // Only mark the invoice paid once total verified payments cover it.
            $verifiedTotal = (int) Payment::where('invoice_id', $invoice->id)
                ->where('status', Payment::STATUS_VERIFIED)
                ->sum('amount_idr');
            if ($verifiedTotal >= (int) $invoice->amount_idr) {
                $invoice->update([
                    'status' => Invoice::STATUS_PAID,
                    'paid_at' => $payment->paid_at,
                    'payment_method' => $payment->method,
                ]);
            }
        });

        return back()->with('success', 'Pembayaran diverifikasi.');
    }

    public function reject(Payment $payment)
    {
        $payment->update([
            'status' => Payment::STATUS_REJECTED,
            'verified_by' => auth()->id(),
        ]);

        return back()->with('success', 'Pembayaran ditolak.');
    }
}
