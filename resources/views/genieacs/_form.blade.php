@csrf
@php($s = $server ?? null)
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nama <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $s?->name) }}" class="form-control" required>
    </div>
    <div class="col-md-3 d-flex align-items-center pt-4">
        <div class="form-check form-switch me-3">
            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="gdef" @checked(old('is_default', $s?->is_default))>
            <label class="form-check-label" for="gdef">Default</label>
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="gact" @checked(old('is_active', $s?->is_active ?? true))>
            <label class="form-check-label" for="gact">Aktif</label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">NBI URL <span class="text-danger">*</span></label>
        <input type="url" name="nbi_url" value="{{ old('nbi_url', $s?->nbi_url) }}" class="form-control" placeholder="http://genieacs.example.com:7557" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Auth user (opsional)</label>
        <input type="text" name="auth_user" value="{{ old('auth_user', $s?->auth_user) }}" class="form-control" autocomplete="off">
    </div>
    <div class="col-md-6">
        <label class="form-label">Auth password @if($s) <span class="small text-muted">(kosongkan jika tidak ganti)</span> @endif</label>
        <input type="password" name="auth_password" class="form-control" autocomplete="new-password">
    </div>
</div>

<div class="mt-4 d-flex justify-content-end">
    <a href="{{ route('genieacs.index') }}" class="btn btn-light me-2">Batal</a>
    <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
</div>
