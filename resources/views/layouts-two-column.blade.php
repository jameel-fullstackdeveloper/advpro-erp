@extends('layouts.layouts-two-column')
@section('title') @lang('translation.two-column') @endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Layouts @endslot
        @slot('title') Two Column @endslot
    @endcomponent
  
    
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
