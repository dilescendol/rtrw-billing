@extends('layouts.app')
@section('title', 'Tambah RADIUS Server')
@section('content')
<h3 class="mb-3">Tambah RADIUS Server</h3>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('radius.store') }}">
        @include('radius._form')
    </form>
</div></div>
@endsection
