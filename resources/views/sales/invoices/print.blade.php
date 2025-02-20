<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}">
    <title>Invoice #{{ $invoice->invoice_number }} | QuickERP</title>

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
            <p class="text-sm text-gray-600  mb-1">Email: {{ $company->email }}</p>
            <p class="text-sm text-gray-600">Phone: {{ $company->phone }}</p>
        </div>
        </div>
        <!-- Right side: Company details -->
        <div class="text-right">
         <p class="text-sm text-gray-600 mb-1">&nbsp;</p>
         <h2 class="text-3xl font-bold uppercase  mb-1">Invoice</h2>


          <p class="text-sm text-gray-600  mb-1"></p>
          <p class="text-sm text-gray-600 "></p>
        </div>
      </div>

      <div class="flex justify-between items-start mt-4">
        <!-- Left side: Customer details -->
        <div>
          <h2 class="text-sm font-bold  mb-1">To:</h2>
          <p class="text-sm text-dark-600 font-bold uppercase mb-1 ml-4">{{ $invoice->customer->name }}</p>
          <p class="text-sm text-gray-600  mb-1 ml-4">{{ $customerDetails->address ?? 'N/A' }}</p>
          <p class="text-sm text-gray-600  mb-1 ml-4">CNIC #: {{ $customerDetails->cnic ?? 'N/A' }}</p>
          <p class="text-sm text-gray-600  mb-1 ml-4">STRN #: {{ $customerDetails->strn ?? 'N/A' }}</p>
        </div>
        <!-- Right side: Invoice details -->
        <div class="text-right">
          <p class="text-sm text-gray-600  mb-1">&nbsp;</p>
          <p class="text-sm text-gray-600  mb-1">Invoice #: {{ $invoice->invoice_number }}</p>
          <p class="text-sm text-gray-600  mb-1">Date: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') }}</p>
          <p class="text-sm text-gray-600  mb-1">Vehicle No.: {{ $invoice->salesOrder->vehicle_no ?? 'N/A' }}</p>
        </div>
      </div>

      <table class="w-full border-collapse border mt-6 text-sm">
        <thead class="bg-gray-100 text-gray-700">
          <tr>
            <th class="border px-2 py-2" style="width: 5%;">#</th>
            <th class="border px-2 py-2 text-left" style="width: 30%;">Products</th>
            <th class="border px-2 py-2" style="width: 5%">Bags</th>
            <th class="border px-2 py-2" style="width: 5%;">Price</th>
            <th class="border px-2 py-2" style="width: 15%;">Amount <br/> <small>(Bags * Quantity)</small></th>
            <th class="border px-2 py-2" style="width: 10%;">Discount <br/> <small>(%)</small></th>
            <th class="border px-2 py-2" style="width: 10%;">Discount <br/> <small>(Per Bag)</small></th>
           <!-- <th class="border px-2 py-2" style="width: 10%;">Sales Tax</th>
            <th class="border px-2 py-2" style="width: 10%;">Further Tax</th>
            <th class="border px-2 py-2" style="width: 10%;">WHT</th>-->
            <th class="border px-2 py-2" style="width: 15%;">Net Amount <br/> <small>(Less Discount)</small></th>
          </tr>
        </thead>
        <tbody>
          @php
            $total_quantity = 0;
            $total_gross = 0;
            $total_discount = 0;
            $total_discount_per_bag = 0;
            $total_salestax= 0;
            $total_futhertax = 0;
            $total_wht = 0;
            $total_net = 0;
          @endphp
          @foreach ($invoice->items as $index => $item)
          @php
            $total_quantity += $item->quantity;
            $total_gross += $item->net_amount;
            $total_discount += $item->discount_amount;
            $total_discount_per_bag += $item->discount_per_bag_amount;
            $total_salestax += $item->sales_tax_amount;
            $total_futhertax += $item->further_sales_tax_amount;
            $total_wht += $item->advance_wht_amount;
            $total_net += $item->amount_incl_tax;
          @endphp
          <tr class="hover:bg-gray-50">
            <td class="border px-2 py-2 text-center">{{ $index + 1 }}</td>
            <td class="border px-2 py-2">{{ $item->product->name }}</td>
            <td class="border px-2 py-2 text-center">{{ $item->quantity }}</td>
            <td class="border px-2 py-2 text-right">{{ number_format($item->unit_price, 2) }}</td>
            <td class="border px-2 py-2 text-right">{{ number_format($item->net_amount, 2) }}

            </td>
            <td class="border px-2 py-2 text-right">{{ number_format($item->discount_amount, 2) }}
            <br/>
            <small>@ {{ $item->discount_rate, 2 }} %</small>
            </td>
            <td class="border px-2 py-2 text-right">{{ number_format($item->discount_per_bag_amount, 2) }}
            <br/>
            <small>@ {{ $item->discount_per_bag_rate, 2 }}</small>
            </td>

            <!--<td class="border px-2 py-2 text-right">{{ number_format($item->sales_tax_amount, 2) }}</td>
            <td class="border px-2 py-2 text-right">{{ number_format($item->further_sales_tax_amount, 2) }}</td>
            <td class="border px-2 py-2 text-right">{{ number_format($item->advance_wht_amount, 2) }}</td>-->
            <td class="border px-2 py-2 text-right">{{ number_format($item->amount_incl_tax, 2) }}</td>
          </tr>
          @endforeach
        </tbody>
        <tfoot class="bg-gray-50">
          <tr>
            <td colspan="2" class="border px-2 py-2 text-right font-bold">Total</td>
            <td class="border px-2 py-2 text-center font-bold">{{ $total_quantity }}</td>
            <td class="border px-2 py-2 text-center"> - </td>

            <td class="border px-2 py-2 text-right font-bold">{{ number_format($total_gross, 2) }}</td>
            <td class="border px-2 py-2 text-right font-bold">{{ number_format($total_discount, 2) }}</td>
            <td class="border px-2 py-2 text-right font-bold"">{{ number_format($total_discount_per_bag, 2) }}</td>
            <!--<td class="border px-2 py-2 text-right  font-bold">{{ number_format($total_salestax, 2) }}</td>
            <td class="border px-2 py-2 text-right  font-bold">{{ number_format($total_futhertax, 2) }}</td>
            <td class="border px-2 py-2  text-right  font-bold">{{ number_format($total_wht, 2) }}</td>-->
            <td class="border px-2 py-2 font-bold text-right">{{ number_format($total_net, 2) }}</td>
          </tr>
        </tfoot>
      </table>

     <!-- Amount in words -->
    <!--<p class="text-sm text-gray-600 mt-8 "><strong>Amount in Words:</strong> {{ convert_number_to_words($total_net) }} Only</p>-->


      <!-- Accountant Signature -->
      <div class="flex justify-between items-center mt-24">
        <div class="text-left">
          <h2 class="text-sm font-bold">Accountant Signature:</h2>
          <div class="border-t w-48 mt-4"></div>
        </div>
        <div class="text-right">
          <p class="text-xs text-gray-600">Authorized by {{ $company->name }}</p>
        </div>
      </div>

      <p class="text-xs text-gray-600 mt-16 text-center">
        This is a computer-generated invoice and does not require a stamp. Please contact us within 5 days if you find any discrepancies.
      </p>

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
