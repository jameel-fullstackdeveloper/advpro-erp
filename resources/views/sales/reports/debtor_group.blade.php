<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
      <!-- App favicon -->
      <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}">
    <title>Debtor Report | QuickERP</title>
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

        /*.group-total {
            page-break-before: always; /* Ensures a page break before each group */
        }*/
      }
    </style>
  </head>
  <body class="bg-gray-100">
    @php
        use App\Models\Company;
        $company = Company::find(session('company_id'));

        $groupedCustomers = $customers->groupBy(function ($customer) {
            return $customer->chartOfAccountGroup->name;
        });

        // Initialize grand totals
        $grandTotalBalance = 0;
        $grandTotalAmountDue = 0;
        $grandTotalBagsCurrentMonth = 0;
        $grandTotalBagsLastMonth = 0;
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
          <h2 class="text-2xl font-bold uppercase mb-1 text-black">Debtor Report</h2>
          <p class="text-sm text-dark-600 mb-1">as of: <span class="font-bold">{{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y, l') }} </span></p>
        </div>
      </div>

      <div class="mt-6">
        @foreach ($groupedCustomers as $groupName => $customersInGroup)
          <div class="mb-6">
            <div class="bg-gray-200 text-black text-left py-1 px-5 font-semibold text-l mb-0 uppercase group-total">
              {{ $groupName }}
            </div>

            @php
              $selectedMonth = \Carbon\Carbon::parse($selectedDate)->format('M-Y');
            @endphp

            <table class="min-w-full table-auto border-collapse border border-gray-300">
              <thead>
                <tr>
                  <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600 w-2">#</th>
                  <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600 w-1/3">Customer</th>
                  <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Balance</th>
                  <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Amount <br/>Due</th>
                  <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Bags <br/><small>({{ $selectedMonth }}) </small></th>

                  <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Sale
                  <br/><small>({{ $selectedMonth }}) </small>

                  </th>
                  <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Received
                  <br/><small>({{ $selectedMonth }}) </small>

                  </th>

                  <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Bags <br/>
                  <small>({{ \Carbon\Carbon::parse($selectedDate)->subMonth()->format('M-Y') }}) </small>
                  </th>
                </tr>
              </thead>
              <tbody>
                @php
                    $totalBalance = 0;
                    $totalAmountDue = 0;
                    $totalBagsCurrentMonth = 0;
                    $totalBagsLastMonth = 0;
                    $totalInvoiceAmount = 0;
                    $totalPaymentAmount = 0;
                    $counter = 1;
                @endphp

                @foreach ($customersInGroup as $customer)
                  @if ($customer->ledgerBalance != 0)
                    <tr>
                      <td class="px-2 py-1 border-b text-sm text-gray-700">{{ $counter++ }}</td>
                      <td class="px-2 py-1 border-b text-sm text-gray-700">{{ $customer->name }}</td>
                      <td class="px-2 py-1 border-b text-sm text-gray-700">
                        @if ($customer->ledgerBalance < 0)
                            <span class="text-yellow-400">({{ number_format(abs($customer->ledgerBalance), 2) }})</span>
                        @else
                            {{ number_format($customer->ledgerBalance, 2) }}
                        @endif
                      </td>
                      <td class="px-2 py-1 border-b text-sm">
                        @if ($customer->dueAmount != 0)
                            <span class=" text-red-500">{{ number_format($customer->dueAmount, 2) }}</span>
                        @else
                            -
                        @endif
                      </td>
                      <td class="px-2 py-1 border-b text-sm text-gray-700">
                        @if ($customer->currentmonthbags != 0)
                            {{ $customer->currentmonthbags }}
                        @else
                            -
                        @endif
                      </td>


                      <td class="px-2 py-1 border-b text-sm text-gray-700">
                            @if ($customer->invoiceAmount != 0)
                                {{ number_format($customer->invoiceAmount, 2) }} <!-- Sale Amount for Current Month -->
                            @else
                                -
                            @endif
                     </td>
                     <td class="px-2 py-1 border-b text-sm text-gray-700">

                     @if ($customer->paymentAmount != 0)
                            {{ number_format($customer->paymentAmount,2) }}
                        @else
                            -
                        @endif
                     </td>

                     <td class="px-2 py-1 border-b text-sm text-gray-700">

                        @if ($customer->lastmonthbags != 0)
                            {{ $customer->lastmonthbags }}
                        @else
                            -
                        @endif
                      </td>

                    </tr>
                    @php
                        $totalBalance += $customer->ledgerBalance;
                        $totalAmountDue += $customer->dueAmount;
                        $totalBagsCurrentMonth += $customer->currentmonthbags;
                        $totalBagsLastMonth += $customer->lastmonthbags;

                        $totalInvoiceAmount += $customer->invoiceAmount;
                        $totalPaymentAmount += $customer->paymentAmount;

                        // Add to grand total
                        $grandTotalBalance += $customer->ledgerBalance;
                        $grandTotalAmountDue += $customer->dueAmount;
                        $grandTotalBagsCurrentMonth += $customer->currentmonthbags;
                        $grandTotalBagsLastMonth += $customer->lastmonthbags;
                    @endphp
                  @endif
                @endforeach

                <tr class="bg-gray-100">
                    <td class="px-2 py-1 border-b text-center text-sm font-bold text-gray-700" colspan="2">Total</td>
                    <td class="px-2 py-1 border-b text-sm font-bold text-gray-700">{{ number_format($totalBalance, 2) }}</td>
                    <td class="px-2 py-1 border-b text-sm font-bold text-red-500">{{ number_format($totalAmountDue, 2) }}</td>
                    <td class="px-2 py-1 border-b text-sm font-bold text-gray-700">{{ $totalBagsCurrentMonth }}</td>
                    <td class="px-2 py-1 border-b text-sm font-bold text-gray-700">{{ number_format($totalInvoiceAmount,2) }}</td>
                    <td class="px-2 py-1 border-b text-sm font-bold text-gray-700">{{ number_format($totalPaymentAmount,2) }}</td>
                    <td class="px-2 py-1 border-b text-sm font-bold text-gray-700">{{ $totalBagsLastMonth }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        @endforeach

        <!-- Grand Total -->
        <div class="mt-6 bg-gray-200 text-black text-center py-2 font-semibold text-lg uppercase hidden">
          Grand Total
        </div>
        <table class="min-w-full table-auto border-collapse border border-gray-300 mt-2 hidden">
          <thead>
            <tr>
              <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Balance</th>
              <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Amount Due</th>
              <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Bags <small>({{ $selectedMonth }})</small></th>
              <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Bags <small>({{ \Carbon\Carbon::parse($selectedDate)->subMonth()->format('M-Y') }})</small></th>
            </tr>
          </thead>
          <tbody>
            <tr class="bg-gray-100">
              <td class="px-2 py-1 border-b text-sm font-bold text-gray-700">{{ number_format($grandTotalBalance, 2) }}</td>
              <td class="px-2 py-1 border-b text-sm font-bold text-red-500">{{ number_format($grandTotalAmountDue, 2) }}</td>
              <td class="px-2 py-1 border-b text-sm font-bold text-gray-700">{{ $grandTotalBagsCurrentMonth }}</td>
              <td class="px-2 py-1 border-b text-sm font-bold text-gray-700">{{ $grandTotalBagsLastMonth }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-10 text-right no-print">
        <button onclick="window.print()" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Print</button>
        <button onclick="handleBackButton()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Go Back</button>
      </div>
    </div>

    <script>
      function handleBackButton() {
        if (window.history.length > 1) {
          window.history.back();
        } else {
          alert("No previous page in the history stack.");
        }
      }
    </script>
  </body>
</html>
