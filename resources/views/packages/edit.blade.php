@extends('layouts.app')
@section('title', 'Edit Paket')
@section('content')
<h3 class="mb-3">Edit Paket: {{ $package->name }}</h3>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('packages.update', $package) }}">@method('PUT')@include('packages._form')</form>
</div></div>
@endsection
