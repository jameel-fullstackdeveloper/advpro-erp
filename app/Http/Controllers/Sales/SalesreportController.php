<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesProduct;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\CustomerDetail;
use App\Models\Company;
use App\Models\ChartOfAccount; // Import ChartOfAccount for customers
use App\Models\ChartOfAccountGroup; // Import ChartOfAccount for customers
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Items;
use Carbon\Carbon;


class SalesreportController extends Controller
{

 /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {

        abort_if(!auth()->user()->can('sales invocies create'), 403);

        $companyId = session('company_id');

        // Get today's date in Y-m-d format
        $todayDate = now()->format('Y-m-d');

        // Retrieve customers
        $customersGroups = ChartOfAccountGroup::where('is_customer_vendor', 'customer')
            ->where('company_id', $companyId)
            ->get();


        // Get the current month
        $firstDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $lastDate = Carbon::now()->endOfMonth()->format('Y-m-d');


        return view('sales.reports.index', compact('todayDate', 'customersGroups','firstDate','lastDate'));
    }

    public function debtorreport(Request $request)
    {



         // Validate the input date
            $validated = $request->validate([
                'date' => 'required|date', // Ensures valid date format
            ]);



        $selectedDate = Carbon::parse($request->date)->format('Y-m-d'); // Convert to Y-m-d format

        $companyId = session('company_id');

        // Retrieve customers
        $customers = ChartOfAccount::where('is_customer_vendor', 'customer')
            ->where('company_id', $companyId)
            ->get();


        // Start and end dates for the current month
        $startOfCurrentMonth = Carbon::parse($selectedDate)->startOfMonth();
        $endOfCurrentMonth = Carbon::parse($selectedDate)->endOfMonth();

        // Start and end dates for the last month
        $startOfLastMonth = $startOfCurrentMonth->copy()->subMonth()->startOfMonth();  // First day of last month
        $endOfLastMonth = $startOfLastMonth->copy()->endOfMonth();  // Last day of last month (e.g., 2024-10-31 if $startOfCurrentMonth is 2024-11-01)


        // Loop through each customer and calculate the ledger balance and due amount
        foreach ($customers as $customer) {
            // Calculate Ledger Balance (from ChartOfAccount's balance field)
            $ledgerBalance = $customer->balance;

            // Initialize the invoice amount (total due from invoices and returns)
            $invoiceAmount = 0;
            $returnAmount = 0;
            $paymentAmount =0;
            $paymentAmountDebit = 0;


            $invoiceAmount = SalesInvoiceItem::join('sales_invoices', 'sales_invoice_items.sales_invoice_id', '=', 'sales_invoices.id')
            ->where('sales_invoices.customer_id', $customer->id)
            ->where('sales_invoices.company_id', $companyId)
            ->where('sales_invoices.invoice_date', '<=', $selectedDate) // Ensure the invoice date is <= selected date
            ->sum('sales_invoice_items.amount_incl_tax'); // Directly sum the amount_incl_tax



            // Sum up the total return amounts (from sales_return_items) for selectedDate
            $returnAmount = SalesReturnItem::whereHas('salesReturn', function ($query) use ($customer, $companyId, $selectedDate) {
                $query->where('customer_id', $customer->id)
                    ->where('company_id', $companyId)
                    ->where('sales_returns.return_date', '<=', $selectedDate); // Ensure the return date is <= selected date
            })->sum('sales_return_items.return_amount');



            // Sum up the total payment amounts (from voucher details) for the selectedDate
            $paymentAmount = VoucherDetail::whereHas('voucher', function ($query) use ($companyId, $selectedDate) {
                $query->where('company_id', $companyId)
                    ->where('vouchers.date', '<=', $selectedDate); // Ensure the voucher date is <= selected date
                })->where('account_id', $customer->id)
                ->where('type', 'credit')
            ->sum('voucher_details.amount');



            // Debit Entry for Customer if any mostly it will be transfer from journal
            $paymentAmountDebit = VoucherDetail::whereHas('voucher', function ($query) use ($companyId, $selectedDate) {
                $query->where('company_id', $companyId)
                    ->where('vouchers.date', '<=', $selectedDate)
                    ->where('vouchers.voucher_type', 'journal');
                })->where('account_id', $customer->id)
                ->where('type', 'debit')

            ->sum('voucher_details.amount');




            // Final invoice amount after applying payments
            $finalInvoiceAmount =  ($ledgerBalance + $invoiceAmount + $paymentAmountDebit) - ($returnAmount + $paymentAmount);

            // Store the calculated values
            // Add ledger balance (opening balance) to the final invoice amount
            $customer->ledgerBalance = $finalInvoiceAmount;

            // Calculate the number of bags for the current month
            $customer->currentmonthbags = SalesInvoiceItem::join('sales_invoices', 'sales_invoice_items.sales_invoice_id', '=', 'sales_invoices.id')
            ->where('sales_invoices.customer_id', $customer->id)
            ->where('sales_invoices.company_id', $companyId)
            ->whereBetween('sales_invoices.invoice_date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
            ->sum('sales_invoice_items.quantity')
            - SalesReturnItem::join('sales_returns', 'sales_return_items.sales_return_id', '=', 'sales_returns.id')
            ->where('sales_returns.customer_id', $customer->id)
            ->where('sales_returns.company_id', $companyId)
            ->whereBetween('sales_returns.return_date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
            ->sum('sales_return_items.return_quantity');

            // Calculate the number of bags for last month
            $customer->lastmonthbags = SalesInvoiceItem::join('sales_invoices', 'sales_invoice_items.sales_invoice_id', '=', 'sales_invoices.id')
            ->where('sales_invoices.customer_id', $customer->id)
            ->where('sales_invoices.company_id', $companyId)
            ->whereBetween('sales_invoices.invoice_date', [$startOfLastMonth->format('Y-m-d'), $endOfLastMonth->format('Y-m-d')])
            ->sum('sales_invoice_items.quantity')
            - SalesReturnItem::join('sales_returns', 'sales_return_items.sales_return_id', '=', 'sales_returns.id')
            ->where('sales_returns.customer_id', $customer->id)
            ->where('sales_returns.company_id', $companyId)
            ->whereBetween('sales_returns.return_date', [$startOfLastMonth->format('Y-m-d'), $endOfLastMonth->format('Y-m-d')])
            ->sum('sales_return_items.return_quantity');


            //for debuging
           /* if($customer->id == 126){
                var_dump($startOfLastMonth->format('Y-m-d'));
                var_dump($endOfLastMonth->format('Y-m-d'));
                var_dump($customer->currentmonthbags);
                var_dump($customer->lastmonthbags);
            }*/


        // Calculate the due amount (same logic as in getCustomersDueAmounts)
        $totalDueAmount = 0;

        // Fetch all invoices for this customer within the selected date
        $invoices = SalesInvoice::with(['items'])
            ->where('company_id', $companyId)
            ->where('customer_id', $customer->id)
            ->get();

        foreach ($invoices as $invoice) {
            $invoiceAmount = $invoice->items->sum('amount_incl_tax');
            $dueDate = Carbon::parse($invoice->invoice_date)->addDays($invoice->invoice_due_days);

            // Only accumulate due amount for invoices that are past due or due today and <= selected date
            if (($dueDate->isPast() || $dueDate->isToday()) && $dueDate->lte($selectedDate)) {
                $totalDueAmount += $invoiceAmount;
            }
        }

            // Get payments made for this customer, considering payments until the selected date
            if ($totalDueAmount > 0) {
                $payments = VoucherDetail::where('account_id', $customer->id)
                    ->whereHas('voucher', function ($query) use ($selectedDate) {
                        $query->where('vouchers.date', '<=', $selectedDate);
                    })
                    ->where('type', '=', 'credit')
                    ->sum('amount');

                // Subtract payments from due amount
                $totalDueAmount -= $payments;

                // Ensure the due amount is not negative
                $totalDueAmount = max($totalDueAmount, 0);
            }

            // Add the calculated due amount to the customer
            $customer->dueAmount = $totalDueAmount;

            $invoiceAmountSale = SalesInvoiceItem::join('sales_invoices', 'sales_invoice_items.sales_invoice_id', '=', 'sales_invoices.id')
            ->where('sales_invoices.customer_id', $customer->id)
            ->where('sales_invoices.company_id', $companyId)
            ->where('sales_invoices.invoice_date', '<=', $selectedDate) // Ensure the invoice date is <= selected date
            ->whereBetween('sales_invoices.invoice_date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
            ->sum('sales_invoice_items.amount_incl_tax'); // Directly sum the amount_incl_tax

            // Sum up the total payment amounts (from voucher details) for the selected date
            $paymentAmountSale = VoucherDetail::whereHas('voucher', function ($query) use ($companyId, $startOfCurrentMonth, $endOfCurrentMonth) {
                $query->where('company_id', $companyId)
                    ->whereBetween('vouchers.date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')]);
            })
            ->where('account_id', $customer->id)
            ->where('type', 'credit')
            ->sum('voucher_details.amount');

            $customer->invoiceAmount = $invoiceAmountSale;
            $customer->paymentAmount = $paymentAmountSale;
    }


        return view('sales.reports.debtor', compact('customers', 'selectedDate'));
    }


    public function debtorgroupreport(Request $request)
    {

         // Validate the input date
            $validated = $request->validate([
                'date' => 'required|date', // Ensures valid date format
            ]);



        $selectedDate = Carbon::parse($request->date)->format('Y-m-d'); // Convert to Y-m-d format

        $companyId = session('company_id');

        // Retrieve customers
        $customers = ChartOfAccount::where('is_customer_vendor', 'customer')
            ->where('group_id', $request->groupname)
            ->where('company_id', $companyId)
            ->get();


        // Start and end dates for the current month
        $startOfCurrentMonth = Carbon::parse($selectedDate)->startOfMonth();
        $endOfCurrentMonth = Carbon::parse($selectedDate)->endOfMonth();

        // Start and end dates for the last month
        $startOfLastMonth = $startOfCurrentMonth->copy()->subMonth()->startOfMonth();  // First day of last month
        $endOfLastMonth = $startOfLastMonth->copy()->endOfMonth();  // Last day of last month (e.g., 2024-10-31 if $startOfCurrentMonth is 2024-11-01)


        // Loop through each customer and calculate the ledger balance and due amount
        foreach ($customers as $customer) {
            // Calculate Ledger Balance (from ChartOfAccount's balance field)
            $ledgerBalance = $customer->balance;

            // Initialize the invoice amount (total due from invoices and returns)
            $invoiceAmount = 0;
            $returnAmount = 0;
            $paymentAmount =0;
            $paymentAmountDebit = 0;


            $invoiceAmount = SalesInvoiceItem::join('sales_invoices', 'sales_invoice_items.sales_invoice_id', '=', 'sales_invoices.id')
            ->where('sales_invoices.customer_id', $customer->id)
            ->where('sales_invoices.company_id', $companyId)
            ->where('sales_invoices.invoice_date', '<=', $selectedDate) // Ensure the invoice date is <= selected date
            ->sum('sales_invoice_items.amount_incl_tax'); // Directly sum the amount_incl_tax



            // Sum up the total return amounts (from sales_return_items) for selectedDate
            $returnAmount = SalesReturnItem::whereHas('salesReturn', function ($query) use ($customer, $companyId, $selectedDate) {
                $query->where('customer_id', $customer->id)
                    ->where('company_id', $companyId)
                    ->where('sales_returns.return_date', '<=', $selectedDate); // Ensure the return date is <= selected date
            })->sum('sales_return_items.return_amount');



            // Sum up the total payment amounts (from voucher details) for the selectedDate
            $paymentAmount = VoucherDetail::whereHas('voucher', function ($query) use ($companyId, $selectedDate) {
                $query->where('company_id', $companyId)
                    ->where('vouchers.date', '<=', $selectedDate); // Ensure the voucher date is <= selected date
                })->where('account_id', $customer->id)
                ->where('type', 'credit')
            ->sum('voucher_details.amount');



            // Debit Entry for Customer if any mostly it will be transfer from journal
            $paymentAmountDebit = VoucherDetail::whereHas('voucher', function ($query) use ($companyId, $selectedDate) {
                $query->where('company_id', $companyId)
                    ->where('vouchers.date', '<=', $selectedDate)
                    ->where('vouchers.voucher_type', 'journal');
                })->where('account_id', $customer->id)
                ->where('type', 'debit')

            ->sum('voucher_details.amount');




            // Final invoice amount after applying payments
            $finalInvoiceAmount =  ($ledgerBalance + $invoiceAmount + $paymentAmountDebit) - ($returnAmount + $paymentAmount);

            // Store the calculated values
            // Add ledger balance (opening balance) to the final invoice amount
            $customer->ledgerBalance = $finalInvoiceAmount;

            // Calculate the number of bags for the current month
            $customer->currentmonthbags = SalesInvoiceItem::join('sales_invoices', 'sales_invoice_items.sales_invoice_id', '=', 'sales_invoices.id')
            ->where('sales_invoices.customer_id', $customer->id)
            ->where('sales_invoices.company_id', $companyId)
            ->whereBetween('sales_invoices.invoice_date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
            ->sum('sales_invoice_items.quantity')
            - SalesReturnItem::join('sales_returns', 'sales_return_items.sales_return_id', '=', 'sales_returns.id')
            ->where('sales_returns.customer_id', $customer->id)
            ->where('sales_returns.company_id', $companyId)
            ->whereBetween('sales_returns.return_date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
            ->sum('sales_return_items.return_quantity');

            // Calculate the number of bags for last month
            $customer->lastmonthbags = SalesInvoiceItem::join('sales_invoices', 'sales_invoice_items.sales_invoice_id', '=', 'sales_invoices.id')
            ->where('sales_invoices.customer_id', $customer->id)
            ->where('sales_invoices.company_id', $companyId)
            ->whereBetween('sales_invoices.invoice_date', [$startOfLastMonth->format('Y-m-d'), $endOfLastMonth->format('Y-m-d')])
            ->sum('sales_invoice_items.quantity')
            - SalesReturnItem::join('sales_returns', 'sales_return_items.sales_return_id', '=', 'sales_returns.id')
            ->where('sales_returns.customer_id', $customer->id)
            ->where('sales_returns.company_id', $companyId)
            ->whereBetween('sales_returns.return_date', [$startOfLastMonth->format('Y-m-d'), $endOfLastMonth->format('Y-m-d')])
            ->sum('sales_return_items.return_quantity');


            //for debuging
           /* if($customer->id == 126){
                var_dump($startOfLastMonth->format('Y-m-d'));
                var_dump($endOfLastMonth->format('Y-m-d'));
                var_dump($customer->currentmonthbags);
                var_dump($customer->lastmonthbags);
            }*/


        // Calculate the due amount (same logic as in getCustomersDueAmounts)
        $totalDueAmount = 0;

        // Fetch all invoices for this customer within the selected date
        $invoices = SalesInvoice::with(['items'])
            ->where('company_id', $companyId)
            ->where('customer_id', $customer->id)
            ->get();

        foreach ($invoices as $invoice) {
            $invoiceAmount = $invoice->items->sum('amount_incl_tax');
            $dueDate = Carbon::parse($invoice->invoice_date)->addDays($invoice->invoice_due_days);

            // Only accumulate due amount for invoices that are past due or due today and <= selected date
            if (($dueDate->isPast() || $dueDate->isToday()) && $dueDate->lte($selectedDate)) {
                $totalDueAmount += $invoiceAmount;
            }
        }

            // Get payments made for this customer, considering payments until the selected date
            if ($totalDueAmount > 0) {
                $payments = VoucherDetail::where('account_id', $customer->id)
                    ->whereHas('voucher', function ($query) use ($selectedDate) {
                        $query->where('vouchers.date', '<=', $selectedDate);
                    })
                    ->where('type', '=', 'credit')
                    ->sum('amount');

                // Subtract payments from due amount
                $totalDueAmount -= $payments;

                // Ensure the due amount is not negative
                $totalDueAmount = max($totalDueAmount, 0);
            }

            // Add the calculated due amount to the customer
            $customer->dueAmount = $totalDueAmount;

            $invoiceAmountSale = SalesInvoiceItem::join('sales_invoices', 'sales_invoice_items.sales_invoice_id', '=', 'sales_invoices.id')
            ->where('sales_invoices.customer_id', $customer->id)
            ->where('sales_invoices.company_id', $companyId)
            ->where('sales_invoices.invoice_date', '<=', $selectedDate) // Ensure the invoice date is <= selected date
            ->whereBetween('sales_invoices.invoice_date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
            ->sum('sales_invoice_items.amount_incl_tax'); // Directly sum the amount_incl_tax

            // Sum up the total payment amounts (from voucher details) for the selected date
            $paymentAmountSale = VoucherDetail::whereHas('voucher', function ($query) use ($companyId, $startOfCurrentMonth, $endOfCurrentMonth) {
                $query->where('company_id', $companyId)
                    ->whereBetween('vouchers.date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')]);
            })
            ->where('account_id', $customer->id)
            ->where('type', 'credit')
            ->sum('voucher_details.amount');

            $customer->invoiceAmount = $invoiceAmountSale;
            $customer->paymentAmount = $paymentAmountSale;
    }


        return view('sales.reports.debtor', compact('customers', 'selectedDate'));
    }


    public function sale_register_report(Request $request)
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session

        // Get the date range from the request
        $firstDate = $request->firstdate;
        $lastDate = $request->lastdate;

        // Fetch invoices within the date range for the given company
        $invoices = SalesInvoice::with(['salesOrder', 'items'])
        ->where('company_id', $companyId)
        ->whereBetween('invoice_date', [$firstDate, $lastDate])
        ->orderBy('sales_invoices.invoice_date')
        ->get();


        // Fetch all products related to the company
        $products = Items::where('company_id', $companyId)->get()->keyBy('id'); // Key by 'id' for faster lookup


        return view('sales.reports.sale_register_report', [
            'firstDate' => $firstDate,
            'lastDate' => $lastDate,
            'invoices' => $invoices,
            'products' => $products,
        ]);
    }




}
