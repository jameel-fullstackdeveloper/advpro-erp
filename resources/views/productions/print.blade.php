<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
      @media print {
        .no-print {
          display: none;
        }
        .invoice-container {
          border: none;
        }
        /* Reduce font size for print */
        body {
            font-size: 10px;
        }
        table {
            font-size: 10px;
        }
        h2, p {
            font-size: 12px;
        }
        .text-l {
            font-size: 12px;
        }
      }
    </style>
</head>
<body class="bg-white-100">

@php
    use App\Models\Company;
    $company = Company::find(session('company_id'));
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
                <h1 class="text-2xl font-bold text-green-700 uppercase py-2">{{ $company->name }}</h1>
                <p class="text-sm text-gray-600  mb-1">{{ $company->address }}</p>
                <p class="text-sm text-gray-600  mb-1">Email: {{ $company->email }}</p>
                <p class="text-sm text-gray-600">Phone: {{ $company->phone }}</p>
            </div>
        </div>
        <!-- Right side: Company details -->
        <div class="text-right">
            <h2 class="text-1xl font-bold uppercase mb-1">Product based Consumption Report</h2>
        </div>
    </div>

    <div class="max-w-4xl mx-auto mt-4">
        <div class="mb-4">
            <div class="flex justify-between">
                <div>
                    <p class="font-semibold text-l">Date</p>
                    <p class="text-gray-700">{{ \Carbon\Carbon::parse($production->production_date)->format('d-m-Y, l') }}</p>
                </div>
                <div>
                    <p class="font-semibold text-l">Description</p>
                    <p class="text-gray-700">{{ $production->comments }}</p>
                </div>
            </div>
        </div>

        <!-- Finished Products Table -->
        <div class="mb-5">
            <table class="w-full border">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="py-2 px-2 text-left font-semibold">Product Name</th>
                        <th class="py-2 px-2 text-center font-semibold">Batch Executed</th>
                        <th class="py-2 px-2 text-center font-semibold">Expected Prodcution</th>
                        <th class="py-2 px-2 text-center font-semibold">Shortage</th>
                        <th class="py-2 px-2 text-center font-semibold">Excess</th>
                        <th class="py-2 px-2 text-center font-semibold">Actual Production</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalFinishedProducts = 0;
                    @endphp
                       <tr>
                            <td class="py-2 px-2 text-left font-semibold">{{ $production->product->name }}</td>
                            <td class="py-2 px-2 text-center font-semibold">{{ $production->lots }}</td>
                            <td class="py-2 px-2 text-center font-semibold">{{ $production->defaultbags_perlot }} </td>
                            <td class="py-2 px-2 text-center font-semibold">{{ $production->short_perlot }}</td>
                            <td class="py-2 px-2 text-center font-semibold">{{ $production->excess_perlot }}</td>
                            <td class="py-2 px-2 text-center font-semibold">{{ number_format($production->actual_produced) }} <small> Bags</small></td>
                        </tr>
                        @php
                            $totalFinishedProducts += $production->actual_produced;
                        @endphp
                </tbody>
            </table>

        </div>

        <!-- Raw Materials Table -->
        <div class="mb-4">
            <table class="w-full border">

                <tbody>

                    @php
            $totalRawMaterials = 0;
        @endphp

        <!-- Raw Materials Table -->
@foreach ($groupedMaterials as $groupName => $details)
    <tr class="bg-gray-200">
        <td class="py-2 px-2 text-left font-semibold">{{ $groupName }} </td> <!-- Group Name -->
        <td class="py-2 px-2 text-left font-semibold"> Used in 1 batch </td>
        <td class="py-2 px-2 text-right font-semibold"> Quantity </td>
    </tr>

    @foreach ($details as $detail)
        <tr>
            <td class="py-2 px-2 text-left font-semibold">{{ $detail->rawMaterial->name ?? 'Unknown' }}</td> <!-- Raw Material Name -->
            <td class="py-2 px-2 text-left"> {{ number_format($detail->quantity_used / $production->lots,3) }} </td>
            <td class="py-2 px-2 text-right">{{ $detail->quantity_used }}</td>
        </tr>
    @endforeach
@endforeach
                </tbody>
            </table>
            <div class="mt-2 font-semibold hidden">
                <p class="text-right">Total  Materials Used: {{ $totalRawMaterials }}</p>
            </div>
        </div>

        <!-- Signature Section -->
        <div class="mt-6">
            <div class="flex justify-between items-center">
                <div>
                    <p class="font-semibold text-l">Production Manager</p>
                    <div class="h-24 border-t mt-2"></div>
                    <p class="text-center mt-2">Signature</p>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-l">Created By</p>
                    <p class="text-gray-700">{{ $production->createdBy->name }}</p>
                </div>
            </div>
        </div>

        <!-- Footer Section -->
        <div class="no-print mt-6 text-center">
            <button onclick="window.print()" class="bg-blue-500 text-white py-2 px-2 rounded-md">Print</button>
            <a href="{{ route('productions.index') }}" class="bg-gray-500 text-white py-2 px-2 rounded-md ml-2">Back to List</a>
        </div>
    </div>

</body>
</html>
