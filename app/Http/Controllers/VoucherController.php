<?php

namespace App\Http\Controllers;

use App\Models\NasDevice;
use App\Models\Package;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $batch = trim((string) $request->query('batch', ''));
        $status = trim((string) $request->query('status', ''));

        $vouchers = Voucher::with('package')
            ->when($batch !== '', fn ($q) => $q->where('batch_code', $batch))
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $batches = Voucher::select('batch_code', DB::raw('count(*) as total'), DB::raw('min(created_at) as created_at'))
            ->whereNotNull('batch_code')
            ->groupBy('batch_code')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('vouchers.index', compact('vouchers', 'batches', 'batch', 'status'));
    }

    public function create()
    {
        $packages = Package::where('is_active', true)
            ->where('type', Package::TYPE_HOTSPOT)
            ->orderBy('name')->get();
        $nasDevices = NasDevice::where('is_active', true)->orderBy('name')->get();

        return view('vouchers.create', compact('packages', 'nasDevices'));
    }

    public function store(Request $request)
    {
        $tenantId = app('current_tenant_id');

        $data = $request->validate([
            'package_id' => ['nullable', "exists:packages,id,tenant_id,{$tenantId}"],
            'nas_device_id' => ['nullable', "exists:nas_devices,id,tenant_id,{$tenantId}"],
            'count' => ['required', 'integer', 'min:1', 'max:500'],
            'price_idr' => ['nullable', 'integer', 'min:0'],
            'profile' => ['nullable', 'string', 'max:64'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:43200'],
            'code_length' => ['nullable', 'integer', 'min:5', 'max:16'],
        ]);

        $package = ! empty($data['package_id']) ? Package::find($data['package_id']) : null;
        $batch = Voucher::generateBatchCode();
        $count = (int) $data['count'];
        $codeLength = (int) ($data['code_length'] ?? 8);

        $existing = Voucher::pluck('code')->all();
        $codes = [];
        while (count($codes) < $count) {
            $code = Voucher::generateCode($codeLength);
            if (! in_array($code, $existing) && ! in_array($code, $codes)) {
                $codes[] = $code;
            }
        }

        DB::transaction(function () use ($codes, $batch, $data, $package) {
            foreach ($codes as $code) {
                Voucher::create([
                    'package_id' => $data['package_id'] ?? null,
                    'nas_device_id' => $data['nas_device_id'] ?? null,
                    'batch_code' => $batch,
                    'code' => $code,
                    'price_idr' => $data['price_idr'] ?? ($package?->price_idr ?? 0),
                    'profile' => $data['profile'] ?? $package?->mikrotik_profile,
                    'duration_minutes' => $data['duration_minutes'] ?? $package?->duration_minutes,
                    'status' => Voucher::STATUS_AVAILABLE,
                ]);
            }
        });

        return redirect()->route('vouchers.index', ['batch' => $batch])
            ->with('success', "Berhasil generate {$count} voucher (batch {$batch}).");
    }

    public function destroyBatch(Request $request)
    {
        $request->validate([
            'batch_code' => ['required', 'string', 'max:32'],
        ]);
        $deleted = Voucher::where('batch_code', $request->string('batch_code'))
            ->whereIn('status', [Voucher::STATUS_AVAILABLE, Voucher::STATUS_EXPIRED])
            ->delete();

        return back()->with('success', "Batch dihapus ({$deleted} voucher belum terpakai).");
    }

    public function print(Request $request)
    {
        $batch = $request->string('batch')->toString();
        abort_unless($batch !== '', 404);

        $vouchers = Voucher::with('package')->where('batch_code', $batch)->orderBy('code')->get();

        return view('vouchers.print', compact('vouchers', 'batch'));
    }
}
