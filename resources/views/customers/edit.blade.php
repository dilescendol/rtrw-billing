@extends('layouts.app')
@section('title', 'Edit Pelanggan')
@section('content')
<h3 class="mb-3">Edit Pelanggan: {{ $customer->name }}</h3>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('customers.update', $customer) }}">
    @method('PUT')
    @include('customers._form')
</form>
</div></div>
<form method="POST" action="{{ route('customers.destroy', $customer) }}" class="mt-3" onsubmit="return confirm('Hapus pelanggan ini?')">
    @csrf @method('DELETE')
    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Hapus pelanggan</button>
</form>
@endsection
