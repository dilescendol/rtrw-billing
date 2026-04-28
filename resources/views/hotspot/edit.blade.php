@extends('layouts.app')
@section('title', 'Edit Hotspot User')
@section('content')
<h3 class="mb-3">Edit Hotspot User — {{ $hotspotUser->username }}</h3>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('hotspot.update', $hotspotUser) }}">
            @method('PUT')
            @include('hotspot._form')
        </form>
    </div>
</div>
@endsection
