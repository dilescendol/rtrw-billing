@csrf
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Kode Pelanggan</label>
        <input type="text" name="code" value="{{ old('code', $customer->code ?? '') }}" class="form-control" placeholder="auto-generate jika kosong">
    </div>
    <div class="col-md-8">
        <label class="form-label">Nama <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $customer->name ?? '') }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Paket</label>
        <select name="package_id" class="form-select">
            <option value="">— pilih paket —</option>
            @foreach($packages as $p)
                <option value="{{ $p->id }}" @selected(old('package_id', $customer->package_id ?? null) == $p->id)>
                    {{ $p->name }} (Rp {{ number_format($p->price_idr, 0, ',', '.') }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select">
            @foreach(['active'=>'Aktif','isolated'=>'Diisolir','terminated'=>'Berhenti'] as $k=>$v)
                <option value="{{ $k }}" @selected(old('status', $customer->status ?? 'active') === $k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Tgl Jatuh Tempo</label>
        <input type="number" min="1" max="28" name="due_day" value="{{ old('due_day', $customer->due_day ?? 5) }}" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">No. Telepon / WA</label>
        <input type="text" name="phone" value="{{ old('phone', $customer->phone ?? '') }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $customer->email ?? '') }}" class="form-control">
    </div>
    <div class="col-12">
        <label class="form-label">Alamat</label>
        <textarea name="address" rows="2" class="form-control">{{ old('address', $customer->address ?? '') }}</textarea>
    </div>

    <div class="col-md-6">
        <label class="form-label">PPPoE Username</label>
        <input type="text" name="pppoe_username" value="{{ old('pppoe_username', $customer->pppoe_username ?? '') }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">PPPoE Password</label>
        <input type="text" name="pppoe_password" value="{{ old('pppoe_password', $customer->pppoe_password ?? '') }}" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Tgl Pasang</label>
        <input type="date" name="installed_at" value="{{ old('installed_at', optional($customer->installed_at ?? null)->format('Y-m-d')) }}" class="form-control">
    </div>
    <div class="col-12">
        <label class="form-label">Catatan</label>
        <textarea name="notes" rows="2" class="form-control">{{ old('notes', $customer->notes ?? '') }}</textarea>
    </div>
</div>
<div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Simpan</button>
    <a href="{{ route('customers.index') }}" class="btn btn-light">Batal</a>
</div>
