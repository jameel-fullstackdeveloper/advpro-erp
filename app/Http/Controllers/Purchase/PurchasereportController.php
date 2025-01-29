<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseItem;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\CustomerDetail;
use App\Models\Company;
use App\Models\ChartOfAccount; // Import ChartOfAccount for customers
use App\Models\ChartOfAccountGroup; // Import ChartOfAccount for customers
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class PurchasereportController extends Controller
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

        abort_if(!auth()->user()->can('purchases bills create'), 403);

        $companyId = session('company_id');

        // Get today's date in Y-m-d format
        $todayDate = now()->format('Y-m-d');

        // Retrieve customers
        $customersGroups = ChartOfAccountGroup::where('is_customer_vendor', 'vendor')
            ->where('company_id', $companyId)
            ->get();


        return view('purchase.reports.index', compact('todayDate', 'customersGroups'));
    }

    public function creditorreport(Request $request)
    {
         // Validate the input date
            $validated = $request->validate([
                'date' => 'required|date', // Ensures valid date format
            ]);



        $selectedDate = Carbon::parse($request->date)->format('Y-m-d'); // Convert to Y-m-d format

        $companyId = session('company_id');

        // Retrieve customers
        $customers = ChartOfAccount::where('is_customer_vendor', 'vendor')
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


            $invoiceAmount = PurchaseBillItem::join('purchase_bills', 'purchase_bill_items.purchase_bill_id', '=', 'purchase_bills.id')
            ->where('purchase_bills.vendor_id', $customer->id)
            ->where('purchase_bills.company_id', $companyId)
            ->where('purchase_bills.bill_date', '<=', $selectedDate) // Ensure the invoice date is <= selected date
            ->sum('purchase_bill_items.net_amount'); // Directly sum the amount_incl_tax


            // Sum up the total return amounts (from sales_return_items) for selectedDate
            $returnAmount = PurchaseReturnItem::whereHas('purchaseReturn', function ($query) use ($customer, $companyId, $selectedDate) {
                $query->where('vendor_id', $customer->id)
                    ->where('company_id', $companyId)
                    ->where('pruchase_returns.return_date', '<=', $selectedDate); // Ensure the return date is <= selected date
            })->sum('purchase_return_items.return_amount');



            // Sum up the total payment amounts (from voucher details) for the selectedDate
            $paymentAmount = VoucherDetail::whereHas('voucher', function ($query) use ($companyId, $selectedDate) {
                $query->where('company_id', $companyId)
                    ->where('vouchers.date', '<=', $selectedDate); // Ensure the voucher date is <= selected date
                })->where('account_id', $customer->id)
                ->where('type', 'debit')
            ->sum('voucher_details.amount');



            // Debit Entry for Customer if any mostly it will be transfer from journal
            $paymentAmountDebit = VoucherDetail::whereHas('voucher', function ($query) use ($companyId, $selectedDate) {
                $query->where('company_id', $companyId)
                    ->where('vouchers.date', '<=', $selectedDate)
                    ->where('vouchers.voucher_type', 'journal');
                })->where('account_id', $customer->id)
                ->where('type', 'credit')

            ->sum('voucher_details.amount');

            // Final invoice amount after applying payments
            $finalInvoiceAmount =  ($ledgerBalance + $invoiceAmount + $paymentAmountDebit) - ($returnAmount + $paymentAmount);

            // Store the calculated values
            // Add ledger balance (opening balance) to the final invoice amount
            $customer->ledgerBalance = $finalInvoiceAmount;


        // Calculate the due amount (same logic as in getCustomersDueAmounts)
        $totalDueAmount = 0;

        // Fetch all invoices for this customer within the selected date
        $invoices = PurchaseBill::with(['items'])
            ->where('company_id', $companyId)
            ->where('vendor_id', $customer->id)
            ->get();

        foreach ($invoices as $invoice) {
            $invoiceAmount = $invoice->items->sum('net_amount');
            $dueDate = Carbon::parse($invoice->bill_date)->addDays($invoice->bill_due_days);

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
                    ->where('type', '=', 'debit')
                    ->sum('amount');

                // Subtract payments from due amount
                $totalDueAmount -= $payments;

                // Ensure the due amount is not negative
                $totalDueAmount = max($totalDueAmount, 0);
            }

            // Add the calculated due amount to the customer
            $customer->dueAmount = $totalDueAmount;

            $invoiceAmountSale = PurchaseBillItem::join('purchase_bills', 'purchase_bill_items.purchase_bill_id', '=', 'purchase_bills.id')
            ->where('purchase_bills.vendor_id', $customer->id)
            ->where('purchase_bills.company_id', $companyId)
            ->where('purchase_bills.bill_date', '<=', $selectedDate) // Ensure the invoice date is <= selected date
            ->whereBetween('purchase_bills.bill_date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
            ->sum('purchase_bill_items.net_amount'); // Directly sum the amount_incl_tax*/



            // Sum up the total payment amounts (from voucher details) for the selected date
            $paymentAmountSale = VoucherDetail::whereHas('voucher', function ($query) use ($companyId, $startOfCurrentMonth, $endOfCurrentMonth) {
                $query->where('company_id', $companyId)
                    ->whereBetween('vouchers.date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')]);
            })
            ->where('account_id', $customer->id)
            ->where('type', 'debit')
            ->sum('voucher_details.amount');

            $customer->invoiceAmount = $invoiceAmountSale;
            $customer->paymentAmount = $paymentAmountSale;
    }


        return view('purchase.reports.creditor', compact('customers', 'selectedDate'));
    }


    public function creditorgroupreport(Request $request)
    {

         // Validate the input date
         $validated = $request->validate([
            'date' => 'required|date', // Ensures valid date format
        ]);

    $selectedDate = Carbon::parse($request->date)->format('Y-m-d'); // Convert to Y-m-d format

    $companyId = session('company_id');

    // Retrieve customers
    $customers = ChartOfAccount::where('is_customer_vendor', 'vendor')
        ->where('group_id', $request->groupname )
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


        $invoiceAmount = PurchaseBillItem::join('purchase_bills', 'purchase_bill_items.purchase_bill_id', '=', 'purchase_bills.id')
        ->where('purchase_bills.vendor_id', $customer->id)
        ->where('purchase_bills.company_id', $companyId)
        ->where('purchase_bills.bill_date', '<=', $selectedDate) // Ensure the invoice date is <= selected date
        ->sum('purchase_bill_items.net_amount'); // Directly sum the amount_incl_tax


        // Sum up the total return amounts (from sales_return_items) for selectedDate
        $returnAmount = PurchaseReturnItem::whereHas('purchaseReturn', function ($query) use ($customer, $companyId, $selectedDate) {
            $query->where('vendor_id', $customer->id)
                ->where('company_id', $companyId)
                ->where('pruchase_returns.return_date', '<=', $selectedDate); // Ensure the return date is <= selected date
        })->sum('purchase_return_items.return_amount');



        // Sum up the total payment amounts (from voucher details) for the selectedDate
        $paymentAmount = VoucherDetail::whereHas('voucher', function ($query) use ($companyId, $selectedDate) {
            $query->where('company_id', $companyId)
                ->where('vouchers.date', '<=', $selectedDate); // Ensure the voucher date is <= selected date
            })->where('account_id', $customer->id)
            ->where('type', 'debit')
        ->sum('voucher_details.amount');



        // Debit Entry for Customer if any mostly it will be transfer from journal
        $paymentAmountDebit = VoucherDetail::whereHas('voucher', function ($query) use ($companyId, $selectedDate) {
            $query->where('company_id', $companyId)
                ->where('vouchers.date', '<=', $selectedDate)
                ->where('vouchers.voucher_type', 'journal');
            })->where('account_id', $customer->id)
            ->where('type', 'credit')

        ->sum('voucher_details.amount');

        // Final invoice amount after applying payments
        $finalInvoiceAmount =  ($ledgerBalance + $invoiceAmount + $paymentAmountDebit) - ($returnAmount + $paymentAmount);

        // Store the calculated values
        // Add ledger balance (opening balance) to the final invoice amount
        $customer->ledgerBalance = $finalInvoiceAmount;


    // Calculate the due amount (same logic as in getCustomersDueAmounts)
    $totalDueAmount = 0;

    // Fetch all invoices for this customer within the selected date
    $invoices = PurchaseBill::with(['items'])
        ->where('company_id', $companyId)
        ->where('vendor_id', $customer->id)
        ->get();

    foreach ($invoices as $invoice) {
        $invoiceAmount = $invoice->items->sum('net_amount');
        $dueDate = Carbon::parse($invoice->bill_date)->addDays($invoice->bill_due_days);

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
                ->where('type', '=', 'debit')
                ->sum('amount');

            // Subtract payments from due amount
            $totalDueAmount -= $payments;

            // Ensure the due amount is not negative
            $totalDueAmount = max($totalDueAmount, 0);
        }

        // Add the calculated due amount to the customer
        $customer->dueAmount = $totalDueAmount;

        $invoiceAmountSale = PurchaseBillItem::join('purchase_bills', 'purchase_bill_items.purchase_bill_id', '=', 'purchase_bills.id')
        ->where('purchase_bills.vendor_id', $customer->id)
        ->where('purchase_bills.company_id', $companyId)
        ->where('purchase_bills.bill_date', '<=', $selectedDate) // Ensure the invoice date is <= selected date
        ->whereBetween('purchase_bills.bill_date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
        ->sum('purchase_bill_items.net_amount'); // Directly sum the amount_incl_tax*/



        // Sum up the total payment amounts (from voucher details) for the selected date
        $paymentAmountSale = VoucherDetail::whereHas('voucher', function ($query) use ($companyId, $startOfCurrentMonth, $endOfCurrentMonth) {
            $query->where('company_id', $companyId)
                ->whereBetween('vouchers.date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')]);
        })
        ->where('account_id', $customer->id)
        ->where('type', 'debit')
        ->sum('voucher_details.amount');

        $customer->invoiceAmount = $invoiceAmountSale;
        $customer->paymentAmount = $paymentAmountSale;
    }


    return view('purchase.reports.creditor', compact('customers', 'selectedDate'));
    }

}
