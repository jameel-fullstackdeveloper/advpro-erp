@extends('layouts.master')
@section('title')
    @lang('translation.dashboards')
@endsection

@section('css')

@endsection

@section('content')

<div class="row">
    <div class="col">
    <h1>Role: {{ $role->name }}</h1>

<h3>Permissions:</h3>
<ul>
    @forelse ($role->permissions as $permission)
        <li>{{ $permission->name }}</li>
    @empty
        <li>No permissions assigned to this role.</li>
    @endforelse
</ul>

<a href="{{ route('roles.index') }}">Back to Roles</a>
    </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
