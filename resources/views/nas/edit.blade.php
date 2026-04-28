@extends('layouts.app')
@section('title', 'Edit NAS')
@section('content')
<h3 class="mb-3">Edit NAS — {{ $device->name }}</h3>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('nas.update', $device) }}">
            @method('PUT')
            @include('nas._form')
        </form>
    </div>
</div>
@endsection
