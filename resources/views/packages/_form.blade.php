@csrf
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Nama Paket <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $package->name ?? '') }}" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Aktif</label>
        <select name="is_active" class="form-select">
            <option value="1" @selected(old('is_active', $package->is_active ?? true) == 1)>Aktif</option>
            <option value="0" @selected(old('is_active', $package->is_active ?? true) == 0)>Nonaktif</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Harga (IDR) <span class="text-danger">*</span></label>
        <input type="number" name="price_idr" value="{{ old('price_idr', $package->price_idr ?? '') }}" class="form-control" required min="0">
    </div>
    <div class="col-md-4">
        <label class="form-label">Kecepatan (Mbps)</label>
        <input type="number" name="speed_mbps" value="{{ old('speed_mbps', $package->speed_mbps ?? '') }}" class="form-control" min="0">
    </div>
    <div class="col-md-4">
        <label class="form-label">MikroTik Profile</label>
        <input type="text" name="mikrotik_profile" value="{{ old('mikrotik_profile', $package->mikrotik_profile ?? '') }}" class="form-control" placeholder="mis. 10M">
    </div>
    <div class="col-12">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" rows="2" class="form-control">{{ old('description', $package->description ?? '') }}</textarea>
    </div>
</div>
<div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Simpan</button>
    <a href="{{ route('packages.index') }}" class="btn btn-light">Batal</a>
</div>
