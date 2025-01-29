@extends('layouts.master')
@section('title')
   Bank Book
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
                                <h4 class="mb-sm-0 card-title">Bank Book</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Accounting</a></li>
                                        <li class="breadcrumb-item active">Bank Book</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>

                    <livewire:accounting.bankbook.summaryboxbank />


<div class="card1">
            <div class="card-body1">
                      <div class="row">
                                        <div class="col-lg-12">
                                            <div class="nav nav-pills nav-customs nav-danger nav-border-bottom nav-border-bottom" role="tablist" aria-orientation="vertical">
                                                <a class="nav-link  show active" id="custom-v-pills-profile-tab" data-bs-toggle="pill" href="#custom-v-pills-types" role="tab" aria-controls="custom-v-pills-profile" aria-selected="true" tabindex="-1">
                                                    Bank Receipts
                                                </a>
                                                <a class="nav-link" id="custom-v-pills-profile-tab" data-bs-toggle="pill" href="#custom-v-pills-messages" role="tab" aria-controls="custom-v-pills-profile" aria-selected="false" tabindex="-1">
                                                   Bank Payments
                                                </a>



                                            </div>
                                        </div> <!-- end col-->
                                </div>

                                <div class="row">
                                        <div class="col-lg-12">
                                            <div class="tab-content text-muted">

                                                <div class="tab-pane fade  active show" id="custom-v-pills-types" role="tabpanel" aria-labelledby="custom-v-pills-types-tab">
                                                     <livewire:accounting.bankbook.bankreceipt  :key="'tab3-component'" />
                                                </div>
                                                <!--end tab-pane-->

                                                <div class="tab-pane fade" id="custom-v-pills-messages" role="tabpanel" aria-labelledby="custom-v-pills-messages-tab">
                                                        <livewire:accounting.bankbook.bankbookpayment  :key="'tab2-component'" />
                                                </div>
                                                <!--end tab-pane-->

                                            </div>
                                        </div> <!-- end col-->
                                    </div>
                                </div>


                                    </div>   </div>

@endsection

@section('script')
<!--jquery cdn-->
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/sweetalerts.init.js') }}"></script>
<script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
<!--<script src="https://cdn.lordicon.com/libs/mssddfmo/lord-icon-2.1.0.js"></script>-->
<script src="{{ URL::asset('build/js/pages/modal.init.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
