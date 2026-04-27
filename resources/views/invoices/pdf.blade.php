<!doctype html>
<html lang="id"><head><meta charset="utf-8"><title>{{ $invoice->invoice_no }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
    .header-table { width: 100%; border-collapse: collapse; border-bottom: 2px solid #3b7ddd; }
    .header-table td { padding: 6px 0; vertical-align: top; }
    .biz-name { font-size: 18px; font-weight: bold; color: #3b7ddd; }
    .text-right { text-align: right; }
    .items { width: 100%; border-collapse: collapse; margin-top: 16px; }
    .items th, .items td { border-bottom: 1px solid #eee; padding: 8px; text-align: left; }
    .items th { background: #f5f7fb; }
    .items .total td { font-weight: bold; font-size: 14px; border-top: 2px solid #3b7ddd; border-bottom: 0; }
    .status { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
    .status-paid { background: #1cbb8c; color: #fff; }
    .status-unpaid { background: #fcb92c; color: #3a2200; }
    .status-overdue { background: #dc3545; color: #fff; }
    .status-cancelled { background: #6c757d; color: #fff; }
</style></head><body>
<table class="header-table">
    <tbody>
        <tr>
            <td>
                <div class="biz-name">{{ $tenant->business_name ?: $tenant->name }}</div>
                <div>{{ $tenant->business_address }}</div>
                <div>{{ $tenant->business_phone }}</div>
            </td>
            <td class="text-right">
                <div style="font-size:18px;font-weight:bold">INVOICE</div>
                <div>{{ $invoice->invoice_no }}</div>
                <div>Tgl: {{ $invoice->created_at->translatedFormat('d M Y') }}</div>
                <div class="status status-{{ $invoice->status }}">{{ $invoice->status }}</div>
            </td>
        </tr>
    </tbody>
</table>

<div style="margin-top:16px;">
    <strong>Untuk:</strong><br>
    {{ $invoice->customer?->name }}<br>
    <span style="color:#666;">{{ $invoice->customer?->phone }}<br>{{ $invoice->customer?->address }}</span>
</div>

<table class="items">
    <thead><tr><th>Deskripsi</th><th class="text-right">Jumlah</th></tr></thead>
    <tbody>
        <tr>
            <td>{{ $invoice->package?->name ?? 'Layanan Internet' }}<br><small style="color:#666;">Periode {{ $invoice->period_start->translatedFormat('d M Y') }} – {{ $invoice->period_end->translatedFormat('d M Y') }}</small></td>
            <td class="text-right">Rp {{ number_format($invoice->amount_idr, 0, ',', '.') }}</td>
        </tr>
        <tr class="total"><td>TOTAL</td><td class="text-right">Rp {{ number_format($invoice->amount_idr, 0, ',', '.') }}</td></tr>
    </tbody>
</table>

<div style="margin-top:24px;font-size:11px;color:#666;">
    <strong>Cara bayar:</strong> Hubungi {{ $tenant->business_phone }} atau gunakan link Pakasir yang dikirimkan via WhatsApp.<br>
    Jatuh tempo: <strong>{{ $invoice->due_date->translatedFormat('d M Y') }}</strong>.
</div>

</body></html>
