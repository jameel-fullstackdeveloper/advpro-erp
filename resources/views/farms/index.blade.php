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
            <h4 class="mb-sm-0 card-title">Farms
            </h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Farms</a></li>
                    <li class="breadcrumb-item active">Reports</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="card1">
            <div class="card-body1">
                      <div class="row">
                                        <div class="col-lg-12">
                                            <div class="nav nav-pills nav-customs nav-danger nav-border-bottom nav-border-bottom" role="tablist" aria-orientation="vertical">
                                                <a class="nav-link  show active" id="custom-v-pills-profile-tab" data-bs-toggle="pill" href="#custom-v-pills-types" role="tab" aria-controls="custom-v-pills-profile" aria-selected="true" tabindex="-1">
                                                    Farms
                                                </a>
                                                <a class="nav-link" id="custom-v-pills-profile-tab" data-bs-toggle="pill" href="#custom-v-pills-messages" role="tab" aria-controls="custom-v-pills-profile" aria-selected="false" tabindex="-1">
                                                Farms's Groups
                                                </a>

                                            </div>
                                        </div> <!-- end col-->
                                </div>

                                <div class="row">
                                        <div class="col-lg-12">
                                            <div class="tab-content">

                                                <div class="tab-pane fade  active show" id="custom-v-pills-types" role="tabpanel" aria-labelledby="custom-v-pills-types-tab">
                                                     <livewire:farms.farms.farms-detail-manager :key="'tab3-component'"/>
                                                </div>
                                                <!--end tab-pane-->

                                                <div class="tab-pane fade" id="custom-v-pills-messages" role="tabpanel" aria-labelledby="custom-v-pills-messages-tab">
                                                    <livewire:farms.farms.farms-group :key="'tab5-component'"/>
                                                </div>
                                                <!--end tab-pane-->
                                            </div>
                                        </div> <!-- end col-->
                                    </div>
                                </div>

                                </div>
</div>

</div>


@endsection

@section('script')
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/sweetalerts.init.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>

@endsection
