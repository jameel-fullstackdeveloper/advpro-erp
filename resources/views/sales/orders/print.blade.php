<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Challan | QuickERP</title>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}">
    <style>
        body {
            font-family: 'Roboto', 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            height: 100%;
        }
        .table-condensed {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .table-condensed th, .table-condensed td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
        }
        .container-copy {
            width: 32%;
            display: inline-block;
            margin-right: 1%;
            height: 100vh; /* Each copy will take full page height */
            position: relative;
            box-sizing: border-box;
        }
        .header-logo img {
            height: 100px;
            width: 100px;
            padding-left:30px;
        }
        .title-section {
            text-align: center;
            margin-bottom: 10px;
        }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }

        .signature {
            text-align: left;
        }


        .footer-section {
    position: absolute;
    bottom: 0;
    width: 100%;
    padding-bottom: 10px;
    text-align: right;
}

.remarks-img {
    height: 50px;
    width: auto;
}


@media print {
    .header-logo img {
        width: 100px !important;  /* Increase the width for the print version */
        height: auto !important;  /* Ensure the height is adjusted automatically */
        max-width: none !important; /* Ensure there's no max-width restriction */
        padding-left:30px !important;
    }

    .container-copy {
        width: 100%;  /* Ensure the layout uses full width for printing */
        margin-right: 0;
    }

    .footer-section {
        position: absolute;  /* Ensures the footer stays at the bottom of each page */
        bottom: 10px;  /* Add some space from the bottom */
        width: 100%;
        text-align: right;  /* Align the remarks image to the right */
    }
}


    </style>
</head>
<body>

@php
use App\Models\Company;
        $company = Company::find(session('company_id'));

@endphp

<div class="container-fluid">
    <!-- Horizontal Layout for 3 Copies -->
    <div style="display: flex; width: 100%; height: 100%;">
        @foreach (['Customer Copy', 'Customer Receiver Copy', 'Gate Pas'] as $copyType)
        <div class="container-copy" style="margin-right:10px;">
            <div class="row" style="display: flex; align-items: center; justify-content: center;">
                <div class="col-xs-2 text-center" style="display: flex; align-items: center; justify-content: center;">
                    <div class="header-logo">
                    <img
                        src="{{ $company->avatar ? Storage::disk('spaces')->url($company->avatar) : asset('images/user-dummy-img.jpg') }}"
                        alt="Company Logo"  style="">
                        <!--<img src="/images/logo.png" alt="Company Logo">-->
                    </div>
                </div>
                <div class="col-xs-10 text-center">
                    <h4><strong>{{ $company->name }}</strong></h4>
                    <p>{{ $company->address }}<br/>
                    <span style="margin-top:8px;">Phone # {{ $company->phone }}</span></p>
                </div>
            </div>

            <hr style="margin:0px;">

            <div class="row" style="margin-top:15px;margin-bottom:15px">
                <div class="col-xs-6 text-left">
                    <p style="text-transform: uppercase;font-size:14px;"><strong>Delivery Challan</strong></p>
                </div>
                <div class="col-xs-6 text-end" style="text-align:right">
                    <strong>{{ $copyType }}</strong>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-6">
                    <p><strong>DC Number:</strong> {{ $salesOrder->order_number }}</p>
                    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($salesOrder->order_date)->format('d-m-Y') }}</p>
                </div>
                <div class="col-xs-6 text-right">
                    <p><strong>Supervisor Mobile:</strong> {{ $salesOrder->farm_supervisor_mobile ?? 'N/A' }}</p>
                    <p><strong>Fare:</strong> {{ is_numeric($salesOrder->vehicle_fare) ? number_format($salesOrder->vehicle_fare) : 'N/A' }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-6">
                    <p><strong>Farm:</strong> {{ $salesOrder->farm_name ?? 'N/A' }}</p>
                    <p><strong>Farm Address:</strong> {{ $salesOrder->farm_address ?? 'N/A' }}</p>
                </div>
                <div class="col-xs-6 text-right">
                    <p><strong>Vehicle:</strong> {{ $salesOrder->vehicle_no ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-condensed table-bordered">
                    <thead>
                        <tr>
                            <th>Sr. No</th>
                            <th>Items</th>
                            <th class="text-center">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orderItems as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->product_name ?? 'N/A' }}</td>
                            <td class="text-center">{{ $item->quantity }} Bags</td>
                        </tr>
                        @endforeach
                        <tr>
                            <td colspan="2" class="text-right"><strong>Total:</strong></td>
                            <td class="text-center"><strong>{{ $orderItems->sum('quantity') }} Bags</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Section at the Bottom of Each Copy -->
            <div class="footer-section">
                <div class="row">
                    <div class="col-xs-6 signature">
                        <p>Deliver's Signature:</p>
                    </div>
                    <div class="col-xs-6 text-right">
                        <img src="/images/remarks.png" alt="Remarks" class="remarks-img">
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
   window.onload = function() {
    window.print();
   }
</script>

</body>
</html>
