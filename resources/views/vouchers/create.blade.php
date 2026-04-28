@extends('layouts.app')
@section('title', 'Generate Voucher')
@section('content')
<h3 class="mb-3">Generate Voucher Hotspot</h3>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('vouchers.store') }}">@csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Paket Hotspot</label>
                    <select name="package_id" class="form-select">
                        <option value="">— Tanpa paket —</option>
                        @foreach($packages as $p)
                            <option value="{{ $p->id }}" data-price="{{ $p->price_idr }}" data-profile="{{ $p->mikrotik_profile }}" data-duration="{{ $p->duration_minutes }}" @selected(old('package_id')==$p->id)>{{ $p->name }} — Rp {{ number_format($p->price_idr, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                    <div class="small text-muted mt-1">Saat dipilih, harga / profile / durasi akan diisi otomatis dari paket.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">NAS (opsional)</label>
                    <select name="nas_device_id" class="form-select">
                        <option value="">— Tidak diatur —</option>
                        @foreach($nasDevices as $n)
                            <option value="{{ $n->id }}" @selected(old('nas_device_id')==$n->id)>{{ $n->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                    <input type="number" name="count" value="{{ old('count', 50) }}" min="1" max="500" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Panjang kode</label>
                    <input type="number" name="code_length" value="{{ old('code_length', 8) }}" min="5" max="16" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Harga per kupon</label>
                    <input type="number" name="price_idr" value="{{ old('price_idr') }}" class="form-control" placeholder="Ikut paket bila kosong">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Durasi (menit)</label>
                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes') }}" class="form-control" placeholder="Ikut paket bila kosong">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Profile (override)</label>
                    <input type="text" name="profile" value="{{ old('profile') }}" class="form-control" placeholder="Ikut paket bila kosong">
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <a href="{{ route('vouchers.index') }}" class="btn btn-light me-2">Batal</a>
                <button class="btn btn-primary"><i class="bi bi-magic me-1"></i>Generate</button>
            </div>
        </form>
    </div>
</div>
@endsection
