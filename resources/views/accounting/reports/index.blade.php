@extends('layouts.master')
@section('title')
    Accounting Reports
@endsection

@section('css')
@endsection

@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h4 class="mb-sm-0">Accounting Reports</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Accounting</a></li>
                        <li class="breadcrumb-item active">Reports</li>
                    </ol>
                </div>

                </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-6">
                                    <div class="card card-animate">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm flex-shrink-0">
                                                    <span class="avatar-title bg-primary-subtle text-primary rounded-1 fs-2">
                                                     <i class="bx bxs-bank"></i>
                                                </span></div>
                                                <div class="flex-grow-1 overflow-hidden ms-3">

                                                    <p class="text-uppercase fw-medium text-info text-truncate mb-3 fw-bold">Cash & Bank Report</p>



                                                    <form id="dateForm" method="POST" action="{{  route('cashbank_report')  }}" target="_blank">
                                                        @csrf
                                                            <div class="d-flex align-items-center mb-3">
                                                                <input type="date" class="form-control" id="exampleInputdate" value="{{ old('firstdate', $firstDate) }}"
                                                                 min="2024-07-01" max="{{ now()->format('Y-m-d') }}"
                                                                name="firstdate" required>
                                                                &nbsp;&nbsp;
                                                                <input type="date" class="form-control" id="exampleInputdate" value="{{ old('lastdate', $lastDate) }}"
                                                                 min="2024-07-01"
                                                                name="lastdate" required>

                                                                <button type="submit" style="border:none;background:none;"><span class="badge text-primary fs-12">
                                                                    <i class="ri-printer-line fs-18 align-middle me-1"></i>
                                                                </span>
                                                                    </button>
                                                            </div>
                                                        </form>


                                                    <p class="text-muted text-truncate mb-0">Opening Balance, Receipts, Payments & Balance between two dates </p>
                                                </div>
                                            </div>
                                        </div><!-- end card body -->
                                    </div>
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
