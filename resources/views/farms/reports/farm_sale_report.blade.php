<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}">
  <title>Sale Register (Farms) | QuickERP</title>
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
      <h2>SALE REGISTER (FARM)</h2>
      <p>From: <strong>{{ \Carbon\Carbon::parse($firstDate)->format('d-m-Y') }}</strong> to
        <strong>{{ \Carbon\Carbon::parse($lastDate)->format('d-m-Y') }}</strong>
      </p>
    </div>
  </div>

  <!-- Table Section -->
  <table>

    <tbody>
    @php
    $groupedInvoices = $invoices->groupBy(function ($invoice) {
        return optional(\App\Models\ChartOfAccount::find($invoice->farm_account))->name ?? 'Unknown Farm';
    });

    $grandTotalBags = 0;
    $grandTotalGross = 0;
    $grandTotalDiscount = 0;
    $grandTotalBonus = 0;
    $grandTotalAmount = 0;
@endphp

@forelse ($groupedInvoices as $farmName => $farmInvoices)
    @php
        $totalBags = 0;
        $totalGross = 0;
        $totalDiscount = 0;
        $totalBonus = 0;
        $totalAmount = 0;
    @endphp

    <!-- Add spacing before each group -->
    <tr><td colspan="9" style="height: 15px; background: white; border: none;"></td></tr>

    <!-- Group Header -->
    <tr>
        <th colspan="9" class="bg-gray-200 text-lg py-3 text-center">{{ $farmName }}</th>
    </tr>

    <!-- Repeat Table Headers for Each Group -->
    <tr class="bg-gray-100">
        <th>Date</th>
        <th>Invoice #</th>
        <th>Customer Name</th>
        <th>Product Name</th>
        <th>Quantity</th>
        <th>Price</th>
        <th>Gross Amount</th>
        <th>Discount <br/><small>Amount & Rate</small></th>
        <th>Net Amount</th>
    </tr>

    @foreach ($farmInvoices as $invoice)
        @foreach ($invoice->items as $index => $item)
            @php
                $bags = floatval($item->quantity ?? 0);
                $netAmount = floatval($item->net_amount ?? 0);
                $discount = floatval($item->discount_amount ?? 0);
                $bonus = floatval($item->discount_per_bag_amount ?? 0);
                $amountInclTax = floatval($item->amount_incl_tax ?? 0);

                $totalBags += $bags;
                $totalGross += $netAmount;
                $totalDiscount += $discount;
                $totalBonus += $bonus;
                $totalAmount += $amountInclTax;
            @endphp

            <tr>
                <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') }}</td>
                <td>{{ $invoice->invoice_number }}</td>
                <td>{{ $invoice->customer->name ?? 'N/A' }}</td>
                <td>{{ $item->product->name ?? 'N/A' }}</td>
                <td>{{ number_format($item->quantity, 2) }}</td>
                <td>{{ number_format($item->unit_price, 2) }}</td>
                <td>{{ number_format($netAmount, 2) }}</td>
                <td>
                    @if($discount > 0)
                        {{ number_format($discount, 2) }}
                        @if($item->discount_rate > 0)
                            <small>@ {{ number_format($item->discount_rate) }}%</small>
                        @endif
                    @else
                        -
                    @endif
                </td>
                <td>{{ number_format($amountInclTax, 2) }}</td>
            </tr>
        @endforeach
    @endforeach

    <!-- Group Total Row -->
    <tr class="totals-row">
        <th colspan="4" class="text-right bg-gray-100">Total for {{ $farmName }}:</th>
        <th class="bg-gray-100">{{ number_format($totalBags, 2) }}</th>
        <th class="bg-gray-100">-</th>
        <th class="bg-gray-100">{{ number_format($totalGross, 2) }}</th>
        <th class="bg-gray-100">{{ number_format($totalDiscount, 2) }}</th>
        <th class="bg-gray-100">{{ number_format($totalAmount, 2) }}</th>
    </tr>

    @php
        $grandTotalBags += $totalBags;
        $grandTotalGross += $totalGross;
        $grandTotalDiscount += $totalDiscount;
        $grandTotalBonus += $totalBonus;
        $grandTotalAmount += $totalAmount;
    @endphp
@empty
    <tr>
        <td colspan="9">No invoices found.</td>
    </tr>
@endforelse

<!-- Grand Total Row -->
<tr class="totals-row">
    <th colspan="4" class="text-center bg-gray-300">Grand Total:</th>
    <th class="bg-gray-300">{{ number_format($grandTotalBags, 2) }}</th>
    <th class="bg-gray-300">-</th>
    <th class="bg-gray-300">{{ number_format($grandTotalGross, 2) }}</th>
    <th class="bg-gray-300">{{ number_format($grandTotalDiscount, 2) }}</th>
    <th class="bg-gray-300">{{ number_format($grandTotalAmount, 2) }}</th>
</tr>


    </tbody>
  </table>



</div>

</body>
</html>
