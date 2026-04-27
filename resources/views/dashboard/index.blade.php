@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Dashboard</h3>
    <span class="text-muted small">{{ now()->translatedFormat('l, d F Y') }}</span>
</div>

<div class="row g-3">
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card">
            <div class="stat-icon bg-primary-soft"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-value">{{ number_format($totalCustomers) }}</div>
                <div class="stat-label">Total Pelanggan ({{ $activeCustomers }} aktif)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card">
            <div class="stat-icon bg-success-soft"><i class="bi bi-cash-coin"></i></div>
            <div>
                <div class="stat-value text-currency">Rp {{ number_format($thisMonthRevenue, 0, ',', '.') }}</div>
                <div class="stat-label">Pendapatan bulan ini</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card">
            <div class="stat-icon bg-warning-soft"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="stat-value text-currency">Rp {{ number_format($unpaidThisMonth, 0, ',', '.') }}</div>
                <div class="stat-label">Belum Dibayar</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card">
            <div class="stat-icon bg-danger-soft"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div class="stat-value">{{ number_format($overdue) }}</div>
                <div class="stat-label">Tagihan Telat / Overdue</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h6 class="card-title">Pendapatan 6 Bulan Terakhir</h6></div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between"><h6 class="card-title">Invoice Terbaru</h6><a href="{{ route('invoices.index') }}" class="small text-decoration-none">Lihat semua</a></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>No</th><th>Pelanggan</th><th>Periode</th><th class="text-end">Jumlah</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($recentInvoices as $inv)
                            <tr>
                                <td><a href="{{ route('invoices.show', $inv) }}" class="text-decoration-none">{{ $inv->invoice_no }}</a></td>
                                <td>{{ $inv->customer?->name }}</td>
                                <td class="small text-muted">{{ optional($inv->period_start)->translatedFormat('M Y') }}</td>
                                <td class="text-end text-currency">Rp {{ number_format($inv->amount_idr, 0, ',', '.') }}</td>
                                <td><span class="status-pill status-{{ $inv->status }}">{{ $inv->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Belum ada invoice.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h6 class="card-title">Pelanggan Diisolir</h6></div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="display-6 fw-bold">{{ $isolatedCustomers }}</div>
                        <div class="text-muted small">Tertutup karena belum bayar</div>
                    </div>
                    <i class="bi bi-shield-slash" style="font-size: 3rem; color: #dc3545; opacity: .3;"></i>
                </div>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header"><h6 class="card-title">Pembayaran Terbaru</h6></div>
            <div class="list-group list-group-flush">
                @forelse($recentPayments as $p)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">{{ $p->invoice?->customer?->name }}</div>
                            <div class="small text-muted">{{ $p->method }} · {{ optional($p->paid_at)->translatedFormat('d M Y') }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-semibold text-currency">Rp {{ number_format($p->amount_idr, 0, ',', '.') }}</div>
                            <span class="status-pill status-{{ $p->status }}">{{ $p->status }}</span>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-center text-muted py-4 small">Belum ada pembayaran.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('revenueChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($months->pluck('label')),
        datasets: [{
            label: 'Pendapatan',
            data: @json($months->pluck('value')),
            backgroundColor: 'rgba(59,125,221,.6)',
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: (v) => 'Rp ' + new Intl.NumberFormat('id').format(v) }
            }
        }
    }
});
</script>
@endpush
@endsection
