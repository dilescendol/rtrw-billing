@extends('layouts.app')
@section('title', 'Tambah Pelanggan')
@section('content')
<h3 class="mb-3">Tambah Pelanggan</h3>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('customers.store') }}">
    @include('customers._form')
</form>
</div></div>
@endsection
