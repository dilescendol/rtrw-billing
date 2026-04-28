@extends('layouts.portal')
@section('title', 'Tagihan')
@section('content')
<h3 class="mb-3">Tagihan Saya</h3>
<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead><tr>
                <th>Periode</th><th>No. Invoice</th><th>Jatuh Tempo</th><th class="text-end">Jumlah</th><th>Status</th><th></th>
            </tr></thead>
            <tbody>
            @forelse($invoices as $inv)
                <tr>
                    <td>{{ optional($inv->period_start)->format('M Y') }}</td>
                    <td>{{ $inv->invoice_no }}</td>
                    <td>{{ optional($inv->due_date)->format('d M Y') }}</td>
                    <td class="text-end">Rp {{ number_format($inv->amount_idr, 0, ',', '.') }}</td>
                    <td>
                        @if($inv->status === 'paid')<span class="badge bg-success">Lunas</span>
                        @elseif($inv->status === 'overdue')<span class="badge bg-danger">Lewat Tempo</span>
                        @elseif($inv->status === 'cancelled')<span class="badge bg-secondary">Dibatalkan</span>
                        @else<span class="badge bg-warning">Belum Bayar</span>@endif
                    </td>
                    <td><a href="{{ route('portal.invoices.show', $inv) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada tagihan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $invoices->links() }}</div>
@endsection
