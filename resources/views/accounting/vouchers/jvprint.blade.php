<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cash Voucher #{{ $voucher->reference_number}} | QuickERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
      @media print {
        .no-print {
          display: none;
        }
        .invoice-container {
          border: none;
        }
      }
    </style>
  </head>
  <body class="bg-gray-100">
    @php
        use App\Models\Company;
        $company = Company::find(session('company_id'));

        function convert_number_to_words($number) {
      $words = [
          '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
          'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen',
          'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'
      ];
      $thousands = ['', 'Thousand', 'Million', 'Billion'];
      $number = (int)$number;
      $word = '';

      if ($number == 0) {
          return 'Zero';
      }

      $partCount = 0;
      while ($number > 0) {
          $part = $number % 1000;
          if ($part > 0) {
              $word = convert_three_digit_number_to_words($part) . ' ' . $thousands[$partCount] . ' ' . $word;
          }
          $number = (int)($number / 1000);
          $partCount++;
      }

      return trim($word);
  }

  function convert_three_digit_number_to_words($number) {
      $words = [
          '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
          'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen',
          'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'
      ];

      $word = '';
      if ($number >= 100) {
          $hundreds = (int)($number / 100);
          $word .= $words[$hundreds] . ' Hundred ';
          $number = $number % 100;
      }

      if ($number > 0 && $number < 20) {
          $word .= $words[$number];
      } else if ($number >= 20) {
          $tens = (int)($number / 10);
          $ones = $number % 10;
          $word .= $words[$tens + 18]; // 18 + index of tens word
          if ($ones > 0) {
              $word .= '-' . $words[$ones];
          }
      }

      return trim($word);
  }

    @endphp

    <div class="invoice-container max-w-4xl mx-auto bg-white p-6 border rounded-md">
      <div class="flex items-center justify-between border-b pb-4">
        <!-- Left side: Logo -->
        <div class="flex items-center space-x-4">
          <img
            src="{{ $company->avatar ? Storage::disk('spaces')->url($company->avatar) : asset('images/user-dummy-img.jpg') }}"
            alt="Company Logo"
            class="h-24 w-24 rounded-md"
          >
          <div>
            <h1 class="text-2xl font-bold text-green-700 uppercase py-1">{{ $company->name }}</h1>
            <p class="text-sm text-gray-600  mb-1">{{ $company->address }}</p>
            <p class="text-sm text-gray-600">Phone: {{ $company->phone }}</p>
        </div>
        </div>
        <!-- Right side: Company details -->
        <div class="text-right">
         <p class="text-sm text-gray-600 mb-1">&nbsp;</p>
         <p class="text-sm text-gray-600  mb-1">Email: {{ $company->email }}</p>

          <p class="text-sm text-gray-600  mb-1">STR #: {{ $company->strn ?? 'N/A' }}</p>
          <p class="text-sm text-gray-600 ">NTN #: {{ $company->ntn ?? 'N/A' }}</p>
        </div>
      </div>



        <div class="flex justify-between items-start mt-4">
        <!-- Left side: Customer details -->
        <div>
          <p class="text-sm text-gray-600  mb-1 mt-2"><strong>Date:</strong> {{ \Carbon\Carbon::parse($voucher->date)->format('d-m-Y') }}</p>
          <p class="text-sm text-gray-600  mb-1"></p>

        </div>
        <!-- Right side: Invoice details -->
        <div class="text-right">
          <h2 class="text-lg font-bold uppercase  mb-1">Receipt Voucher</h2>
          <!--<p class="text-sm text-gray-600  mb-1">Description: {{ $voucher->description }}</p>-->

        </div>
      </div>

        <!-- Voucher Details Table -->
        <table class="w-full border-collapse border mt-6 text-sm">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                <th class="border px-2 py-2" style="width: 15%;">Voucher #</th>
          <th class="border px-2 py-2" style="width: 25%;">Account Title</th>
          <th class="border px-2 py-2" style="width: 30%;">Narration</th>
          <th class="border px-2 py-2 text-right" style="width: 15%;">Debit</th>
          <th class="border px-2 py-2 text-right" style="width: 15%;">Credit</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalDebit = 0;
                    $totalCredit = 0;
                @endphp
                @foreach ($voucher->voucherDetails as $index => $detail)
                <tr class="bg-white even:bg-gray-50">
                    <!-- Display Voucher Number only in the first row -->
                    @if ($index == 0)
                    <td class="border border-gray-200 px-4 py-2 text-sm text-gray-700" rowspan="{{ count($voucher->voucherDetails) }}">{{ $voucher->reference_number }}</td>
                    @endif
                    <td class="border border-gray-200 px-4 py-2 text-sm text-gray-700 font-bold">{{ $detail->account->name }}</td>
                    <td class="border border-gray-200 px-4 py-2 text-sm text-gray-700">{{ $detail->narration }}</td>
                    <td class="border border-gray-200 px-4 py-2 text-right text-sm text-gray-700">
                        @if($detail->type === 'debit')
                            @php $totalDebit += $detail->amount; @endphp
                            {{ number_format($detail->amount, 2) }}
                        @endif
                    </td>
                    <td class="border border-gray-200 px-4 py-2 text-right text-sm text-gray-700">
                        @if($detail->type === 'credit')
                            @php $totalCredit += $detail->amount; @endphp
                            {{ number_format($detail->amount, 2) }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-bold">
                    <td colspan="3" class="border px-4 py-2 text-sm text-gray-700 text-right">Total</td>
                    <td class="border px-4 py-2 text-right text-sm text-gray-700">{{ number_format($totalDebit, 2) }}</td>
                    <td class="border px-4 py-2 text-right text-sm text-gray-700">{{ number_format($totalCredit, 2) }}</td>
                </tr>
            </tfoot>
        </table>


            <!-- Amount in words -->
    <p class="text-sm text-gray-600 mt-4"><strong>Amount in Words:</strong> {{ convert_number_to_words($totalDebit) }} Only</p>


       <!-- Accountant Signature -->
<div class="mt-10 flex justify-between items-end">
    <!-- Created By Section -->
    <div class="text-center">
        <h2 class="text-sm font-bold text-center">Created By:</h2>
        <p class="text-sm text-gray-700 mt-2">{{ $voucher->createdBy->name }}</p> <!-- Display username -->
        <p class="text-sm text-gray-500">{{ $voucher->created_at->format('d-m-Y h:i A') }}</p> <!-- Display creation date and time -->
        <div class="border-t border-gray-400 w-48 mt-2 mx-auto"></div> <!-- Signature Line -->
    </div>

    <!-- Account Manager Section -->
    <div class="text-center">
        <h2 class="text-sm font-bold text-center">Account Manager:</h2>
        <p class="text-sm text-gray-700 mt-2">&nbsp;</p>
        <p class="text-sm text-gray-700">&nbsp;</p>
        <div class="border-t border-gray-400 w-48 mt-2 mx-auto"></div> <!-- Signature Line -->
    </div>

    <!-- Director Section -->
    <div class="text-center">
        <h2 class="text-sm font-bold text-center">Director:</h2>
        <p class="text-sm text-gray-700 mt-2">&nbsp;</p>
        <p class="text-sm text-gray-700">&nbsp;</p>
        <div class="border-t border-gray-400 w-48 mt-2 mx-auto"></div> <!-- Signature Line -->
    </div>
</div>



        <!-- Print Button -->
        <div class="mt-8 text-right no-print">
            <button onclick="window.print()" class="px-4 py-2 bg-blue-500 text-white font-semibold rounded-lg shadow-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75">Print Voucher</button>
        </div>
    </div>
</body>
</html>
