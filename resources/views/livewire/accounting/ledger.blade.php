<div class="">
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



    body {
        margin: 0;
        padding: 0;
        font-family: 'Open Sans', sans-serif;
    }

    /*body:after {
        content: "Page " counter(page);
        position: fixed;
        bottom: 10px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 0.8rem;
        color: #999;
    }*/


    .font-weight-bold {
        color:#000 !important;
    }

    .table-sm>:not(caption)>*>* {
         padding: 0.3rem .25rem !important;
    }
}

         /* Custom styling for the select2 dropdown */
    .stylish-select {
        font-size: 14px;
        color: #495057;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
        background-color: #fff;
        transition: all 0.3s ease;
    }

    .stylish-select:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    /* Base styling for the select2 dropdown */
    .stylish-select {
        font-size: 14px;
        color: #495057;
        padding: 0.375rem 1rem;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        background-color: #fff;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .stylish-select:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        outline: none;
    }

    /* Styling for the optgroup headers */
    .optgroup-header {
        color: #495057;
        font-weight: 600;
        background-color: #f8f9fa;
        padding: 0.25rem 0.75rem;
    }

    /* Styling for the options */
    .stylish-select option {
        padding: 0.5rem;
        font-size: 14px;
        color: #212529;
    }

    .stylish-select option:hover {
        background-color: #f1f3f5;
        color: #495057;
    }

    /* Select2 custom styles */
    .select2-container--default .select2-selection--single {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        height: calc(2.25rem + 2px); /* Same height as Bootstrap's form-select */
        background-color: #fff;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        padding:8px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #495057;
        line-height: 1.5;
        padding-left: 0.75rem;
        font-family: 'Montserrat', sans-serif;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
        right: 10px;
        padding:8px;
    }

    .select2-container--default .select2-selection--single:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        padding:8px;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #80bdff;
        color: #fff;
        padding:8px;
    }

    .select2-dropdown {
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border: 1px solid #ced4da;
    }

    .select2-results__group {
        padding: 0.5rem 0.75rem;
        font-weight: 600;
        color: #212529;
        background-color: #e9ecef;
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

body, html {
    margin: 0 !important;
    padding: 0 !important;
    height: 100% !important;
    background-color: #fff !important;
}

.card, .table-container, .table, .table th, .table td, .card-body, .card-header {
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
}

.table {
    width: 100% !important;
    border-collapse: collapse !important;
    table-layout: fixed !important; /* Fix column width */
}

.table th, .table td {
    padding: 4px !important;
    font-size: 0.9rem !important;
    word-wrap: break-word; /* Ensure content wraps correctly */
    overflow: hidden; /* Hide overflow content */
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
    width: 100% !important;
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
    margin-bottom: 20px !important;
}

.printable-section table {
    border: none !important;
}

.printable-section table tr th {
    border: none !important;
}

.printable-section table tr td {
    border: none !important;
}

.printable-section {
    display: block !important;
}

.rownpadding {
    margin-bottom: 20px !important;
    padding-bottom: 20px !important;
}

.hide_in_print {
    display: none !important;
}

.tableprint tr {
    border-bottom: 1px solid #ddd !important;  /* Ensure borders are visible */
}

.print-footer {
    display: block !important;
}
}


    </style>


@php
        use App\Models\Company;
        $company = Company::find(session('company_id'));
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
                <h2 style="font-size: 1.2rem; font-weight: bold; margin: 0;">Ledger Statement</h2>
            </td>
        </tr>
    </table>
</div>






    <!-- Content -->
    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <input type="date" id="startDate" wire:model="startDate" class="form-control">
                    @error('startDate') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-2">
                    <input type="date" id="endDate" wire:model="endDate" class="form-control">
                    @error('endDate') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6"  wire:ignore>
                    <select id="accountId" wire:model="accountId" class="form-select select2 stylish-select">
                        <option value="">--- Select Account ---</option>
                        @foreach($accountTypes as $type)
                            <optgroup label="{{ $type->name }}">  <!-- Use name instead of category -->
                                @foreach($type->chartOfAccountGroups as $group)
                                    @foreach($group->chartOfAccounts as $account)
                                        <option value="{{ $account->id }}">
                                            {{ $account->name }} [{{ $group->name }}]
                                        </option>
                                    @endforeach
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('accountId') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button wire:click="generateLedger" class="btn btn-success me-1" id="btnledger">Show</button>


                    <!--<button wire:click="downloadPdf" id="downloadPdfButton" class="btn btn-sm btn-light me-1">
                        <i class="bx bxs-file-pdf" style="font-size:24px;"></i>
                    </button>-->

                    <button wire:click="printLedger" id="printButton" class="btn btn-light">
                        <i class="bx bx-printer" style="font-size:16px;"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if($ledgerEntries)


                @php
                    $totalDebit = 0;
                    $totalCredit = 0;
                    $endingBalance = 0;
                    $balanceSuffix = '';

                    foreach ($ledgerEntries as $entry) {
                        if ($entry->type == 'debit') {
                            $totalDebit += $entry->amount;
                        } elseif ($entry->type == 'credit') {
                            $totalCredit += $entry->amount;
                        }

                        $endingBalance = (float) $entry->balance; // Ensure the balance is numeric

                        // Determine the correct balance suffix based on account category and balance
                        if ($accountCategory === 'Assets' || $accountCategory === 'Expenses') {
                            // Default nature is Dr.
                            $balanceSuffix = $endingBalance >= 0 ? 'Dr.' : 'Cr.';
                        } else {
                            // Default nature is Cr.
                            $balanceSuffix = $endingBalance >= 0 ? 'Cr.' : 'Dr.';
                        }

                        // Convert ending balance to positive if it is negative
                        $endingBalance = abs($endingBalance);
                    }
                @endphp


                <h4 class="printable-section  text-white font-weight-bold text-uppercase"
                style="font-size: 16px;margin-bottom:10px !important;">
                 {{ $accountTitle }} </h4>

        <div class="row rownpadding">
            <div class="col-md-12">
                <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center text-white bg-primary" style="padding: 10px; border: none; box-shadow: none;">
                <div class="d-flex justify-content-between w-100">

                    <div class="text-start">
                        <span class="me-3" style="font-size: 14px;">
                        From {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}</span>
                    </div>

                    <!-- Balance in the middle -->
                    <div class="text-center hide_in_print">
                        <h4 class="mb-0 text-white font-weight-bold text-uppercase" style="font-size: 16px;"> {{ $accountTitle }} </h4>
                    </div>

                    <!-- Dates on the right side -->
                    <div class="text-end">
                            <span class="font-weight-bold" style="font-size: 16px;">Balance: {{ number_format($endingBalance,2) . ' ' .$balanceSuffix }}</span>
                    </div>

                </div>
        </div>

    <div class="card-body">
                        <div class="table-container">
                                <!-- Loading Spinner and Message -->
                                <div class="table-loading-overlay" wire:loading.class="active">
                                    <div class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <div class="loading-message">
                                            Generating the ledger...
                                        </div>
                                    </div>
                                </div>
                            <table class="table table-sm table-hover table-responsive-sm tableprint">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th class="">Type</th>
                                    <th class="">Refrence No.</th>
                                    <th>Description</th>
                                    <th style="text-align:right">Debit</th>
                                    <th style="text-align:right">Credit</th>
                                    <th style="text-align:right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>

                             @foreach($ledgerEntries as $entry)
                                <tr>
                                    <td width="90px">{{ \Carbon\Carbon::parse($entry->voucher_date)->format('d-m-Y') }}</td>

                                    <td> @if($entry->reference_number)
                                            @if(Str::contains($entry->reference_number, 'INV-FRM'))
                                                <!-- This is a Farm Invoice -->
                                                <span class="text-muted">Farm Invoice</span>
                                            @elseif(Str::contains($entry->reference_number, 'INV-FM'))
                                                <!-- This is a Mill Invoice -->
                                                <span class="text-muted">Mill Invoice</span>

                                            @elseif(Str::contains($entry->reference_number, 'PB-FM'))
                                                <!-- This is a Mill Invoice -->
                                                <span class="text-muted">Mill Bill</span>
                                            @elseif(Str::contains($entry->reference_number, 'PB-FRM'))
                                                <!-- This is a Mill Invoice -->
                                                <span class="text-muted">Farm Bill</span>

                                            @elseif(Str::contains($entry->reference_number, 'BR'))
                                                <!-- This is a Bank Receipt -->
                                                <span class="text-muted">Bank Receipt</span>
                                            @elseif(Str::contains($entry->reference_number, 'BP'))
                                                <!-- This is a Bank Payment -->
                                                <span class="text-muted">Bank Payment</span>
                                            @elseif(Str::contains($entry->reference_number, 'CP'))
                                                <!-- This is a Cash Payment -->
                                                <span class="text-muted">Cash Payment</span>
                                            @elseif(Str::contains($entry->reference_number, 'CR'))
                                                <!-- This is a Cash Receipt -->
                                                <span class="text-muted">Cash Receipt</span>
                                            @elseif(Str::contains($entry->reference_number, 'JV'))
                                                <!-- This is a Journal Voucher -->
                                                <span class="text-muted">Journal Voucher</span>
                                            @elseif(Str::contains($entry->reference_number, 'MTF'))
                                                <!-- This is a Journal Voucher -->
                                                <span class="text-muted">Material Transfer</span>
                                            @else

                                            @endif
                                        @else

                                        @endif</td>
                                    <td class=""> {{ $entry->reference_number  }} </td>
                                    <td width="50%">{!! $entry->full_description !!}</td>

                                    <td style="text-align:right">
                                        {{ $entry->type == 'debit' ? number_format($entry->amount,2) : '-' }}
                                    </td>
                                    <td style="text-align:right">
                                        {{ $entry->type == 'credit' ? number_format($entry->amount,2) : '-' }}
                                    </td>
                                    <td style="text-align:right">
                                        @php
                                            // Ensure the balance is numeric before applying abs() and number_format()
                                            $numericBalance = (float) $entry->balance;

                                            // Determine the correct balance suffix based on account category and balance
                                            if ($accountCategory === 'Assets' || $accountCategory === 'Expenses') {
                                                // Default nature is Dr.
                                                $balanceSuffix = $numericBalance >= 0 ? 'Dr.' : 'Cr.';
                                            } else {
                                                // Default nature is Cr.
                                                $balanceSuffix = $numericBalance >= 0 ? 'Cr.' : 'Dr.';
                                            }

                                            // Format the balance as a positive number
                                            $formattedBalance = number_format(abs($numericBalance),2);
                                        @endphp

                                        {{ $formattedBalance . ' ' . $balanceSuffix }}
                                    </td>
                                </tr>
                                @endforeach

                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" style="font-weight:bold;text-align:center;">Total</td>
                                    <td style="font-weight:bold;text-align:right">{{ number_format($totalDebit,2) }}</td>
                                    <td style="font-weight:bold;text-align:right">{{ number_format($totalCredit,2) }}</td>
                                    <td style="font-weight:bold;text-align:right">  {{ $formattedBalance . ' ' . $balanceSuffix }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif



    <div class="print-footer">
        <hr style="border-top: 1px solid #ddd;">
        <p style="text-align: center; font-size: 12px;">
            ****** End of Report *******<br>
            printed by {{ auth()->user()->name }} on {{ \Carbon\Carbon::now()->format('d-m-Y h:i:s A') }}
        </p>
    </div>


</div>





@script
<script>
    $wire.on('printLedger', () => {
        window.print();
    });

   document.addEventListener('livewire:initialized', () => {
        initializeSelect2();

    });

    function initializeSelect2() {
        $('#accountId').select2();
        $('#accountId').on('change', function () {
            // Trigger Livewire update when select2 changes
            let selectedValue = $(this).val();
            @this.set('accountId', selectedValue);
        });
    }

</script>
@endscript
