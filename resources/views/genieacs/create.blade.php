@extends('layouts.app')
@section('title', 'Tambah GenieACS Server')
@section('content')
<h3 class="mb-3">Tambah GenieACS Server</h3>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('genieacs.store') }}">
        @include('genieacs._form')
    </form>
</div></div>
@endsection
