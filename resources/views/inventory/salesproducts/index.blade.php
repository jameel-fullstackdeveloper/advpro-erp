@extends('layouts.master')
@section('title')
    Sales Products (Finished Goods)
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

<div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0">Sales Products (Finished Goods)</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Inventory</a></li>
                        <li class="breadcrumb-item active">Sales Products</li>
                    </ol>
                </div>

            </div>
        </div>
</div>


<div class="row">
                                        <div class="col-lg-12">
                                            <div class="nav nav-pills nav-customs nav-danger nav-border-bottom nav-border-bottom" role="tablist" aria-orientation="vertical">
                                                <a class="nav-link  show active d-none"  id="custom-v-pills-profile-tab" data-bs-toggle="pill" href="#custom-v-pills-types" role="tab" aria-controls="custom-v-pills-profile" aria-selected="true" tabindex="-1">
                                                    Products
                                                </a>
                                                <a class="nav-link d-none" id="custom-v-pills-profile-tab" data-bs-toggle="pill" href="#custom-v-pills-messages" role="tab" aria-controls="custom-v-pills-profile" aria-selected="false" tabindex="-1">
                                                    Product Group
                                                </a>

                                            </div>
                                        </div> <!-- end col-->
                                </div>

                                <div class="row">
                                        <div class="col-lg-12">
                                            <div class="tab-content">

                                                <div class="tab-pane fade  active show" id="custom-v-pills-types" role="tabpanel" aria-labelledby="custom-v-pills-types-tab">
                                                        <livewire:inventory.sales.product :key="'tab33-component'"/>
                                                </div>
                                                <!--end tab-pane-->

                                                <div class="tab-pane fade" id="custom-v-pills-messages" role="tabpanel" aria-labelledby="custom-v-pills-messages-tab">
                                                    <livewire:inventory.sales.productgroup :key="'tab55-component'"/>

                                                </div>
                                                <!--end tab-pane-->
                                            </div>
                                        </div> <!-- end col-->
                                    </div>
                                </div>
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/sweetalerts.init.js') }}"></script>
<script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
<script src="https://cdn.lordicon.com/libs/mssddfmo/lord-icon-2.1.0.js"></script>
<script src="{{ URL::asset('build/js/pages/modal.init.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
