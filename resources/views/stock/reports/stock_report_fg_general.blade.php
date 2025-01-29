<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}">
  <title>Finished Goods Stock Report | QuickERP</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <style>
      body {
          border:none !important;
          background-color:#fff !important;
          font-family: 'Roboto', sans-serif;
      }
    @page {
      size: A4 landscape;
      margin: 10mm;
    }
    @media print {
      body {
        width: 100%;
        margin: 0;
      }
      .invoice-container {
        width: 100%;
      }
      .no-print {
        display: none;
      }
      table {
        width: 100%;
        table-layout: fixed;
      }
    }
  </style>
</head>
<body class="bg-gray-100">
@php
    use App\Models\Company;
    $company = Company::find(session('company_id'));
@endphp

<div class="invoice-container max-w-full mx-auto bg-white p-6">
  <div class="flex items-center justify-between border-b pb-4">
    <div class="flex items-center space-x-4">
      <img src="{{ $company->avatar ? Storage::disk('spaces')->url($company->avatar) : asset('images/user-dummy-img.jpg') }}" alt="Company Logo" class="h-24 w-24 rounded-md">
      <div>
        <h1 class="text-2xl font-bold text-green-700 uppercase py-1">{{ $company->name }}</h1>
        <p class="text-sm text-gray-600 mb-1">{{ $company->address }}</p>
        <p class="text-sm text-gray-600 mb-1">Email: {{ $company->email }}</p>
        <p class="text-sm text-gray-600">Phone: {{ $company->phone }}</p>
      </div>
    </div>
    <div class="text-right">
      <h2 class="text-2xl font-bold uppercase mb-1 text-black">Finished Goods Stock Report</h2>
      <p class="text-sm text-dark-600 mb-1">From:
      <span class="font-bold">{{ \Carbon\Carbon::parse($firstDate)->format('d-m-Y') }}</span> to
        <span class="font-bold">{{ \Carbon\Carbon::parse($lastDate)->format('d-m-Y') }}</span></p>
    </div>
  </div>

  <div class="mt-6">
    @php
        $groupedItems = [];
        $grandTotalOpeningBalance = 0;
        $grandTotalClosingBalance = 0;
        $grandTotalAmount = 0;
        $grandTotalSale = 0;
        $grandTotalProduction = 0;
    @endphp
    @foreach ($ledger as $entry)
        @php
            // Group items by category
            $groupedItems[$entry['category']][] = $entry;
        @endphp
    @endforeach

    @foreach ($groupedItems as $category => $items)
        <table class="min-w-full table-auto border-collapse border border-gray-300">
            <thead>
                <tr>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600 w-2">#</th>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600 w-1/3">Product Name</th>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Opening Balance <br/> <small>(Bags)</small></th>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Production  <br/> <small>(Bags)</small></th>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Sale  <br/> <small>(Bags)</small></th>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Closing Balance  <br/> <small>(Bags)</small></th>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Price  <br/> <small>(Per Bag)</small></th>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Amount <br/><small>(Rs.)</small></th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalOpeningBalance = 0;
                    $totalClosingBalance = 0;
                    $totalAmount = 0;
                    $totalSale = 0;
                    $totalProduction = 0;
                    $counter=1;
                @endphp
                <div class="mb-0">
                    <div class="bg-gray-200 text-black text-left py-1 px-5 font-semibold text-l mb-0 uppercase">
                        {{ $category ?? 'Uncategorized' }}
                    </div>

                    @foreach ($items as $item)
                        <tr>
                            <td class="px-2 py-1 border-b text-sm text-gray-700">{{ $counter }}</td>
                            <td class="px-2 py-1 border-b text-sm text-gray-700">{{ $item['product_name'] }}</td>
                            <td class="px-2 py-1 border-b text-sm text-gray-700">
                                @if ($item['opening_balance'] < 0)
                                    <span class="text-red-500"> ({{ number_format(abs($item['opening_balance'])) }})</span>
                                @elseif ($item['opening_balance'] == 0)
                                    -
                                @else
                                    {{ number_format($item['opening_balance']) }}
                                @endif
                            </td>
                            <td class="px-2 py-1 border-b text-sm text-gray-700">
                                @if ($item['production_bags'] == 0)
                                    -
                                @else
                                    {{ number_format($item['production_bags']) }}
                                @endif
                            </td>
                            <td class="px-2 py-1 border-b text-sm text-gray-700">
                                @if ($item['sale_bags'] == 0)
                                    -
                                @else
                                    {{ number_format($item['sale_bags']) }}
                                @endif
                            </td>
                            <td class="px-2 py-1 border-b text-sm text-gray-700">
                                @if ($item['current_balance'] < 0)
                                    <span class="text-red-500"> ({{ number_format(abs($item['current_balance'])) }})</span>
                                @elseif ($item['current_balance'] == 0)
                                    -
                                @else
                                    {{ number_format($item['current_balance']) }}
                                @endif
                            </td>
                            <td class="px-2 py-1 border-b text-sm text-gray-700"> {{ number_format($item['average_price'], 2) }} </td>
                            <td class="px-2 py-1 border-b text-sm text-gray-700">
                                @php
                                    $currentBalance = number_format($item['current_balance'], 2, '.', '');
                                    $averagePrice = number_format($item['average_price'], 2, '.', '');
                                    $amount = $currentBalance * $averagePrice;
                                @endphp

                                @if ($amount < 0)
                                    <span class="text-red-500">({{ number_format(abs($amount), 2) }}) </span>
                                @elseif ($amount == 0)
                                    -
                                @else
                                    {{ number_format($amount, 2) }}
                                @endif
                            </td>
                        </tr>

                        @php
                            $totalOpeningBalance += $item['opening_balance'];
                            $totalClosingBalance += $item['current_balance'];
                            $totalAmount += $amount;
                            $totalSale += $item['sale_bags'];
                            $totalProduction += $item['production_bags'];
                            $counter += 1;
                        @endphp

                    @endforeach

                    <tr class="bg-gray-100">
                        <td class="px-2 py-1 border-b text-center text-sm font-bold text-gray-700" colspan="2">Total</td>
                        <td class="px-2 py-1 border-b text-sm font-bold text-gray-700"> {{ number_format($totalOpeningBalance) }} </td>
                        <td class="px-2 py-1 border-b text-sm font-bold text-gray-700"> {{ number_format($totalProduction) }} </td>
                        <td class="px-2 py-1 border-b text-sm font-bold text-gray-700"> {{ number_format($totalSale) }} </td>
                        <td class="px-2 py-1 border-b text-sm font-bold text-gray-700"> {{ number_format($totalClosingBalance) }} </td>
                        <td class="px-2 py-1 border-b text-sm font-bold text-gray-700"> - </td>
                        <td class="px-2 py-1 border-b text-sm font-bold text-gray-700"> {{ number_format($totalAmount, 2) }} </td>
                    </tr>

                    @php
                        $grandTotalOpeningBalance += $totalOpeningBalance;
                        $grandTotalClosingBalance += $totalClosingBalance;
                        $grandTotalAmount += $totalAmount;
                        $grandTotalSale += $totalSale;
                        $grandTotalProduction += $totalProduction;
                    @endphp



                </div>
        </table>
    @endforeach

  <!-- Grand Total Row -->
<table class="min-w-full table-auto border-collapse border border-gray-300 mt-4 hidden">
    <tbody>
        <tr class="bg-gray-200">
            <!-- Grand Total cell with the correct colspan -->
            <td class="px-2 py-1 border-b text-center text-sm font-semibold text-gray-600" colspan="2" width="200px;">Grand Total</td>

            <!-- Ensure there are 8 total columns in this row -->
            <td class="px-2 py-1 border-b text-sm font-semibold text-gray-600">{{ number_format($grandTotalOpeningBalance) }}</td>
            <td class="px-2 py-1 border-b  text-center  text-sm font-semibold text-gray-600">{{ number_format($grandTotalProduction) }}</td>
            <td class="px-2 py-1 border-b text-center text-sm font-semibold text-gray-600">{{ number_format($grandTotalSale) }}</td>
            <td class="px-2 py-1 border-b  text-sm font-semibold text-gray-600">{{ number_format($grandTotalClosingBalance) }}</td>
            <td class="px-2 py-1 border-b text-center text-sm font-semibold text-gray-600"> - </td>
            <td class="px-2 py-1 border-b text-center text-sm font-semibold text-gray-600">{{ number_format($grandTotalAmount, 2) }}</td>
        </tr>
    </tbody>
</table>


  </div>
</div>

</body>
</html>
