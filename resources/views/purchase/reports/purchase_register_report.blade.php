<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}">
  <title>Purchase Register (Mill) | QuickERP</title>
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
      <h2>PURCHASE REGISTER</h2>
      <p>From: <strong>{{ \Carbon\Carbon::parse($firstDate)->format('d-m-Y') }}</strong> to
        <strong>{{ \Carbon\Carbon::parse($lastDate)->format('d-m-Y') }}</strong>
      </p>
    </div>
  </div>

  <!-- Table Section -->
  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th>Bill #</th>
        <th>Vendor Name</th>
        <th>Item Name</th>
        <th>Quantity</th>
        <th>Rate</th>
        <th>Amount</th>
        <th>Freight</th>
        <th>Brokerage</th>
      </tr>
    </thead>
    <tbody>
      @php
        $totalBags = 0;
        $totalGross = 0;
        $totalTax = 0;
        $totalFurtherTax = 0;
        $totalAdvanceTax = 0;
        $totalAmount = 0;
        $totalFreight = 0;
        $totalBrokarage = 0;
      @endphp

      @foreach($invoices as $invoice)
        @foreach($invoice->items as $index => $item)
          @php
            $bags = floatval($item->quantity ?? 0);
            $netAmount = floatval($item->gross_amount ?? 0);
            $salesTax = floatval($item->sales_tax_amount ?? 0);
            $furtherTax = floatval($item->further_sales_tax_amount ?? 0);
            $advanceTax = floatval($item->advance_wht_amount ?? 0);
            $amountInclTax = floatval($item->net_amount ?? 0);

            $totalBags += $bags;
            $totalGross += $netAmount;
            $totalTax += $salesTax;
            $totalFurtherTax += $furtherTax;
            $totalAdvanceTax += $advanceTax;
            $totalAmount += $amountInclTax;
            $totalFreight += $invoice->freight;
            $totalBrokarage += $invoice->broker_amount;
          @endphp

          <tr>
             <td>{{ \Carbon\Carbon::parse($invoice->bill_date)->format('d-m-Y') }}</td>
              <td>
                {{ $invoice->bill_number }}
              </td>
              <td>{{ $invoice->vendor->name ?? 'N/A' }}</td>
            <td>{{ $item->product->name ?? 'N/A' }}</td>
            <td>{{ number_format($item->quantity,2) }}</td>
            <td>{{ number_format($item->price, 2) }}</td>
            <td>{{ number_format($amountInclTax, 2) }}</td>
            <td>{{ number_format($invoice->freight, 2) }}</td>
            <td>{{ number_format($invoice->broker_amount, 2) }}</td>
          </tr>
        @endforeach
      @endforeach

      <tr>
      <th colspan="4" class="text-center">Total:</th>
      <th>{{ number_format($totalBags, 2) }}</th>
      <th>-</th>
      <th>{{ number_format($totalAmount, 2) }}</th>
      <th>{{ number_format($totalFreight, 2) }}</th>
      <th>{{ number_format($totalBrokarage, 2) }}</th>
    </tr>

    </tbody>
  </table>



</div>

</body>
</html>
