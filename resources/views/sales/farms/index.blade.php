@extends('layouts.master')
@section('title')
    Farms
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

<div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                                <h4 class="mb-sm-0 card-title">Farms</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Sales</a></li>
                                        <li class="breadcrumb-item active">Farms</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
</div>


<div class="card1">
            <div class="card-body1">
                      <div class="row">
                                        <div class="col-lg-12">

                                        </div> <!-- end col-->
                                </div>

                                <div class="row">
                                        <div class="col-lg-12">
                                            <div class="tab-content">

                                                <div class="tab-pane fade  active show" id="custom-v-pills-types" role="tabpanel" aria-labelledby="custom-v-pills-types-tab">
                                                     <livewire:sales.farms.farm-detail-manager :key="'tab3-component'"/>
                                                </div>
                                                <!--end tab-pane-->

                                                <div class="tab-pane fade d-none" id="custom-v-pills-messages" role="tabpanel" aria-labelledby="custom-v-pills-messages-tab">

                                                </div>
                                                <!--end tab-pane-->
                                            </div>
                                        </div> <!-- end col-->
                                    </div>
                                </div>

                                </div>   </div>

@endsection

@section('script')
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/sweetalerts.init.js') }}"></script>
<script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
<script src="https://cdn.lordicon.com/libs/mssddfmo/lord-icon-2.1.0.js"></script>
<script src="{{ URL::asset('build/js/pages/modal.init.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
