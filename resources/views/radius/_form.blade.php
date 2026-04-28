@csrf
@php($s = $server ?? null)
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nama <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $s?->name) }}" class="form-control" required>
    </div>
    <div class="col-md-3 d-flex align-items-center pt-4">
        <div class="form-check form-switch me-3">
            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="rdef" @checked(old('is_default', $s?->is_default))>
            <label class="form-check-label" for="rdef">Default</label>
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="ract" @checked(old('is_active', $s?->is_active ?? true))>
            <label class="form-check-label" for="ract">Aktif</label>
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">RADIUS host <span class="text-danger">*</span></label>
        <input type="text" name="host" value="{{ old('host', $s?->host) }}" class="form-control" required placeholder="radius.example.com">
    </div>
    <div class="col-md-3">
        <label class="form-label">Auth port</label>
        <input type="number" name="auth_port" value="{{ old('auth_port', $s?->auth_port ?? 1812) }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Acct port</label>
        <input type="number" name="acct_port" value="{{ old('acct_port', $s?->acct_port ?? 1813) }}" class="form-control">
    </div>

    <div class="col-md-12">
        <label class="form-label">Shared secret @if($s) <span class="small text-muted">(kosongkan jika tidak ganti)</span> @endif</label>
        <input type="password" name="shared_secret" class="form-control" autocomplete="new-password">
    </div>

    <div class="col-12"><hr class="my-2"><h6 class="mb-0">SQL Backend (rlm_sql)</h6></div>
    <div class="col-md-3">
        <label class="form-label">Driver <span class="text-danger">*</span></label>
        <select name="sql_driver" class="form-select">
            @foreach(['mysql','pgsql','sqlite','sqlsrv'] as $d)
                <option value="{{ $d }}" @selected(old('sql_driver', $s?->sql_driver ?? 'mysql')===$d)>{{ $d }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">SQL host</label>
        <input type="text" name="sql_host" value="{{ old('sql_host', $s?->sql_host) }}" class="form-control" placeholder="kosongkan untuk pakai host RADIUS di atas">
    </div>
    <div class="col-md-3">
        <label class="form-label">SQL port</label>
        <input type="number" name="sql_port" value="{{ old('sql_port', $s?->sql_port) }}" class="form-control" placeholder="3306">
    </div>

    <div class="col-md-4">
        <label class="form-label">Database</label>
        <input type="text" name="sql_database" value="{{ old('sql_database', $s?->sql_database) }}" class="form-control" placeholder="radius">
    </div>
    <div class="col-md-4">
        <label class="form-label">Username</label>
        <input type="text" name="sql_username" value="{{ old('sql_username', $s?->sql_username) }}" class="form-control" autocomplete="off">
    </div>
    <div class="col-md-4">
        <label class="form-label">Password @if($s) <span class="small text-muted">(kosongkan jika tidak ganti)</span> @endif</label>
        <input type="password" name="sql_password" class="form-control" autocomplete="new-password">
    </div>
</div>

<div class="mt-4 d-flex justify-content-end">
    <a href="{{ route('radius.index') }}" class="btn btn-light me-2">Batal</a>
    <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
</div>
