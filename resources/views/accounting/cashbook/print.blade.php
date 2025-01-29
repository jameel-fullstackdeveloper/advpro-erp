<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt Voucher</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Custom styles for print */
        @media print {
            .no-print {
                display: none;
            }

            /* Set paper size to A4, portrait orientation, and remove margins */
            @page {
                size: A4 portrait;
                margin: 0;
            }

            /* Ensure content fits within the margins */
            body {
                margin: 0;
                padding: 0;
            }

            /* Adjust content to fit within the page, considering no margins */
            .max-w-4xl {
                max-width: 100%;
            }

            .voucher-header,
            .voucher-details,
            .voucher-footer,
            .voucher-signature {
                margin-left: 10mm;
                margin-right: 10mm;
            }
        }

    </style>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white  p-8">

         <!-- Company Info -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <img src="{{ asset('path-to-your-logo/logo.png') }}" alt="Company Logo" class="h-16">
            </div>
            <div class="text-right">
                <h2 class="text-xl font-bold text-gray-800">{{ $company->name }}</h2>
                <p class="text-sm text-gray-600">{{ $company->address }}</p>
                <p class="text-sm text-gray-600">Phone: {{ $company->phone }}</p>
                <p class="text-sm text-gray-600">Email: {{ $company->email }}</p>
            </div>
        </div>

        <!-- Voucher Header -->
        <div class="voucher-header mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Receipt Voucher</h1>
            <p class="text-sm text-gray-600">Date: {{ \Carbon\Carbon::parse($voucher->date)->format('d-m-Y') }}</p>
            <p class="text-sm text-gray-600">Voucher No: <span class="font-semibold">{{ $voucher->reference_number }}</span></p>
            <p class="text-sm text-gray-600">Description: {{ $voucher->description }}</p>
        </div>

        <!-- Voucher Details -->
        <div class="voucher-details">
            <table class="w-full border-collapse border border-gray-200">
                <thead>
                    <tr>
                        <th class="border border-gray-200 bg-gray-50 px-4 py-2 text-left text-sm font-semibold text-gray-600">Account Title</th>
                        <th class="border border-gray-200 bg-gray-50 px-4 py-2 text-right text-sm font-semibold text-gray-600">Debit</th>
                        <th class="border border-gray-200 bg-gray-50 px-4 py-2 text-right text-sm font-semibold text-gray-600">Credit</th>
                        <th class="border border-gray-200 bg-gray-50 px-4 py-2 text-left text-sm font-semibold text-gray-600">Narration</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($voucher->voucherDetails as $detail)
                    <tr class="bg-white even:bg-gray-50">
                        <td class="border border-gray-200 px-4 py-2 text-sm text-gray-700">{{ $detail->account->name }}</td>
                        <td class="border border-gray-200 px-4 py-2 text-right text-sm text-gray-700">{{ $detail->type === 'debit' ? number_format($detail->amount, 2) : '' }}</td>
                        <td class="border border-gray-200 px-4 py-2 text-right text-sm text-gray-700">{{ $detail->type === 'credit' ? number_format($detail->amount, 2) : '' }}</td>
                        <td class="border border-gray-200 px-4 py-2 text-sm text-gray-700">{{ $detail->narration }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Voucher Footer -->
        <div class="voucher-footer mt-6 hidden">
            <p class="text-sm text-gray-600">Total Debit: <span class="font-semibold">{{ number_format($voucher->voucherDetails->where('type', 'debit')->sum('amount'), 2) }}</span></p>
            <p class="text-sm text-gray-600">Total Credit: <span class="font-semibold">{{ number_format($voucher->voucherDetails->where('type', 'credit')->sum('amount'), 2) }}</span></p>
            <p class="text-sm text-gray-600">Balance: <span class="font-semibold">{{ number_format($voucher->voucherDetails->where('type', 'debit')->sum('amount') - $voucher->voucherDetails->where('type', 'credit')->sum('amount'), 2) }}</span></p>
        </div>

        <!-- Accountant Signature -->
        <div class="mt-12">
            <p class="text-sm text-gray-600">Authorized Signature: ___________________________</p>
        </div>

        <!-- Print Button -->
        <div class="mt-8 no-print">
            <button onclick="window.print()" class="px-4 py-2 bg-blue-500 text-white font-semibold rounded-lg shadow-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75">Print Voucher</button>
        </div>
    </div>
</body>
</html>
