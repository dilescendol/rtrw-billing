@extends('layouts.portal')
@section('title', 'Profil')
@section('content')
<h3 class="mb-3">Profil Pelanggan</h3>
<div class="row g-4">
    <div class="col-md-5">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Kode</div>
            <h5 class="mb-3">{{ $customer->code }}</h5>
            <div class="text-muted small">Paket</div>
            <h5 class="mb-3">{{ $customer->package?->name ?: '-' }}
                @if($customer->package?->speed_mbps)
                    <small class="text-muted">({{ $customer->package->speed_mbps }} Mbps)</small>
                @endif
            </h5>
            <div class="text-muted small">PPPoE Username</div>
            <code>{{ $customer->pppoe_username ?: '-' }}</code>
        </div></div>
    </div>
    <div class="col-md-7">
        <form method="POST" action="{{ route('portal.profile.update') }}" class="card">@csrf
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Nama (read-only)</label>
                    <input type="text" class="form-control" value="{{ $customer->name }}" disabled>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" rows="2" class="form-control">{{ old('address', $customer->address) }}</textarea>
                </div>
                <hr>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Password baru (opsional)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Konfirmasi password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
