@csrf
@php($u = $hotspotUser ?? null)
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Username <span class="text-danger">*</span></label>
        <input type="text" name="username" value="{{ old('username', $u?->username) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Password @if($u) <span class="small text-muted">(kosongkan jika tidak ganti)</span> @endif</label>
        <input type="text" name="password" value="{{ old('password') }}" class="form-control" placeholder="auto-generate jika kosong saat create">
    </div>

    <div class="col-md-6">
        <label class="form-label">NAS</label>
        <select name="nas_device_id" class="form-select">
            <option value="">— Tidak push ke NAS —</option>
            @foreach($nasDevices as $n)
                <option value="{{ $n->id }}" @selected(old('nas_device_id', $u?->nas_device_id)==$n->id)>{{ $n->name }} ({{ $n->host }})</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Paket Hotspot</label>
        <select name="package_id" class="form-select">
            <option value="">— Tanpa paket —</option>
            @foreach($packages as $p)
                <option value="{{ $p->id }}" @selected(old('package_id', $u?->package_id)==$p->id)>{{ $p->name }} — Rp {{ number_format($p->price_idr, 0, ',', '.') }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Profile (override)</label>
        <input type="text" name="profile" value="{{ old('profile', $u?->profile) }}" class="form-control" placeholder="default">
    </div>
    <div class="col-md-4">
        <label class="form-label">MAC Address</label>
        <input type="text" name="mac_address" value="{{ old('mac_address', $u?->mac_address) }}" class="form-control" placeholder="AA:BB:CC:DD:EE:FF">
    </div>
    <div class="col-md-4">
        <label class="form-label">Expired</label>
        <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $u?->expires_at?->format('Y-m-d\TH:i')) }}" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="active" @selected(old('status', $u?->status ?? 'active')==='active')>Aktif</option>
            <option value="disabled" @selected(old('status', $u?->status)==='disabled')>Nonaktif</option>
            <option value="expired" @selected(old('status', $u?->status)==='expired')>Expired</option>
        </select>
    </div>
    <div class="col-md-8">
        <label class="form-label">Catatan</label>
        <input type="text" name="comment" value="{{ old('comment', $u?->comment) }}" class="form-control">
    </div>
</div>

<div class="mt-4 d-flex justify-content-end">
    <a href="{{ route('hotspot.index') }}" class="btn btn-light me-2">Batal</a>
    <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
</div>
