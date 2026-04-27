@extends('layouts.app')
@section('title', 'Tambah Paket')
@section('content')
<h3 class="mb-3">Tambah Paket</h3>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('packages.store') }}">@include('packages._form')</form>
</div></div>
@endsection
