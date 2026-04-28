@extends('layouts.app')
@section('title', 'Tambah NAS')
@section('content')
<h3 class="mb-3">Tambah NAS / Router</h3>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('nas.store') }}">
            @include('nas._form')
        </form>
    </div>
</div>
@endsection
