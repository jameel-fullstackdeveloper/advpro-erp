<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}">
  <title>Expenses (Farms) | QuickERP</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <style>
      body {
          background-color:#fff !important;
          font-family: 'Roboto', sans-serif;
          font-size: 12px;
      }
      @page {
        size: A4 landscape;
        margin: 10mm;
      }
      @media print {
        body {
          margin: 0;
          padding: 0;
          width: 100%;
        }
        .invoice-container {
          width: 100%;
          padding: 10px;
        }
        .no-print {
          display: none;
        }
        .totals-row {
          page-break-before: always; /* Ensures it appears only on the last page */
        }
      }
      .invoice-container {
        max-width: 100%;
        background-color: #fff;
        padding: 15px;
      }
      .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 10px;
        border-bottom: 2px solid #e5e7eb;
      }
      .header img {
        height: 80px;
        width: 80px;
        object-fit: cover;
        border-radius: 6px;
      }
      .header .company-info {
        text-align: left;
      }
      .header .company-info h1 {
        font-size: 20px;
        font-weight: bold;
        color: #1f2937;
        text-transform: uppercase;
      }
      .header .company-info p {
        font-size: 12px;
        color: #4b5563;
        margin-bottom: 2px;
      }
      .report-title {
        text-align: right;
        font-size: 18px;
        font-weight: bold;
        color: #1f2937;
      }
      .report-title p {
        font-size: 12px;
        color: #6b7280;
      }
      table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
      }
      th, td {
        border: 1px solid #d1d5db;
        padding: 6px 8px;
        text-align: left;
      }
      th {
        background-color: #f3f4f6;
        font-size: 12px;
        font-weight: bold;
        color: #1f2937;
      }
      td {
        font-size: 12px;
        color: #374151;
      }
      .totals-row th, .totals-row td {
        font-weight: bold;
        background-color: #e5e7eb;
      }
  </style>
</head>
<body>

@php
  use App\Models\Company;
  $company = Company::find(session('company_id'));
@endphp

<div class="invoice-container">
  <!-- Header Section -->
  <div class="header">
    <div class="flex items-center space-x-4">
      <img src="{{ $company->avatar ? Storage::disk('spaces')->url($company->avatar) : asset('images/user-dummy-img.jpg') }}" alt="Company Logo">
      <div class="company-info">
        <h1>{{ $company->name }}</h1>
        <p>{{ $company->address }}</p>
        <p>Email: {{ $company->email }}</p>
        <p>Phone: {{ $company->phone }}</p>
      </div>
    </div>
    <div class="report-title">
      <h2>EXPENSES (FARM)</h2>
      <p>From: <strong>{{ \Carbon\Carbon::parse($firstDate)->format('d-m-Y') }}</strong> to
        <strong>{{ \Carbon\Carbon::parse($lastDate)->format('d-m-Y') }}</strong>
      </p>
    </div>
  </div>

  <!-- Table Section -->
  <table>

    <tbody>
    @php
    $groupedVouchers = $vouchers->groupBy(function ($voucher) {
        return optional($voucher->voucherDetails->firstWhere('type', 'debit'))->account->name ?? 'Unknown Farm';
    });

    $totalAmountPay = 0;
@endphp

@forelse ($groupedVouchers as $farmName => $farmVouchers)
    @php
        $farmTotal = 0;
    @endphp




    <!-- Group Header -->
    <tr style="margin-top:10px;">
        <th colspan="5" class="bg-gray-200 text-lg text-center">{{ $farmName }}</th>
    </tr>

    <!-- Repeat Table Headers for Each Group -->
    <tr class="bg-gray-100">
        <th>Date</th>
        <th>Voucher #</th>
        <th>Payment From</th>
        <th>Expense Detail</th>
        <!--<th>Description</th>-->
        <th>Amount</th>
    </tr>

    @foreach ($farmVouchers as $voucher)
        @php
            // Get the debit account (payment from)
            $debitAccount = $voucher->voucherDetails->firstWhere('type', 'credit');

            // Get all credit accounts (expenses for the farm)
            $creditAccounts = $voucher->voucherDetails->where('type', 'debit');
        @endphp

        @foreach ($creditAccounts as $creditAccount)
            <tr>
                <td>{{ \Carbon\Carbon::parse($voucher->date)->format('d-m-Y') }}</td>
                <td>{{ $voucher->reference_number }}</td>
                <td>{{ $debitAccount ? $debitAccount->account->name : 'N/A' }}</td>
                <td>{{ $voucher->exp_to ? \App\Models\ChartOfAccount::find($voucher->exp_to)->name : 'N/A' }}
                    <small>({{ $creditAccount->narration }})</small>


                </td>
                <!--<td>{{ $creditAccount->narration }}</td>-->
                <td>{{ number_format($creditAccount->amount) }}</td>
            </tr>
            @php
                $farmTotal += $creditAccount->amount;
            @endphp
        @endforeach
    @endforeach

    <!-- Group Total Row -->
    <tr class="totals-row">
        <th colspan="4" class="text-right bg-gray-100">Total for {{ $farmName }}:</th>
        <th class="bg-gray-100">{{ number_format($farmTotal) }}</th>
    </tr>

     <!-- Empty row for spacing -->
     <tr><td colspan="5" style="height: 15px; background: white; border: none;"></td></tr>

    @php
        $totalAmountPay += $farmTotal;
    @endphp
@empty
    <tr>
        <td colspan="7">No vouchers found.</td>
    </tr>
@endforelse

<!-- Overall Total Row -->

<!--

<tr class="totals-row">
    <th colspan="5" class="text-center bg-gray-300">Grand Total:</th>
    <th class="bg-gray-300">{{ number_format($totalAmountPay) }}</th>
</tr> -->



    </tbody>
  </table>



</div>

</body>
</html>
