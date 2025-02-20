<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}">
  <title>Cash & Bank Report | QuickERP</title>
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
      <h2 class="text-2xl font-bold uppercase mb-1 text-black">Cash and Bank Report</h2>
      <p class="text-sm text-dark-600 mb-1">From:
      <span class="font-bold">{{ \Carbon\Carbon::parse($firstDate)->format('d-m-Y') }}</span> to
        <span class="font-bold">{{ \Carbon\Carbon::parse($lastDate)->format('d-m-Y') }}</span></p>
    </div>
  </div>

  <div class="mt-6">

        <table class="min-w-full table-auto border-collapse border border-gray-300">
            <thead>

            </thead>

            <tbody>
    <!-- Cash Accounts Section -->
    <tr>
        <td colspan="6" class="text-center px-2 py-2 border text-sm font-bold text-gray-800 bg-gray-200">CASH ACCOUNTS</td>
    </tr>

    <tr>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600 w-2">#</th>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600 w-1/3">Account Title</th>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Opening Balance</th>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Receipts</th>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Payments </th>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Closing Balance</th>
                </tr>

    @php
        $cashTotalOpening = $cashTotalDebit = $cashTotalCredit = $cashTotalClosing = 0;
    @endphp

    @foreach ($cashAccounts as $account)
        @php
            $cashTotalOpening += $account->opening_balance;
            $cashTotalDebit += $account->debit;
            $cashTotalCredit += $account->credit;
            $cashTotalClosing += $account->closing_balance;
        @endphp
        <tr>
            <td class="px-2 py-1 border text-sm text-gray-600">{{ $loop->iteration }}</td>
            <td class="px-2 py-1 border text-sm text-gray-600">{{ $account->name }}</td>
             <!-- Opening Balance (Red if Negative) -->
            <td class="px-2 py-1 border text-sm
                {{ $account->opening_balance < 0 ? 'text-red-500 font-bold' : 'text-gray-600' }}">
                {{ $account->opening_balance < 0 ? '(' . number_format(abs($account->opening_balance)) . ')' : number_format($account->opening_balance) }}
            </td>


            <td class="px-2 py-1 border text-sm text-gray-600">{{ number_format($account->debit) }}</td>
            <td class="px-2 py-1 border text-sm text-gray-600">{{ number_format($account->credit) }}</td>
             <!-- Closing Balance (Red if Negative) -->
             <td class="px-2 py-1 border text-sm
                {{ $account->closing_balance < 0 ? 'text-red-500 font-bold' : 'text-gray-600' }}">
                {{ $account->closing_balance < 0 ? '(' . number_format(abs($account->closing_balance)) . ')' : number_format($account->closing_balance) }}
            </td>
        </tr>
    @endforeach

    <!-- Cash Total Row -->
    <tr class="bg-gray-100 font-bold">
        <td colspan="2" class="text-center px-2 py-1 border text-sm text-gray-800">Total of Cash Accounts:</td>
        <td class="px-2 py-1 border text-sm text-gray-800">{{ number_format($cashTotalOpening) }}</td>
        <td class="px-2 py-1 border text-sm text-gray-800">{{ number_format($cashTotalDebit) }}</td>
        <td class="px-2 py-1 border text-sm text-gray-800">{{ number_format($cashTotalCredit) }}</td>
        <td class="px-2 py-1 border text-sm text-gray-800">{{ number_format($cashTotalClosing) }}</td>
    </tr>

    <!-- Bank Accounts Section -->
    <tr class="bg-gray-200">
        <td colspan="6" class="text-center px-2 py-2 border text-sm font-bold text-gray-800">BANK ACCOUNTS</td>
    </tr>

    <tr>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600 w-2">#</th>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600 w-1/3">Account Ttilte</th>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Opening Balance</th>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Receipts</th>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Payments </th>
                    <th class="px-2 py-1 border-b text-left text-sm font-semibold text-gray-600">Closing Balance</th>
                </tr>

    @php
        $bankTotalOpening = $bankTotalDebit = $bankTotalCredit = $bankTotalClosing = 0;
    @endphp

    @foreach ($bankAccounts as $account)
        @php
            $bankTotalOpening += $account->opening_balance;
            $bankTotalDebit += $account->debit;
            $bankTotalCredit += $account->credit;
            $bankTotalClosing += $account->closing_balance;
        @endphp
        <tr>
            <td class="px-2 py-1 border text-sm text-gray-600">{{ $loop->iteration }}</td>
            <td class="px-2 py-1 border text-sm text-gray-600">{{ $account->name }}</td>

            <!-- Opening Balance (Red if Negative) -->
            <td class="px-2 py-1 border text-sm
                {{ $account->opening_balance < 0 ? 'text-red-500 font-bold' : 'text-gray-600' }}">
                {{ $account->opening_balance < 0 ? '(' . number_format(abs($account->opening_balance)) . ')' : number_format($account->opening_balance) }}
            </td>


            <td class="px-2 py-1 border text-sm text-gray-600">{{ number_format($account->debit) }}</td>
            <td class="px-2 py-1 border text-sm text-gray-600">{{ number_format($account->credit) }}</td>
             <!-- Closing Balance (Red if Negative) -->
             <td class="px-2 py-1 border text-sm
                {{ $account->closing_balance < 0 ? 'text-red-500 font-bold' : 'text-gray-600' }}">
                {{ $account->closing_balance < 0 ? '(' . number_format(abs($account->closing_balance)) . ')' : number_format($account->closing_balance) }}
            </td>

        </tr>
    @endforeach

    <!-- Bank Total Row -->
    <tr class="bg-gray-100 font-bold">
        <td colspan="2" class="text-center px-2 py-1 border text-sm text-gray-800">Total of Bank Accounts:</td>
        <td class="px-2 py-1 border text-sm text-gray-800">{{ number_format($bankTotalOpening, 2) }}</td>
        <td class="px-2 py-1 border text-sm text-gray-800">{{ number_format($bankTotalDebit, 2) }}</td>
        <td class="px-2 py-1 border text-sm text-gray-800">{{ number_format($bankTotalCredit, 2) }}</td>
        <td class="px-2 py-1 border text-sm text-gray-800">{{ number_format($bankTotalClosing, 2) }}</td>
    </tr>
</tbody>


    </table>




 </div>


</div>

</body>
</html>
