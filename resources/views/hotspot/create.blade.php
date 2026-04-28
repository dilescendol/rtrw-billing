@extends('layouts.app')
@section('title', 'Tambah Hotspot User')
@section('content')
<h3 class="mb-3">Tambah Hotspot User</h3>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('hotspot.store') }}">
            @include('hotspot._form')
        </form>
    </div>
</div>
@endsection
