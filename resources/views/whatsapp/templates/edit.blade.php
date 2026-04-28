@extends('layouts.app')
@section('title', 'Edit Template — '.\App\Models\WhatsappTemplate::eventLabel($event))

@section('content')
<a href="{{ route('whatsapp.templates.index') }}" class="btn btn-link p-0 mb-2">&larr; Kembali</a>
<h1 class="h4 mb-3">{{ \App\Models\WhatsappTemplate::eventLabel($event) }}</h1>

<div class="row g-3">
    <div class="col-lg-7">
        <form method="POST" action="{{ route('whatsapp.templates.update', $event) }}">
            @csrf @method('PUT')
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nama (opsional)</label>
                        <input class="form-control" name="name" value="{{ old('name', $template->name) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pesan</label>
                        <textarea class="form-control @error('message') is-invalid @enderror" name="message" rows="9">{{ old('message', $template->message ?? \App\Models\WhatsappTemplate::DEFAULTS[$event] ?? '') }}</textarea>
                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                            {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Aktifkan template ini</label>
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                </div>
            </div>
        </form>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">Tes kirim ke nomor saya</div>
            <div class="card-body">
                <form method="POST" action="{{ route('whatsapp.templates.test') }}">
                    @csrf
                    <input type="hidden" name="event" value="{{ $event }}">
                    <div class="mb-3">
                        <label class="form-label">Nomor tujuan</label>
                        <input class="form-control" name="phone" placeholder="08xx atau 62xx">
                    </div>
                    <button class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-send"></i> Kirim tes
                    </button>
                </form>
                <hr>
                <small class="text-muted">Tes mengirim pesan menggunakan placeholder dari pelanggan pertama jika ada,
                    serta menggunakan template yang sedang tersimpan (bukan yang diketik di form di kiri).</small>
            </div>
        </div>
    </div>
</div>
@endsection
