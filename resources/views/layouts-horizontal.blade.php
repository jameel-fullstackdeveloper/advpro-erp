@extends('layouts.layouts-horizontal')
@section('title') @lang('translation.horizontal') @endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Layouts @endslot
        @slot('title') Horizontal @endslot
    @endcomponent
   
    
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
