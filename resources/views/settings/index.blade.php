@extends('layouts.app')
@section('title', 'Pengaturan')
@section('content')
<h3 class="mb-3">Pengaturan</h3>
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#biz">Bisnis</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#pakasir">Pakasir</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#mikrotik">MikroTik</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#wa">WhatsApp</button></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="biz">
        <div class="card"><div class="card-body">
            <form method="POST" action="{{ route('settings.business') }}">@csrf
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nama Tenant</label><input name="name" class="form-control" value="{{ old('name', $tenant->name) }}" required></div>
                    <div class="col-md-6"><label class="form-label">Nama Usaha</label><input name="business_name" class="form-control" value="{{ old('business_name', $tenant->business_name) }}"></div>
                    <div class="col-md-4"><label class="form-label">No. WhatsApp</label><input name="business_phone" class="form-control" value="{{ old('business_phone', $tenant->business_phone) }}"></div>
                    <div class="col-md-4"><label class="form-label">Prefix Invoice</label><input name="invoice_prefix" class="form-control" value="{{ old('invoice_prefix', $tenant->invoice_prefix) }}"></div>
                    <div class="col-md-4"><label class="form-label">Tgl Jatuh Tempo Default</label><input type="number" min="1" max="28" name="default_due_day" class="form-control" value="{{ old('default_due_day', $tenant->default_due_day) }}" required></div>
                    <div class="col-12"><label class="form-label">Alamat</label><textarea name="business_address" rows="2" class="form-control">{{ old('business_address', $tenant->business_address) }}</textarea></div>
                </div>
                <div class="mt-3"><button class="btn btn-primary">Simpan</button></div>
            </form>
        </div></div>
    </div>

    <div class="tab-pane fade" id="pakasir">
        <div class="card"><div class="card-body">
            <p class="text-muted small">Pakasir milik <strong>Anda</strong> — pemasukan billing pelanggan langsung masuk ke akun Pakasir Anda. URL webhook untuk Pakasir:
                <code>{{ route('webhooks.pakasir.tenant', $tenant) }}</code></p>
            <form method="POST" action="{{ route('settings.pakasir') }}">@csrf
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Project (slug)</label><input name="pakasir_project" class="form-control" value="{{ old('pakasir_project', $tenant->pakasir_project) }}"></div>
                    <div class="col-md-6"><label class="form-label">API Key</label><input name="pakasir_api_key" class="form-control" placeholder="{{ $tenant->pakasir_api_key ? '••••••• (sudah diset)' : '' }}"></div>
                    <div class="col-md-6"><label class="form-label">Webhook Signature</label><input name="pakasir_signature" class="form-control" placeholder="{{ $tenant->pakasir_signature ? '••••••• (sudah diset)' : 'isi nilai signature dari dashboard Pakasir' }}"></div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="pakasir_enabled" value="1" id="pakasir_enabled" @checked($tenant->pakasir_enabled)>
                            <label class="form-check-label" for="pakasir_enabled">Aktifkan Pakasir untuk pembayaran pelanggan</label>
                        </div>
                    </div>
                </div>
                <div class="mt-3"><button class="btn btn-primary">Simpan</button></div>
            </form>
        </div></div>
    </div>

    <div class="tab-pane fade" id="mikrotik">
        <div class="card"><div class="card-body">
            <p class="text-muted small">Atur kredensial MikroTik untuk auto-create PPPoE & auto-isolir.</p>
            <form method="POST" action="{{ route('settings.mikrotik') }}">@csrf
                <div class="row g-3">
                    <div class="col-md-5"><label class="form-label">Host</label><input name="mikrotik_host" class="form-control" value="{{ old('mikrotik_host', $tenant->mikrotik_host) }}" placeholder="103.123.45.67"></div>
                    <div class="col-md-2"><label class="form-label">Port</label><input type="number" name="mikrotik_port" class="form-control" value="{{ old('mikrotik_port', $tenant->mikrotik_port ?? 8728) }}"></div>
                    <div class="col-md-5"><label class="form-label">User</label><input name="mikrotik_user" class="form-control" value="{{ old('mikrotik_user', $tenant->mikrotik_user) }}"></div>
                    <div class="col-md-6"><label class="form-label">Password</label><input name="mikrotik_password" class="form-control" placeholder="{{ $tenant->mikrotik_password ? '••••••• (sudah diset)' : '' }}"></div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="mikrotik_enabled" value="1" id="mikrotik_enabled" @checked($tenant->mikrotik_enabled)>
                            <label class="form-check-label" for="mikrotik_enabled">Aktifkan MikroTik</label>
                        </div>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2"><button class="btn btn-primary">Simpan</button></div>
            </form>
            <form method="POST" action="{{ route('settings.mikrotik.test') }}" class="mt-2">@csrf<button class="btn btn-outline-secondary btn-sm"><i class="bi bi-plug"></i> Test Koneksi</button></form>
        </div></div>
    </div>

    <div class="tab-pane fade" id="wa">
        <div class="card"><div class="card-body">
            <p class="text-muted small">Notifikasi WhatsApp via <a href="https://fonnte.com" target="_blank">Fonnte</a>.</p>
            <form method="POST" action="{{ route('settings.fonnte') }}">@csrf
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Token Fonnte</label><input name="fonnte_token" class="form-control" placeholder="{{ $tenant->fonnte_token ? '••••••• (sudah diset)' : '' }}"></div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="fonnte_enabled" value="1" id="fonnte_enabled" @checked($tenant->fonnte_enabled)>
                            <label class="form-check-label" for="fonnte_enabled">Aktifkan</label>
                        </div>
                    </div>
                </div>
                <div class="mt-3"><button class="btn btn-primary">Simpan</button></div>
            </form>
            <form method="POST" action="{{ route('settings.fonnte.test') }}" class="mt-2 row g-2">@csrf
                <div class="col-md-5"><input name="target" class="form-control form-control-sm" placeholder="Tes ke nomor WA, mis. 0812..."></div>
                <div class="col-md-3"><button class="btn btn-outline-secondary btn-sm w-100"><i class="bi bi-send"></i> Tes Kirim</button></div>
            </form>
        </div></div>
    </div>
</div>
@endsection
