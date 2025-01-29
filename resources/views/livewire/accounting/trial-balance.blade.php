<div class="card">
    <style>
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
    .select2-container--default .select2-selection--single {
        display: none !important;
    }

    /* Adjust the card layout for print */
    .card {
        border: none;
        box-shadow: none;
    }

    .card-header {
        background-color: #fff !important;
        color: #000 !important;
        padding: 10px;
        border: none;
    }

    .card-body {
        padding: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 8px 12px;
        border: 1px solid #ddd;
        font-size: 0.9rem;
    }

    th {
        background-color: #f4f4f4;
        font-weight: bold;
        text-align: left;
    }

    td {
        font-size: 0.9rem;
    }

    h4 {
        font-size: 1.2rem;
        margin-bottom: 15px;
    }

    .table {
        margin-bottom: 0;
    }

    tr {
        page-break-inside: avoid;
    }

    @page {
        size: auto;
        margin: 15mm 10mm 10mm 10mm;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: 'Open Sans', sans-serif;
    }

    body:after {
        content: "Page " counter(page);
        position: fixed;
        bottom: 10px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 0.8rem;
        color: #999;
    }
    .font-weight-bold {
        color:#000 !important;
    }

    .table-sm>:not(caption)>*>* {
         padding: 0.3rem .25rem !important;
    }
}

        .table-sm>:not(caption)>*>* {
            padding: 0.5rem .35rem;
        }


        .table-loading-overlay {
            position: fixed; /* Change to fixed to center on the screen */
            top: 50%; /* Center vertically */
            left: 50%; /* Center horizontally */
            transform: translate(-50%, -50%); /* Adjust for element's own width and height */
            width: auto;
            height: auto;
            z-index: 999999; /* Ensure it appears above everything else */
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out;
        }

        .table-loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .table-container {
            position: relative;
        }

        .table-loading-overlay .text-center {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .spinner-border {
            width: 2rem;
            height: 2rem;
            margin-bottom: 10px; /* Adds space between the spinner and the message */
        }

        .loading-message {
            font-size: 14px;
            color: #fe5c4c;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            letter-spacing: 1px;
            animation: blinkingText 1s infinite;
        }

        .card-header .text-start {
            flex: 1;
        }

        .card-header .text-center {
            flex: 1;
            text-align: center;
        }

        .card-header .text-end {
            flex: 1;
            text-align: right;
        }

        .table-sm>:not(caption)>*>* {
            padding: 0.5rem .35rem;
        }

        @keyframes blinkingText {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0;
            }
        }
    </style>

    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label for="startDate" class="form-label" >Start Date</label>
                <input type="date" wire:model="startDate" class="form-control" id="startDate">
                @error('startDate') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-3">
                <label for="endDate" class="form-label">End Date</label>
                <input type="date" wire:model="endDate" class="form-control" id="endDate">
                @error('endDate') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button wire:click="generateTrialBalance" class="btn btn-primary me-2"  id="btnledger">Generate</button>
                <button onclick="window.print()" id="printButton" class="btn btn-light">
                        <i class="bx bx-printer" style="font-size:16px;"></i>
                    </button>

            </div>

            </div>
        </div>

    <div class="row mt-3">
            <div class="col-md-12">
                <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center text-white bg-primary"
                 style="padding: 10px; border: none; box-shadow: none;">
                <div class="d-flex justify-content-between w-100">

                    <div class="text-start col-md-4">
                        <span class="" style="font-size: 14px;">from {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}</span>
                   </div>

                    <div class="text-center col-md-4">
                        <h4 class="mb-0 text-white font-weight-bold text-uppercase" style="font-size: 16px;"> Trail Balance</h4>
                    </div>

                    <div class="text-end col-md-4">

                    </div>

                </div>
    </div>

    <div class="card-body">
                        <div class="table-container">
                                <!-- Loading Spinner and Message -->
                                <div class="table-loading-overlay" wire:loading.class="active">
                                    <div class="text-center">
                                        <div class="spinner-border text-danger" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <div class="loading-message">
                                            Generating the trail balance...
                                        </div>
                                    </div>
                                </div>

        @if(!empty($trialBalanceData))
            <table class="table table-sm table-hover table-bordered">
                <thead class="bg-info text-white" style="font-size:14px;">
                    <tr>
                        <th>Accounts Title</th>
                        <th style="text-align:right">Debit</th>
                        <th style="text-align:right">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Categories order ensures the correct display sequence
                        $categoriesOrder = ['Current Assets', 'Fixed Assets', 'Liabilities', 'Equity', 'Expenses', 'Revenue'];
                    @endphp

                    @foreach($categoriesOrder as $type)
                        @php
                            $typeDebitTotal = 0;
                            $typeCreditTotal = 0;
                        @endphp

                        <!-- Calculate totals for the type -->
                        @foreach($trialBalanceData[$type] ?? [] as $groupName => $accounts)
                            @php
                                $groupDebitTotal = 0;
                                $groupCreditTotal = 0;
                            @endphp
                            @foreach($accounts as $data)
                                @php
                                    $groupDebitTotal += $data['debit'] ?? 0;
                                    $groupCreditTotal += $data['credit'] ?? 0;
                                @endphp
                            @endforeach
                            @php
                                $typeDebitTotal += $groupDebitTotal;
                                $typeCreditTotal += $groupCreditTotal;
                            @endphp
                        @endforeach

                        <!-- Only display the type if it has non-zero values -->
                        @if($typeDebitTotal > 0 || $typeCreditTotal > 0)
                            <!-- Display the total for the type -->
                            <tr style="background-color: #f5f5f5;">
                                <td style="font-weight: bold;" width="70%" class="text-uppercase text-success">{{ $type }}</td>
                                <td style="text-align:right; font-weight: bold;" class="text-success">{{ $typeDebitTotal > 0 ? number_format($typeDebitTotal,2) : '-' }}</td>
                                <td style="text-align:right; font-weight: bold;" class="text-success">{{ $typeCreditTotal > 0 ? number_format($typeCreditTotal,2) : '-' }}</td>
                            </tr>

                            <!-- Display groups under the type -->
                            @foreach($trialBalanceData[$type] ?? [] as $groupName => $accounts)
                                @php
                                    $groupDebitTotal = 0;
                                    $groupCreditTotal = 0;
                                    $groupHasValues = false;
                                @endphp
                                @foreach($accounts as $data)
                                    @php
                                        $groupDebitTotal += $data['debit'] ?? 0;
                                        $groupCreditTotal += $data['credit'] ?? 0;
                                    @endphp
                                    @if(($data['debit'] ?? 0) > 0 || ($data['credit'] ?? 0) > 0)
                                        @php
                                            $groupHasValues = true;
                                        @endphp
                                    @endif
                                @endforeach

                                @if($groupHasValues)
                                    <!-- Display the total for the group -->
                                    <tr style="background-color: #e8f7ff;">
                                        <td style="padding-left: 20px; font-weight: bold;" class="text-uppercase text-primary">{{ $groupName }}</td>
                                        <td style="text-align:right; font-weight: bold;" class="text-primary">{{ $groupDebitTotal > 0 ? number_format($groupDebitTotal,2) : '-' }}</td>
                                        <td style="text-align:right; font-weight: bold;" class="text-primary">{{ $groupCreditTotal > 0 ? number_format($groupCreditTotal,2) : '-' }}</td>
                                    </tr>

                                    <!-- Display individual accounts under the group -->
                                    @foreach($accounts as $data)
                                        @if(($data['debit'] ?? 0) > 0 || ($data['credit'] ?? 0) > 0)
                                            <tr>
                                                <td style="padding-left: 40px;">{{ $data['account_name'] }}</td>
                                                <td style="text-align:right">{{ $data['debit'] > 0 ? number_format($data['debit'],2) : '-' }}</td>
                                                <td style="text-align:right">{{ $data['credit'] > 0 ? number_format($data['credit'],2) : '-' }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                     <!-- Add row for the total value of stock (closing) -->
                     <tr>
                        <td style="text-align:left;padding-left: 40px;" class="text-uppercase">Value of Stock (Opening)</td>
                        <td style="text-align:right;">
                            {{ number_format($trialBalanceData['Stock']['Value of Stock (Closing)'], 2) }}
                        </td>
                        <td> - </td>
                    </tr>
                </tbody>
                <tfoot style="background-color: #e9ecef;">
                    <tr>
                        <td style="font-weight:bold;text-align:center;">Grand Total</td>
                        <td style="text-align:right; font-weight:bold;">{{ $totalDebit > 0 ? number_format($totalDebit + $trialBalanceData['Stock']['Value of Stock (Closing)'],2) : '-' }}</td>
                        <td style="text-align:right; font-weight:bold;">{{ $totalCredit > 0 ? number_format($totalCredit,2) : '-' }}</td>
                    </tr>
                </tfoot>

            </table>

            <div class="d-flex justify-content-center text-danger text-uppercase fs-14 fw-bold">
                Difference:
                {{ number_format(($totalDebit + $trialBalanceData['Stock']['Value of Stock (Closing)'] ) - $totalCredit), 2 }}
            </div>

        </div>
        @endif
                </div>
            </div>
        </div>

</div>
