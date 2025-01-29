@extends('layouts.master')
@section('title')
Segments
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

<style>
    .table th, .table td {
    vertical-align: middle;
}

.table .form-control {
    border-radius: 0;
    box-shadow: none;
    font-size: 14px;
}

.table th {
    background-color: #f7f7f7;
    font-weight: bold;
}

.table-hover tbody tr:hover {

}

.table td i {
    font-size: 18px;
    margin-right: 10px;
}

.table-responsive {
    margin-bottom: 1.5rem;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.font-size-14 {
    font-size: 14px !important;
}

.font-weight-bold {
    font-weight: 700 !important;
}
</style>
@endsection

@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0 card-title">Segments </h4>
                 <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Configuration</a></li>
                        <li class="breadcrumb-item active">Segments</li>
                    </ol>
                </div>

             </div>
    </div>
</div>

<div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <livewire:company.company-manager />
                </div>
            </div>
        </div>
</div>

@endsection

@section('script')
<!--jquery cdn-->
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/sweetalerts.init.js') }}"></script>
<script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/modal.init.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
