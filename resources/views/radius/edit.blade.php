@extends('layouts.app')
@section('title', 'Edit RADIUS Server')
@section('content')
<h3 class="mb-3">Edit RADIUS — {{ $server->name }}</h3>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('radius.update', $server) }}">
        @method('PUT')
        @include('radius._form')
    </form>
</div></div>
@endsection
