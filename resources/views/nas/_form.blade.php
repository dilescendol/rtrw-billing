@csrf
@php($d = $device ?? null)
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nama <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $d?->name) }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Type</label>
        <select name="type" class="form-select">
            <option value="mikrotik" @selected(old('type', $d?->type ?? 'mikrotik')==='mikrotik')>MikroTik</option>
            <option value="other" @selected(old('type', $d?->type)==='other')>Lainnya</option>
        </select>
    </div>
    <div class="col-md-3 d-flex align-items-center pt-4">
        <div class="form-check form-switch me-3">
            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default" @checked(old('is_default', $d?->is_default))>
            <label class="form-check-label" for="is_default">Default</label>
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $d?->is_active ?? true))>
            <label class="form-check-label" for="is_active">Aktif</label>
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Host / IP <span class="text-danger">*</span></label>
        <input type="text" name="host" value="{{ old('host', $d?->host) }}" class="form-control" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">Port API</label>
        <input type="number" name="api_port" value="{{ old('api_port', $d?->api_port ?? 8728) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Identity (opsional)</label>
        <input type="text" name="identity" value="{{ old('identity', $d?->identity) }}" class="form-control" placeholder="diisi otomatis saat tes koneksi">
    </div>

    <div class="col-md-6">
        <label class="form-label">User API <span class="text-danger">*</span></label>
        <input type="text" name="api_user" value="{{ old('api_user', $d?->api_user) }}" class="form-control" required autocomplete="off">
    </div>
    <div class="col-md-6">
        <label class="form-label">Password API @if($d) <span class="small text-muted">(kosongkan jika tidak ganti)</span> @endif</label>
        <input type="password" name="api_password" class="form-control" autocomplete="new-password">
    </div>
</div>

<div class="mt-4 d-flex justify-content-end">
    <a href="{{ route('nas.index') }}" class="btn btn-light me-2">Batal</a>
    <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
</div>
