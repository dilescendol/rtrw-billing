@extends('layouts.superadmin')
@section('title', 'Plans')
@section('content')
<h3 class="mb-3">Paket Subscription</h3>
<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead><tr>
                <th>Kode</th><th>Nama</th><th class="text-end">Harga / bln</th>
                <th class="text-end">Max Pelanggan</th><th>Mikrotik</th><th>WhatsApp</th>
                <th>PDF Invoice</th><th>Aktif</th>
            </tr></thead>
            <tbody>
            @foreach($plans as $p)
                <tr>
                    <td><code>{{ $p->code }}</code></td>
                    <td>{{ $p->name }}</td>
                    <td class="text-end">Rp {{ number_format($p->price_idr, 0, ',', '.') }}</td>
                    <td class="text-end">{{ $p->max_customers ?: '∞' }}</td>
                    <td>@if($p->allow_mikrotik)<i class="bi bi-check-circle text-success"></i>@else<i class="bi bi-x-circle text-muted"></i>@endif</td>
                    <td>@if($p->allow_whatsapp)<i class="bi bi-check-circle text-success"></i>@else<i class="bi bi-x-circle text-muted"></i>@endif</td>
                    <td>@if($p->allow_pdf_invoice)<i class="bi bi-check-circle text-success"></i>@else<i class="bi bi-x-circle text-muted"></i>@endif</td>
                    <td>@if($p->is_active)<span class="badge bg-success">Ya</span>@else<span class="badge bg-secondary">Tidak</span>@endif</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
