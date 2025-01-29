<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}">
  <title>Stock Report | QuickERP</title>
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
      <h2 class="text-2xl font-bold uppercase mb-1 text-black">Stock Report</h2>
      <p class="text-sm text-dark-600 mb-1">Form
        <span class="font-bold">{{ \Carbon\Carbon::parse($firstDate)->format('d-m-Y') }}</span> to
        <span class="font-bold">{{ \Carbon\Carbon::parse($lastDate)->format('d-m-Y') }}</span>
    </p>
    </div>
  </div>

  <div class="mt-6">
    @php
        $groupedItems = [];
        $grandTotalOpeningBalance = 0;
        $grandTotalPurchases = 0;
        $grandTotalAvailableStock = 0;
        $grandTotalConsumption = 0;
        $grandTotalShortage = 0;
        $grandTotalExccess = 0;
        $grandTotalReturn = 0;
        $grandTotalClosingBalance = 0;
        $grandTotalValueConsumed = 0;
        $grandTotalValueClosingStock = 0;
        $grandTotalValueOpeningStock = 0;
    @endphp

    @foreach ($ledger as $entry)
        @php
            // Group items by category
            $groupedItems[$entry['category']][] = $entry;
        @endphp
    @endforeach

    @foreach ($groupedItems as $category => $items)
        @php
            // Reset totals for each category
            $totalOpeningBalance = 0;
            $totalPurchases = 0;
            $totalAvailableStock = 0;
            $totalConsumption = 0;
            $totalShortage=0;
            $totalExccess = 0;
            $totalReturn = 0;
            $totalClosingBalance = 0;
            $totalValueConsumed = 0;
            $totalValueClosingStock = 0;
            $totalValueOpeningStock = 0;
            $counter = 1;
        @endphp

        <table class="min-w-full table-auto border-collapse border border-gray-300" style="table-layout: fixed; width: 100%;">

        @foreach ($items as $item)
            @if($loop->first)
            <thead>
        <tr>
            <th style="width: 30px;" class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">#</th>
            <th style="width: 200px;" class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Item Name</th>
            <th style="width: 100px;" class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Opening <br/> <small>(Stock)</small></th>
            <th style="width: 100px;" class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">
                @if($item['item_type'] =='purchase' )
                    Purchases <br/> <small>(Inwards)</small>
                @else
                    Production <br/> <small>(Bags)</small>
                @endif
            </th>
            <th style="width: 100px;" class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Available <br/> <small>(Stock)</small></th>
            <th style="width: 100px;" class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">
                @if($item['item_type'] =='purchase' )
                    Consumed <br/> <small>(Stock)</small>
                @else
                    Sold <br/> <small>(Bags)</small>
                @endif
            </th>
            <th style="width: 80px;" class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Shortage <br/> <small>(Stock)</small></th>
            <th style="width: 80px;" class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Excess <br/> <small>(Stock)</small></th>
            <th style="width: 80px;" class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Return <br/> <small>(Stock)</small></th>
            <th style="width: 100px;" class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Closing <br/> <small>(Stock)</small></th>
            <th style="width: 100px;" class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">
                @if($item['item_type'] =='purchase' )
                    Rate <br/> <small>(Kg/Unit)</small>
                @else
                    Rate <br/> <small>(Per Bag)</small>
                @endif
            </th>
            <th style="width: 100px;" class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Value of Stock <br/> <small>(Consumed)</small></th>
            <th style="width: 100px;" class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Value of Stock <br/> <small>(Opening)</small></th>
            <th style="width: 100px;" class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Value of Stock <br/> <small>(Closing)</small></th>
        </tr>
    </thead>
            @endif

            <tbody>
                <div class="mb-0">
                    @if($loop->first)
                        <div class="bg-gray-200 text-black text-center py-1 px-5 font-semibold text-l mb-0 uppercase mt-4">
                            {{ $category ?? 'Uncategorized' }}
                        </div>
                    @endif

                        @if ($item['opening_balance'] != '00000000700000000') <!-- Check if the balance is not 0 -->
                            <tr>
                                <td class="px-2 py-1 border-b text-sm text-gray-700">{{ $counter }}</td>
                                <td class="px-2 py-1 border-b text-sm text-black-900  uppercase">{{ $item['item_name'] }}</td>
                                <td class="px-2 py-1 border-b text-sm text-gray-700 {{ $item['opening_balance'] < 0 ? 'text-red-700' : '' }}">
                                    @if ($item['opening_balance'] < 0)
                                        ({{ number_format(abs($item['opening_balance']), 2) }})
                                    @else
                                         {{ $item['opening_balance'] == 0 ? '-' : (number_format($item['opening_balance'], 3)) }}
                                    @endif
                                </td>
                                <td class="px-2 py-1 border-b text-sm text-green-700">
                                     {{ $item['total_purchases'] == 0 ? '-' : (number_format($item['total_purchases'], 3)) }}</td>

                                <td class="px-2 py-1 border-b text-sm text-gray-700">{{ $item['available_balance'] == 0 ? '-' : (number_format($item['available_balance'], 3)) }}</td>
                                <td class="px-2 py-1 border-b text-sm text-red-700">{{ $item['total_consumption'] == 0 ? '-' : (number_format($item['total_consumption'], 3)) }}</td>

                                <td class="px-2 py-1 border-b text-sm text-red-600"> {{ $item['total_shortage'] == 0 ? '-' : (number_format($item['total_shortage'], 3)) }}</td>
                                <td class="px-2 py-1 border-b text-sm text-green-700">{{ $item['total_exccess'] == 0 ? '-' : (number_format($item['total_exccess'], 3)) }}</td>

                                <td class="px-2 py-1 border-b text-sm text-green-700">{{ $item['total_return'] == 0 ? '-' : (number_format($item['total_return'], 3)) }}</td>


                                <td class="px-2 py-1 border-b text-sm text-gray-700 {{ $item['closing_balance'] < 0 ? 'text-red-700' : '' }}">
                                    @if ($item['closing_balance'] < 0)
                                        ({{ number_format(abs($item['closing_balance']), 2) }})
                                    @else
                                        {{ $item['closing_balance'] == 0 ? '-' : (number_format($item['closing_balance'], 3)) }}
                                    @endif
                                </td>


                                <td class="px-2 py-1 border-b text-sm text-gray-700">
                                    {{ $item['average_price'] == 0 ? '-' : (number_format($item['average_price'], 3)) }}
                                </td>
                                <td class="px-2 py-1 border-b text-sm text-gray-700 {{ $item['value_of_consumption'] < 0 ? 'text-red-600' : '' }}">


                                @if($item['item_type'] =='purchase' )

                                    @if ($item['value_of_consumption'] < 0)
                                        ({{ number_format(abs($item['value_of_consumption']), 2) }})
                                    @else
                                    {{ $item['value_of_consumption'] == 0 ? '-' : (number_format($item['value_of_consumption'], 3)) }}
                                    @endif

                                @else
                                    -

                                @endif


                                </td>

                                <td class="px-2 py-1 border-b text-sm text-gray-700 {{ $item['value_of_closing_stock'] < 0 ? 'text-red-600' : '' }}">
                                    @if ($item['value_of_opening_stock'] < 0)
                                        ({{ number_format(abs($item['value_of_opening_stock']), 2) }})
                                    @else
                                    {{ $item['value_of_opening_stock'] == 0 ? '-' : (number_format($item['value_of_opening_stock'], 3)) }}
                                    @endif
                                </td>


                                <td class="px-2 py-1 border-b text-sm text-gray-700 {{ $item['value_of_closing_stock'] < 0 ? 'text-red-600' : '' }}">
                                    @if ($item['value_of_closing_stock'] < 0)
                                        ({{ number_format(abs($item['value_of_closing_stock']), 2) }})
                                    @else
                                    {{ $item['value_of_closing_stock'] == 0 ? '-' : (number_format($item['value_of_closing_stock'], 3)) }}
                                    @endif
                                </td>
                            </tr>

                            @php
                                $totalOpeningBalance += $item['opening_balance'];
                                $totalPurchases += $item['total_purchases'];
                                $totalAvailableStock += $item['available_balance'];
                                $totalConsumption += $item['total_consumption'];
                                $totalShortage += $item['total_shortage'];
                                $totalExccess += $item['total_exccess'];
                                $totalReturn += $item['total_return'];
                                $totalClosingBalance += $item['closing_balance'];
                                $totalValueConsumed += $item['value_of_consumption'];
                                $totalValueClosingStock += $item['value_of_closing_stock'];
                                $totalValueOpeningStock += $item['value_of_opening_stock'];

                                $grandTotalOpeningBalance += $item['opening_balance'];
                                $grandTotalPurchases += $item['total_purchases'];
                                $grandTotalAvailableStock += $item['available_balance'];
                                $grandTotalConsumption += $item['total_consumption'];
                                $grandTotalShortage += $item['total_shortage'];
                                $grandTotalExccess += $item['total_exccess'];
                                $grandTotalReturn += $item['total_return'];
                                $grandTotalClosingBalance += $item['closing_balance'];
                               // $grandTotalValueConsumed += $item['value_of_consumption'];
                                $grandTotalValueClosingStock += $item['value_of_closing_stock'];
                                $grandTotalValueOpeningStock += $item['value_of_opening_stock'];

                                $counter++;
                            @endphp
                        @endif
                    @endforeach

                    <tr class="bg-gray-100">
                        <td class="px-2 py-1 border-b text-center text-sm font-bold text-gray-700" colspan="2">Total of {{ $category ?? 'Uncategorized' }} :</td>
                        <td class="px-2 py-1 border-b text-sm font-bold text-gray-700">{{ number_format($totalOpeningBalance, 3) }}</td>
                        <td class="px-2 py-1 border-b text-sm font-bold text-green-700">{{ $totalPurchases == 0 ? '-' : (number_format($totalPurchases, 3)) }}</td>
                        <td class="px-2 py-1 border-b text-sm font-bold text-gray-700">{{ $totalAvailableStock == 0 ? '-' : (number_format($totalAvailableStock, 3)) }}</td>
                        <td class="px-2 py-1 border-b text-sm font-bold text-red-700">
                        {{ $totalConsumption == 0 ? '-' : (number_format($totalConsumption, 3)) }}
                        </td>
                        <td class="px-2 py-1 border-b text-sm font-bold text-red-700">
                        {{ $totalShortage == 0 ? '-' : (number_format($totalShortage, 3)) }}
                        </td>
                        <td class="px-2 py-1 border-b text-sm font-bold text-green-700">
                        {{ $totalExccess == 0 ? '-' : (number_format($totalExccess, 3)) }}</td>

                        <td class="px-2 py-1 border-b text-sm font-bold text-green-700">
                        {{ $totalReturn == 0 ? '-' : (number_format($totalReturn, 3)) }}</td>


                        <td class="px-2 py-1 border-b text-sm font-bold text-gray-700">
                        {{ $totalClosingBalance == 0 ? '-' : (number_format($totalClosingBalance, 3)) }}</td>
                        <td class="px-2 py-1 border-b text-sm font-bold text-gray-700">-</td>
                        <td class="px-2 py-1 border-b text-sm font-bold text-gray-700">

                            @if($item['item_type'] =='purchase' )
                                {{ $totalValueConsumed == 0 ? '-' : (number_format($totalValueConsumed, 3)) }}

                                @php
                                $grandTotalValueConsumed += $totalValueConsumed;
                                @endphp

                            @else


                            @endif

                    </td>

                        <td class="px-2 py-1 border-b text-sm font-bold text-gray-700">{{ number_format($totalValueOpeningStock, 3) }}</td>

                        <td class="px-2 py-1 border-b text-sm font-bold text-gray-700">{{ number_format($totalValueClosingStock, 3) }}</td>
                    </tr>
                </div>
            @endforeach




             <!-- Grand Total Row -->
            <tfoot class="mt-5">
            <tr class="bg-black mt-5">
    <td class="px-2 py-1 border-b text-center text-sm font-bold text-white" colspan="2">Grand Total:</td>
    <td class="px-2 py-1 border-b text-sm font-bold text-white">{{ number_format($grandTotalOpeningBalance, 3) }}</td>
    <td class="px-2 py-1 border-b text-sm font-bold text-white">{{ $grandTotalPurchases == 0 ? '-' : (number_format($grandTotalPurchases, 3)) }}</td>
    <td class="px-2 py-1 border-b text-sm font-bold text-white">{{ $grandTotalAvailableStock == 0 ? '-' : (number_format($grandTotalAvailableStock, 3)) }}</td>
    <td class="px-2 py-1 border-b text-sm font-bold text-white">
        {{ $grandTotalConsumption == 0 ? '-' : (number_format($grandTotalConsumption, 3)) }}
    </td>
    <td class="px-2 py-1 border-b text-sm font-bold text-white">
        {{ $grandTotalShortage == 0 ? '-' : (number_format($grandTotalShortage, 3)) }}
    </td>
    <td class="px-2 py-1 border-b text-sm font-bold text-white">
        {{ $grandTotalExccess == 0 ? '-' : (number_format($grandTotalExccess, 3)) }}
    </td>

    <td class="px-2 py-1 border-b text-sm font-bold text-white">
        {{ $grandTotalReturn == 0 ? '-' : (number_format($grandTotalReturn, 3)) }}
    </td>

    <td class="px-2 py-1 border-b text-sm font-bold text-white">
        {{ $grandTotalClosingBalance == 0 ? '-' : (number_format($grandTotalClosingBalance, 3)) }}
    </td>
    <td class="px-2 py-1 border-b text-sm font-bold text-white">-</td>
    <td class="px-2 py-1 border-b text-sm font-bold text-white">
        {{ $grandTotalValueConsumed == 0 ? '-' : (number_format($grandTotalValueConsumed, 3)) }}
    </td>
    <td class="px-2 py-1 border-b text-sm font-bold text-white">{{ number_format($grandTotalValueOpeningStock, 3) }}</td>
    <td class="px-2 py-1 border-b text-sm font-bold text-white">{{ number_format($grandTotalValueClosingStock, 3) }}</td>
</tr>
            </tfoot>
        </table>
    </div>



</div>

</body>
</html>
