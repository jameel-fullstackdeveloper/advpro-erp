@extends('layouts.master')
@section('title')
    @lang('translation.dashboards')
@endsection

@section('css')

@endsection

@section('content')

<div class="row">
    <div class="col">
    <h1>Permission Details</h1>
    <p>Name: {{ $permission->name }}</p>
    <a href="{{ route('permissions.index') }}">Back to Permissions</a>
    </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
