<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ledger PDF</title>
    <style>
        /* Inline Tailwind-inspired styles for PDF rendering */
        body {
            font-family: 'Open Sans', sans-serif;
            font-size: 12px;
            color: #2d3748;
            background-color: #f7fafc;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header img {
            height: 45px;
        }

        .header .company-details {
            text-align: right;
        }

        .header .company-details h1 {
            font-size: 18px;
            margin: 0;
            color: #1a202c;
        }

        .header .company-details p {
            margin: 0;
            font-size: 12px;
            color: #718096;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .table th, .table td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            text-align: right;
        }

        .table th {
            background-color: #edf2f7;
            text-align: left;
            font-weight: 600;
            color: #2d3748;
        }

        .table td {
            color: #4a5568;
            font-size: 11px;
        }

        .table tfoot td {
            font-weight: 600;
            background-color: #f7fafc;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        h2 {
            font-size: 14px;
            margin-bottom: 8px;
            color: #2d3748;
        }

        h4 {
            font-size: 14px;
            margin-bottom: 15px;
            color: #2d3748;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('path/to/your/logo.png') }}" alt="Company Logo">
            <div class="company-details">
                <h1>Quick ERP</h1>
                <p>1234 Street Address<br>City, State, Zip Code<br>Email: info@company.com<br>Phone: (123) 456-7890</p>
            </div>
        </div>

        <h4 class="text-center">Ledger Statement</h4>

        <table class="table">
            <tr>
                <td class="text-left"><h2>A/c Title: {{ $accountTitle }}</h2></td>
                <td class="text-right"><p>From {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}</p></td>
            </tr>
        </table>

        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                    <th class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalDebit = 0;
                    $totalCredit = 0;
                @endphp

                @foreach($ledgerEntries as $entry)
                    @php
                        if ($entry->type == 'debit') {
                            $totalDebit += $entry->amount;
                        } elseif ($entry->type == 'credit') {
                            $totalCredit += $entry->amount;
                        }
                    @endphp
                    <tr>
                        <td class="text-left">{{ \Carbon\Carbon::parse($entry->voucher_date)->format('d-m-Y') }}</td>
                        <td class="text-left">{{ $entry->voucher_description }}</td>
                        <td>{{ $entry->type == 'debit' ? number_format($entry->amount) : '-' }}</td>
                        <td>{{ $entry->type == 'credit' ? number_format($entry->amount) : '-' }}</td>
                        <td>{{ number_format($entry->balance) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-center">Total</td>
                    <td>{{ number_format($totalDebit) }}</td>
                    <td>{{ number_format($totalCredit) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>
