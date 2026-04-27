<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Services\InvoiceGenerator;
use App\Services\PakasirService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $q = $request->string('q')->toString();

        $invoices = Invoice::with('customer', 'package')
            ->when($status, fn ($qb) => $qb->where('status', $status))
            ->when($q, fn ($qb) => $qb->where(function ($x) use ($q) {
                $x->where('invoice_no', 'like', "%{$q}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$q}%"));
            }))
            ->latest('due_date')
            ->paginate(20)
            ->withQueryString();

        return view('invoices.index', compact('invoices', 'status', 'q'));
    }

    public function create()
    {
        $customers = Customer::with('package')->orderBy('name')->get();

        return view('invoices.create', compact('customers'));
    }

    public function store(Request $request, InvoiceGenerator $generator)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'period' => ['required', 'date_format:Y-m'],
        ]);

        $customer = Customer::findOrFail($data['customer_id']);
        $for = CarbonImmutable::createFromFormat('Y-m', $data['period'])->startOfMonth();
        $invoice = $generator->generateForCustomer($customer, $for);

        if (! $invoice) {
            return back()->withErrors(['customer_id' => 'Invoice untuk periode ini sudah ada atau pelanggan tidak memiliki paket.'])->withInput();
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice dibuat.');
    }

    public function generateMonth(Request $request, InvoiceGenerator $generator)
    {
        $tenant = app('current_tenant');
        $request->validate(['period' => ['required', 'date_format:Y-m']]);
        $for = CarbonImmutable::createFromFormat('Y-m', $request->input('period'))->startOfMonth();
        $count = $generator->generateForTenant($tenant, $for);

        return back()->with('success', "Berhasil generate {$count} invoice baru.");
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('customer', 'package', 'payments');

        return view('invoices.show', compact('invoice'));
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load('customer', 'package', 'tenant');
        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoice, 'tenant' => $invoice->tenant]);
        $filename = preg_replace('/[^A-Za-z0-9_-]+/', '-', $invoice->invoice_no).'.pdf';

        return $pdf->stream($filename);
    }

    public function pay(Invoice $invoice)
    {
        $tenant = app('current_tenant');
        if ($invoice->isPaid()) {
            return back()->with('info', 'Invoice sudah lunas.');
        }
        $service = PakasirService::forTenant($tenant);
        if (! $service->isConfigured()) {
            return back()->withErrors(['pakasir' => 'Pakasir belum dikonfigurasi di Pengaturan.']);
        }

        $orderId = $invoice->pakasir_order_id ?: PakasirService::generateOrderId('INV-'.$tenant->id);
        $url = $service->buildCheckoutUrl(
            $orderId,
            (int) $invoice->amount_idr,
            route('invoices.show', $invoice)
        );
        $invoice->update(['pakasir_order_id' => $orderId, 'pakasir_payment_url' => $url]);

        return redirect($url);
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->isPaid()) {
            return back()->withErrors(['invoice' => 'Invoice yang lunas tidak dapat dihapus.']);
        }
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice dihapus.');
    }
}
