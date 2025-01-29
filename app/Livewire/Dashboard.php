<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SalesInvoice;
use App\Models\PurchaseBill;
use App\Models\VoucherDetail;

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountGroup;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $filter_date = 'CurrentYear'; // Default value for the dropdown

    // Public properties to store sales data
    public $totalInvoices;
    public $totalBagsSold;
    public $totalSaleDiscount;
    public $totalSaleAmountExclTax;
    public $totalSaleAmount;
    public $totalSaleTaxes;
    public $totalSaleCommission;
    public $totalWHT;
    public $totalSaleFreight;
    public $totalSaleBrokery;

     // Public properties to store purchase data
     public $totalbills;
     public $totalPurchaseAmountExclTax;
     public $totalPurchaseSaleTaxes;
     public $totalPurchaseAmount;
     public $totalPurchaseBrokrage;
     public $totalPurchaseWHT;

     //customers
     public $totalCustomers;
     public $totalCustomersGroup;

      //vendors
      public $totalVendors;
      public $totalVendorsGroup;

       // Customers' Due Amounts
    public $customersDueAmount;
    public $vendorsDueAmount;


    public $cashInHand = 0;
    public $bankAmount = 0;
    public $expenses = 0;


    public function mount()
    {
         $this->filter_date = 'CurrentYear';

        // Set the default sales data based on the current month
        $this->getSalesData();
        $this->getPurchaseData();
        $this->getCustomersData();
        $this->getVendorsData();
        $this->getCustomersDueAmounts();
        $this->getVendorsDueAmounts();
        // Calculate Cash, Bank and Expenses balances
        $this->getFinancialData();

    }

    public function getFinancialData()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session

        // Get Cash in Hand accounts (filtered by the 'Cash' group_id)
        $cashAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('group_id', 1) // Replace with your actual group_id for Cash
            ->get();

        $this->cashInHand = 0;
        foreach ($cashAccounts as $cashAccount) {
            $this->cashInHand += $this->getAccountBalance($cashAccount->id); // Sum balances of all cash accounts
        }

        // Get Bank accounts (filtered by the 'Bank' group_id)
        $bankAccounts = ChartOfAccount::where('company_id', $companyId)
        ->where('group_id', 2) // Replace with your actual group_id for Cash
        ->get();

        $this->bankAmount=0;
        foreach ($bankAccounts as $bankAccount) {
            $this->bankAmount += $this->getAccountBalance($bankAccount->id); // Sum balances of all cash accounts
        }

        $expenseAccounts = ChartOfAccount::whereHas('chartOfAccountGroup.chartOfAccountsType', function ($query) {
            $query->where('name', 'Expenses');
        })->get();

        foreach ($expenseAccounts as $expenseAccount) {
            $this->expenses += $this->getAccountBalance($expenseAccount->id); // Sum balances of all cash accounts
        }


    }


    public function getAccountBalance($accountId)
    {
        // Fetch the opening balance from ChartOfAccount
        $account = ChartOfAccount::find($accountId);

        // Ensure filter_date is parsed correctly
        $dates = $this->getDateRange($this->filter_date);
        $filterDate = $dates['end'];  // Use the 'end' date from the range as the filter date

        $balance = $account ? $account->balance : 0; // Start with the opening balance

        // Add all the voucher transaction amounts for this account (both debit and credit)
        $voucherDetails = VoucherDetail::where('account_id', $accountId)
            ->whereHas('voucher', function($query) use ($filterDate) {
                // Only consider transactions up to the end of the current month
                //$query->where('date', '<=', Carbon::now()->endOfMonth());
                $query->where('date', '<=', $filterDate)->orderBy('date');
            })
            ->get();

        // Loop through each voucher detail to adjust the balance
        foreach ($voucherDetails as $voucherDetail) {
            if ($voucherDetail->type == 'debit') {
                // Debit transactions increase the balance
                $balance += $voucherDetail->amount;
            } elseif ($voucherDetail->type == 'credit') {
                // Credit transactions decrease the balance
                $balance -= $voucherDetail->amount;
            }
        }

        return $balance;
    }


    public function getGroupBalance($groupId)
    {
        // Fetch all accounts under the expense group
        $accounts = ChartOfAccount::where('group_id', $groupId)->get();

        $totalBalance = 0;
        foreach ($accounts as $account) {
            $totalBalance += $this->getAccountBalance($account->id);
        }

        return $totalBalance;
    }

    public function updatedFilterDate()
    {

        $this->getSalesData();
        $this->getPurchaseData();
        $this->getCustomersDueAmounts();
        $this->getVendorsDueAmounts();
        $this->getFinancialData();

    }



    public function getCustomersData()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session


        // Fetch the total number of customers (assuming 'type' or a similar field indicates customer status)
        $this->totalCustomers = ChartOfAccount::where('is_customer_vendor', 'customer')
        ->where('company_id', $companyId)
        ->count();

        $this->totalCustomersGroup = ChartOfAccountGroup::where('is_customer_vendor', 'customer')
        ->where('company_id', $companyId)// Apply search filter
        ->count();  // Apply pagination
    }

    public function getVendorsData()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session


        // Fetch the total number of customers (assuming 'type' or a similar field indicates customer status)
        $this->totalVendors = ChartOfAccount::where('is_customer_vendor', 'vendor')
        ->where('company_id', $companyId)
        ->count();

        $this->totalVendorsGroup = ChartOfAccountGroup::where('is_customer_vendor', 'vendor')
        ->where('company_id', $companyId)// Apply search filter
        ->count();  // Apply pagination
    }

    public function getSalesData()
    {

        $companyId = session('company_id'); // Retrieve the company_id from the session

            // Get start and end date based on filter
        $dates = $this->getDateRange($this->filter_date);

        // Query the sales invoices within the date range and eager load items
        $sales = SalesInvoice::with(['items','salesOrder'])  // Eager load the related items
            ->whereBetween('invoice_date', [$dates['start'], $dates['end']])
            ->where('company_id', $companyId)
            ->get();

        // Log the query result to check if it's fetching records
        \Log::debug('Sales Query Result:', $sales->toArray());

        // Calculate required sales data from the sales and their items
        $this->totalInvoices = $sales->count();

        $this->totalBagsSold = $sales->sum(function ($invoice) {
            return $invoice->items->sum('quantity'); // Sum quantity from related items
        });

        $this->totalSaleDiscount = $sales->sum(function ($invoice) {
            return $invoice->items->sum('discount_amount'); // Sum discount amount from related items
        });

        $this->totalSaleAmount = $sales->sum(function ($invoice) {
            return $invoice->items->sum('amount_incl_tax'); // Sum amount including tax from related items
        });

        $this->totalSaleAmountExclTax = $sales->sum(function ($invoice) {
            return $invoice->items->sum('amount_excl_tax'); // Sum amount including tax from related items
        });

        $this->totalSaleTaxes = $sales->sum(function ($invoice) {
            return $invoice->items->sum('sales_tax_amount'); // Sum sales tax from related items
        });

        $this->totalSaleCommission = $sales->sum(function ($invoice) {
            return $invoice->items->sum('further_sales_tax_amount'); // Sum further sales tax from related items
        });

        $this->totalWHT = $sales->sum(function ($invoice) {
            return $invoice->items->sum('advance_wht_amount'); // Sum WHT from related items
        });


        // Calculate totalSaleBrokery from the sales_invoices table
        $this->totalSaleBrokery = $sales->sum('broker_amount'); // Sum broker amount from sales_invoices table

        // Calculate totalVehicleFare from the sales_orders table
        $this->totalSaleFreight = $sales->sum(function ($invoice) {
            return $invoice->salesOrder->vehicle_fare ?? 0; // Sum vehicle_fare from the related sales_order
        });

    }

    public function getPurchaseData()
    {

        $companyId = session('company_id'); // Retrieve the company_id from the session


            // Get start and end date based on filter
        $dates = $this->getDateRange($this->filter_date);

        // Query the sales invoices within the date range and eager load items
        $purchases = PurchaseBill::with(['items'])  // Eager load the related items
            ->whereBetween('bill_date', [$dates['start'], $dates['end']])
            ->where('company_id', $companyId)
            ->get();


        // Calculate required sales data from the sales and their items
        $this->totalbills = $purchases->count();

        $this->totalPurchaseAmountExclTax = $purchases->sum(function ($bill) {
            return $bill->items->sum('gross_amount'); // Sum amount including tax from related items
        });

        $this->totalPurchaseSaleTaxes = $purchases->sum(function ($bill) {
            return $bill->items->sum('sales_tax_amount'); // Sum amount including tax from related items
        });


        $this->totalPurchaseAmount = $purchases->sum(function ($bill) {
            return $bill->items->sum('net_amount'); // Sum sales tax from related items
        });


         // Calculate totalSaleBrokery from the sales_invoices table
         $this->totalPurchaseBrokrage = $purchases->sum('broker_amount'); // Sum broker amount from sales_invoices table

         // Calculate totalSaleBrokery from the sales_invoices table
         $this->totalPurchaseWHT = $purchases->sum('broker_wht_amount'); // Sum broker amount from sales_invoices table

    }

    public function getDateRange($filter)
    {
        $today = Carbon::today(); // Gets today's date

        switch ($filter) {
            case 'Today':
                // For 'Today', return the start and end of today
                $start = $today->copy()->startOfDay();
                $end = $today->copy()->endOfDay();
                break;
            case 'CurrentMonth':
                $start = $today->copy()->startOfMonth();
                $end = $today->copy()->endOfMonth();
                break;
            case 'CurrentYear':
                $start = $today->copy()->startOfYear();
                $end = $today->copy()->endOfYear();
                break;
            case 'LastMonth':
                $start = $today->copy()->subMonth()->startOfMonth();
                $end = $today->copy()->subMonth()->endOfMonth();
                break;
            case 'LastQuarter':
                $start = $today->copy()->subMonths(3)->startOfMonth(); // Start of last quarter
                $end = $today->copy()->subMonths(3)->endOfMonth();     // End of last quarter
                break;
            case 'LastYear':
                $start = $today->copy()->subYear()->startOfYear(); // Start of last year
                $end = $today->copy()->subYear()->endOfYear();     // End of last year
                break;
            case 'Last30Days':
                $start = $today->copy()->subDays(30); // 30 days ago from today
                $end = $today->copy()->endOfDay();   // End of today
                break;
            case 'Last60Days':
                $start = $today->copy()->subDays(60); // 60 days ago from today
                $end = $today->copy()->endOfDay();   // End of today
                break;
            case 'Last90Days':
                $start = $today->copy()->subDays(90); // 90 days ago from today
                $end = $today->copy()->endOfDay();   // End of today
                break;
            default:
                // Default to Current Month if no filter selected
                $start = $today->copy()->startOfMonth();
                $end = $today->copy()->endOfMonth();
                break;
        }

        return ['start' => $start, 'end' => $end];
    }


    public function getCustomersDueAmounts()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session

        // Fetch all customers
        $customers = ChartOfAccount::where('is_customer_vendor', 'customer')
            ->where('company_id', $companyId)
            ->get();

        // Initialize an array to hold each customer's due amount
        $this->customersDueAmount = [];

        // Ensure filter_date is parsed correctly
        $dates = $this->getDateRange($this->filter_date);
        $filterDate = $dates['end'];  // Use the 'end' date from the range as the filter date

        foreach ($customers as $customer) {
            // Initialize a variable to hold the total due amount for the current customer
            $totalDueAmount = 0;

            // Fetch all invoices for this customer within the filter date range
            $invoices = SalesInvoice::with(['items'])
                ->where('company_id', $companyId)
                ->where('customer_id', $customer->id)  // Filter by customer
                ->get();

            // Loop through each invoice for the current customer
            foreach ($invoices as $invoice) {
                // Calculate the total invoice amount (including tax)
                $invoiceAmount = $invoice->items->sum('amount_incl_tax');

                // Calculate the due date based on the invoice date and the due days
                $dueDate = Carbon::parse($invoice->invoice_date)->addDays($invoice->invoice_due_days);

                // Only accumulate due amount for invoices that are past due or due today and <= filter_date
                if (($dueDate->isPast() || $dueDate->isToday()) && $dueDate->lte($filterDate)) {
                    // Add the due amount for this invoice to the total due amount for the customer
                    $totalDueAmount += $invoiceAmount;
                }
            }

            // Now, check for any payments received for this customer up to the filter_date
            if ($totalDueAmount > 0) {
                // Get payments made for this customer, considering payments until filter_date
                $payments = VoucherDetail::where('account_id', $customer->id)  // Link payments by customer_id
                    ->whereHas('voucher', function ($query) use ($filterDate) {
                        // Order payments by the voucher date column and filter by date
                        $query->where('vouchers.date', '<=', $filterDate)->orderBy('vouchers.date');
                    })
                    ->where('type', '=', 'credit')  // Filter for 'credit' type payments
                    ->sum('amount');  // Get the total payment amount

                // Subtract the total payment from the total due amount for this customer
                $totalDueAmount -= $payments;

                // Ensure the due amount is never negative
                $totalDueAmount = max($totalDueAmount, 0);

                // Store the final due amount for the customer
                $this->customersDueAmount[$customer->name] = $totalDueAmount;
            }
        }
    }

    public function getVendorsDueAmounts()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session

        // Fetch all vendors
        $vendors = ChartOfAccount::where('is_customer_vendor', 'vendor')
            ->where('company_id', $companyId)
            ->get();


        // Initialize an array to hold each vendor's due amount
        $this->vendorsDueAmount = [];

        // Ensure filter_date is parsed correctly
        $dates = $this->getDateRange($this->filter_date);
        $filterDate = $dates['end'];  // Use the 'end' date from the range as the filter date

        foreach ($vendors as $vendor) {
            // Initialize a variable to hold the total due amount for the current vendor
            $totalDueAmount = 0;

            // Fetch all purchase bills for this vendor within the filter date range
            $purchaseBills = PurchaseBill::with(['items'])
            ->where('company_id', $companyId)
            ->where('vendor_id', $vendor->id) // Filter by vendor
            ->get();

        // Loop through each purchase bill for the current vendor
        foreach ($purchaseBills as $purchaseBill) {
            // Calculate the total purchase bill amount (including tax)
            $purchaseBillAmount = $purchaseBill->items->sum('net_amount');

            // Calculate the due date based on the bill date and the bill due days
            $dueDate = Carbon::parse($purchaseBill->bill_date)->addDays($purchaseBill->bill_due_days);

            // Only accumulate due amount for purchase bills that are past due or due today and <= filter_date
            if (($dueDate->isPast() || $dueDate->isToday()) && $dueDate->lte($filterDate)) {
                // Add the due amount for this purchase bill to the total due amount for the vendor
                $totalDueAmount += $purchaseBillAmount;
            }
        }


            // Now, check for any payments made for this vendor up to the filter_date
            if ($totalDueAmount > 0) {
                // Get payments made to this vendor, considering payments until filter_date
                $payments = VoucherDetail::where('account_id', $vendor->id)  // Link payments by vendor_id
                    ->whereHas('voucher', function ($query) use ($filterDate) {
                        // Order payments by the voucher date column and filter by date
                        $query->where('vouchers.date', '<=', $filterDate)->orderBy('vouchers.date');
                    })
                    ->where('type', '=', 'debit')  // Filter for 'debit' type payments
                    ->sum('amount');  // Get the total payment amount

                // Subtract the total payment from the total due amount for this vendor
                $totalDueAmount -= $payments;

                // Ensure the due amount is never negative
                $totalDueAmount = max($totalDueAmount, 0);

                // Store the final due amount for the vendor
                $this->vendorsDueAmount[$vendor->name] = $totalDueAmount;
            }
        }
    }




    public function render()
    {
        // Pass data to the view to render
        return view('livewire.dashboard', [
            'totalInvoices' => $this->totalInvoices,
            'totalBagsSold' => $this->totalBagsSold,
            'totalSaleDiscount' => $this->totalSaleDiscount,
            'totalSaleAmount' => $this->totalSaleAmount,
            'totalSaleAmountExclTax' => $this->totalSaleAmountExclTax,
            'totalSaleTaxes' => $this->totalSaleTaxes,
            'totalSaleCommission' => $this->totalSaleCommission,
            'totalWHT' => $this->totalWHT,
            'customersDueAmount' => $this->customersDueAmount,
            'vendorsDueAmount' => $this->vendorsDueAmount,
            'cashInHand' => $this->cashInHand,
            'bankAmount' => $this->bankAmount,
            'expenses' => $this->expenses,

        ]);
    }
}
