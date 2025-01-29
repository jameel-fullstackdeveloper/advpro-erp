@extends('layouts.master')
@section('title')
    Outwards
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

<div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0 card-title">Outwards </h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Weighbridge</a></li>
                    <li class="breadcrumb-item active">Outwards</li>
                </ol>
            </div>
        </div>
        </div>
</div>



<div class="row">
        <div class="col-12">

        </div>
</div>

<div class="row">
                                        <div class="col-lg-12">
                                            <div class="nav nav-pills nav-customs nav-danger nav-border-bottom nav-border-bottom" role="tablist" aria-orientation="vertical">

                                                <a class="nav-link show active" id="custom-v-pills-home-tab" data-bs-toggle="pill" href="#custom-v-pills-home" role="tab" aria-controls="custom-v-pills-home" aria-selected="true">
                                                        All Outwards
                                                </a>

                                                <a class="nav-link " id="custom-v-pills-profile-tab" data-bs-toggle="pill" href="#custom-v-pills-firstweight" role="tab" aria-controls="custom-v-pills-profile" aria-selected="false" tabindex="-1">
                                                   First Weight
                                                   <span class="badge bg-dark rounded-circle text-white">
                                                    {{ \App\Models\WeighbridgeOutward::where('status', 0)
                                                            ->where('company_id', session('company_id'))
                                                            ->count() }}

                                                    </span>
                                                </a>

                                                <a class="nav-link" id="custom-v-pills-profile-tab" data-bs-toggle="pill" href="#custom-v-pills-secondweight" role="tab" aria-controls="custom-v-pills-profile" aria-selected="false" tabindex="-1">
                                                   Second Weight
                                                </a>

                                                </div>
                                            </div> <!-- end col-->
                                        </div>

                                <div class="row">
                                        <div class="col-lg-12">
                                            <div class="tab-content text-muted">
                                                <div class="tab-pane fade active show" id="custom-v-pills-home" role="tabpanel" aria-labelledby="custom-v-pills-messages-tab">
                                                    <livewire:weighbridge.outwards.outwards-manager :key="'outwards-'.now()->timestamp"/>
                                                </div>


                                                <div class="tab-pane fade " id="custom-v-pills-firstweight" role="tabpanel" aria-labelledby="custom-v-pills-home-tab">
                                                    <livewire:weighbridge.outwards.firstweight-manager :key="'firstweight-'.now()->timestamp"/>
                                                </div>

                                                <div class="tab-pane fade" id="custom-v-pills-secondweight" role="tabpanel" aria-labelledby="custom-v-pills-messages-tab">
                                                    <livewire:weighbridge.outwards.secondweight-manager :key="'secondweight-'.now()->timestamp"/>
                                                </div>



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
<script src="https://cdn.socket.io/4.2.0/socket.io.min.js" integrity="sha384-PiBR5S00EtOj2Lto9Uu81cmoyZqR57XcOna1oAuVuIEjzj0wpqDVfD0JA9eXlRsj" crossorigin="anonymous"></script>




@endsection
