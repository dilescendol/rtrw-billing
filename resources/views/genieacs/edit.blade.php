@extends('layouts.app')
@section('title', 'Edit GenieACS Server')
@section('content')
<h3 class="mb-3">Edit GenieACS — {{ $server->name }}</h3>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('genieacs.update', $server) }}">
        @method('PUT')
        @include('genieacs._form')
    </form>
</div></div>
@endsection
