@extends('layouts.app')
@section('title', 'Buat Invoice')
@section('content')
<h3 class="mb-3">Buat Invoice Manual</h3>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('invoices.store') }}">@csrf
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Pelanggan</label>
            <select name="customer_id" class="form-select" required>
                <option value="">— pilih pelanggan —</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->code }} · {{ $c->name }} @if($c->package)(Rp {{ number_format($c->package->price_idr, 0, ',', '.') }})@endif</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Periode</label>
            <input type="month" name="period" value="{{ now()->format('Y-m') }}" class="form-control" required>
        </div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Buat Invoice</button> <a href="{{ route('invoices.index') }}" class="btn btn-light">Batal</a></div>
</form>
</div></div>
@endsection
