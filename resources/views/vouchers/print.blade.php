<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Voucher batch {{ $batch }}</title>
    <style>
        @media print { @page { margin: 12mm; } }
        body { font-family: 'Helvetica', sans-serif; font-size: 11pt; margin: 0; padding: 16px; background: #fff; color: #111; }
        h1 { font-size: 14pt; margin: 0 0 4px; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 12px; }
        .v { border: 1px dashed #888; padding: 10px; text-align: center; }
        .v code { font-size: 14pt; font-weight: bold; letter-spacing: 1px; }
        .v small { display: block; color: #555; }
        .meta { color: #555; font-size: 9pt; }
        .actions { margin: 8px 0 0; }
    </style>
</head>
<body>
    <h1>Voucher Hotspot — Batch {{ $batch }}</h1>
    <div class="meta">{{ count($vouchers) }} kupon · dicetak {{ now()->format('d M Y H:i') }}</div>
    <div class="actions"><button onclick="window.print()">Print</button></div>

    <div class="grid">
        @foreach($vouchers as $v)
            <div class="v">
                <small>{{ $v->package?->name ?? '' }}</small>
                <code>{{ $v->code }}</code>
                <small>
                    @if($v->price_idr) Rp {{ number_format($v->price_idr, 0, ',', '.') }} · @endif
                    @if($v->duration_minutes) {{ $v->duration_minutes }} menit @endif
                </small>
            </div>
        @endforeach
    </div>
</body>
</html>
