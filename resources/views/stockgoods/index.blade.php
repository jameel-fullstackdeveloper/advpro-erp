@extends('layouts.master')
@section('title')
    Stock (Finished Goods)
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" type="text/css" />
<style>



    /* Hide the section by default */
    .printable-section {
        display: none !important;
        border-top: none !important;
    }

    .print-footer {
        display:none !important;
    }



    /* Show the section only during printing */
    @media print {



        /* Hide unnecessary elements */
    #startDate,
    #endDate,
    label[for="startDate"],
    label[for="endDate"],
    #accountId,
    label[for="accountId"],
    #btnledger,
    #printButton,
    #downloadPdfButton,
    .customizer-setting,
    .stylish-select,
    .form-select,
    .select2-container,
    .title_middle,
    .select2-container--default .select2-selection--single {
        display: none !important;
    }

        body, html {
        margin: 0 !important;
        padding: 0 !important;
        height: 100% !important;
        }

        .card, .table-container, .table, .table th, .table td, .card-body, .card-header {
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
        }

        .table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        .table th, .table td {
            padding: 4px !important;
            font-size: 0.9rem !important;
        }

        .card-header {
            padding: 0 !important;
        }

        .printable-section {
            margin: 0 !important;
            padding: 0 !important;
            border-top: none !important;
        }

        .printable-section table {
            border: none !important;
        }

        .printable-section table th, .printable-section table td {
            border: none !important;
            padding: 4px !important;
        }

        .table-loading-overlay {
            display: none !important;
        }

        /* Ensuring no page breaks */
        tr {
            page-break-inside: avoid !important;
        }

        .card-header {
            margin-bottom:20px !important;
        }



        .printable-section  table{
            border:none !important;
        }

        .printable-section  table tr th {
            border:none !important;
        }

        .printable-section  table tr td {
            border:none !important;
        }


        .printable-section {
            display: block  !important;
        }

        .rownpadding {
            margin-bottom:20px !important;
            padding-bottom":20px !important;
        }

        .hide_in_print {
            display:none !important;
        }

        .tableprint  tr{
            border-bottom: 1px solid #ddd !important;  /* Ensure borders are visible */
        }

        .print-footer {
            display:block !important;
        }


    }
    </style>
@endsection

@section('content')

@php
        use App\Models\Company;
        $company = Company::find(session('company_id'));
@endphp

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h4 class="mb-sm-0 card-title">Stock (Finished Goods)</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Inventory</a></li>
                    <li class="breadcrumb-item active">Stock</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Filter Form (POST request) -->
<form method="POST" action="{{ route('stockgoods.filter') }}">
    @csrf <!-- CSRF Token for security -->

    <div class="card ">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <input type="date" id="startDate" name="startDate" class="form-control" value="{{ old('startDate', $startDate) }}">
                    @error('startDate')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-2">
                    <input type="date" id="endDate" name="endDate" class="form-control" value="{{ old('endDate', $endDate) }}">
                    @error('endDate')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <select id="accountId" name="accountId" class="form-select select2" required>
                    <option value=""> --- Select Finished Goods ---</option>

                    @foreach ($items as $item)
                            <option value="{{ $item->id }}" {{ old('accountId', request('accountId')) == $item->id ? 'selected' : '' }}>
                                {{ $item->product_name }}
                            </option>
                        @endforeach
                    </select>

                    @error('accountId')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-success me-1" id="btnledger">Show</button>

                    <button  id="printButton" class="btn btn-light">
                        <i class="bx bx-printer" style="font-size:16px;"></i>
                    </button>


                </div>
            </div>
        </div>
    </div>
</form>

{{-- Only display the ledger if the form has been submitted --}}
@if (old('startDate') || old('endDate') || request('accountId'))

@php

$endingbal = 0;
@endphp


<div class="printable-section mb-5" style="border:none !important;">
    <table class="table table-borderless" style="width: 100%; margin: 0; padding: 0; border: none;">
        <tr>
            <!-- Left side: Logo -->
            <td style="width: 17%; vertical-align: top; padding: 0;">
                <img
                    src="{{ $company->avatar ? Storage::disk('spaces')->url($company->avatar) : asset('images/user-dummy-img.jpg') }}"
                    alt="Logo"
                    class="img-fluid"
                    style="width: 6rem; height: 6rem;"
                >
            </td>
            <!-- Middle: Company Details -->
            <td style="width: 47%; vertical-align: top; text-align: left; padding: 0;">
                <h1 style="color: rgb(21 128 61); font-size: 1.5rem; line-height: 2rem; font-weight: 700; margin: 0;">
                    {{ $company->name }}
                </h1>
                <p class="text-muted" style="margin: 0;">{{ $company->address }}</p>
                <p class="text-muted" style="margin: 0;">Email: {{ $company->email }}</p>
                <p class="text-muted" style="margin: 0;">Phone: {{ $company->phone }}</p>
            </td>
            <!-- Right side: Stock Statement -->
            <td style="width: 40%; text-align: right; vertical-align: middle; padding: 0;">
                <h2 style="font-size: 1.2rem; font-weight: bold; margin: 0;">Finished Goods Statement</h2>
            </td>
        </tr>
    </table>
</div>


<div class="row rownpadding">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center text-white bg-success" style="padding: 10px; border: none; box-shadow: none;">
            <div class="d-flex justify-content-between w-100">



            <table class="w-100">

                    <tr>
                        <td colspan="2">
                            <h4 class="printable-section  text-dark font-weight-bold text-uppercase"
                                style="font-size: 16px;margin-bottom:10px !important;">
                                {{ $itemName }} </h4>
                        </td>
                    </tr>

                    <tr>
                        <!-- Left Side: Date Range -->
                        <td class="text-start" style="font-size: 16px; font-weight: bold; text-transform: uppercase;" width="25%">
                            From {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}
                        </td>

                        <!-- Middle: Item Name -->
                        <td class="text-center title_middle" style="font-size: 16px; font-weight: bold; text-transform: uppercase;" width="50%">
                            {{ $itemName }}
                        </td>

                        <!-- Right Side: Balance -->
                        <td class="text-end" style="font-size: 16px; font-weight: bold;" id="endbal" width="25%">
                            <!-- Balance will be dynamically updated here -->
                        </td>
                    </tr>
                </table>
            </div>
            </div>

            <!-- Ledger Display -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-centered align-middle table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th style="text-align:right">Stock In</th>
                                    <th style="text-align:right">Stock Out</th>
                                    <th style="text-align:right">Balance</th>
                                </tr>
                            </thead>

                            @php
                                $totalStockIn = 0;
                                $totalStockOut = 0;
                            @endphp

                            <tbody>
                                @foreach($ledger as $entry)
                                <tr>
                                    <td>
                                        @if($entry['date'] !== 'N/A')
                                            {{ \Carbon\Carbon::parse($entry['date'])->format('d-m-Y') }}
                                        @else
                                            {{ $entry['date'] }}
                                        @endif
                                    </td>
                                    <td>{{ $entry['description'] }}</td>
                                    <td style="text-align:right">{{ $entry['stock_in'] }}</td>
                                    <td style="text-align:right">{{ $entry['stock_out'] }}</td>
                                    <td style="text-align:right">{{ $entry['balance'] }} <small> Bags</small></td>
                                </tr>

                                @php
                                    // Summing stock in and stock out values
                                    $totalStockIn += $entry['stock_in'];
                                    $totalStockOut += $entry['stock_out'];
                                    $endingbal = $entry['balance'];
                                @endphp
                                @endforeach

                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="2" style="font-weight:bold;text-align:center;">Total</td>
                                        <td style="font-weight:bold;text-align:right"> {{ $totalStockIn }}</td>
                                        <td style="font-weight:bold;text-align:right"> {{ $totalStockOut }}</td>
                                        <td></td>
                                    </tr>

                                </tfoot>

                            </tbody>
                        </table>
                        <script>
    // Pass the formatted number without commas into the JavaScript variable
    var endingBalance = {{ $endingbal }};

    // Format the number with commas and two decimal places using JavaScript
    function formatNumber(number) {
        return number.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    // Update the text content of the element with the formatted balance
    document.getElementById('endbal').textContent = 'Stock: ' + formatNumber(endingBalance) + ' Bags';
</script>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="print-footer">
        <hr style="border-top: 1px solid #ddd;">
        <p style="text-align: center; font-size: 12px;">
            ****** End of Report *******<br>
            printed by {{ auth()->user()->name }} on {{ \Carbon\Carbon::now()->format('d-m-Y h:i:s A') }}
        </p>
    </div>


</div>
@endif

@endsection

@section('script')
<!--jquery cdn-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<!--select2 cdn-->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ URL::asset('build/js/pages/select2.init.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>

<script>
   document.addEventListener('DOMContentLoaded', () => {
       initializeSelect2();

   });

   document.getElementById('printButton').addEventListener('click', function () {
        window.print(); // This triggers the print dialog to print the entire page
    });

    function initializeSelect2() {
        $('#accountId').select2(); // Initialize Select2 on the accountId element
    }







</script>
@endsection
