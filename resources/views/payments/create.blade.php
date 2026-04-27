@extends('layouts.app')
@section('title', 'Catat Pembayaran')
@section('content')
<h3 class="mb-3">Catat Pembayaran Manual</h3>
<div class="card"><div class="card-body">
    <p class="text-muted">Untuk invoice <strong>{{ $invoice->invoice_no }}</strong> · {{ $invoice->customer?->name }} · Rp {{ number_format($invoice->amount_idr, 0, ',', '.') }}</p>
    <form method="POST" action="{{ route('payments.store', $invoice) }}" enctype="multipart/form-data">@csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Jumlah (IDR)</label>
                <input type="number" name="amount_idr" value="{{ $invoice->amount_idr }}" class="form-control" required min="1">
            </div>
            <div class="col-md-4">
                <label class="form-label">Metode</label>
                <select name="method" class="form-select" required>
                    <option value="transfer">Transfer</option>
                    <option value="cash">Tunai</option>
                    <option value="other">Lainnya</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal Bayar</label>
                <input type="date" name="paid_at" value="{{ now()->format('Y-m-d') }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Referensi</label>
                <input type="text" name="reference" class="form-control" placeholder="No. transaksi / catatan">
            </div>
            <div class="col-md-6">
                <label class="form-label">Bukti Transfer (opsional)</label>
                <input type="file" name="proof" class="form-control" accept="image/*,.pdf">
            </div>
            <div class="col-12">
                <label class="form-label">Catatan</label>
                <textarea name="notes" rows="2" class="form-control"></textarea>
            </div>
        </div>
        <div class="mt-3"><button class="btn btn-primary">Simpan</button> <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-light">Batal</a></div>
    </form>
</div></div>
@endsection
