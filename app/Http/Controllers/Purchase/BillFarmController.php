<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\PurchaseFarmBill;
use App\Models\PurchaseFarmBillItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ChartOfAccount;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\CustomerDetail;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Items;
use App\Models\ItemGroup;


class BillFarmController extends Controller
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
        //return view('sales.invoices.create', compact('products', 'customers', 'brokers', 'invoiceNumber'));
    }

    public function create(Request $request) {

        abort_if(!auth()->user()->can('purchases bills create'), 403);

        $companyId = session('company_id'); // Retrieve the company_id from the session

         // Fetch vendors filtered by company_id
         $vendors = ChartOfAccount::where('is_customer_vendor', 'vendor')
         ->orderBy( 'name')
         ->get();

         $brokers = ChartOfAccount::where('is_customer_vendor', 'purchase_broker')
         ->where('company_id', $companyId)
         ->get(); // Fetch brokers


        //Generate invoice number
        $billNumber = $this->generateBillNumber();

        $segments = Company::all();  // Fetch all segments (companies)

        $farmaccounts = ChartOfAccount::where(function($query) {
            $query->where('is_farm', 1);
        })
        ->orderBy('name')
        ->get();

        // Fetch products filtered by company_id
        $items = Items::where('item_type', 'purchase')
        ->where('item_group_id',10)
        ->orderBy('name')
        ->get();


        return view('purchase.billsfarms.create',compact('vendors','billNumber' , 'brokers','segments' , 'farmaccounts' , 'items'));

    }

    private function generateBillNumber()
    {
        $company = Company::where('id', session('company_id'))->first(); // Fetch the company record
        $companyAbbreviation = $company ? $company->abv : 'PB'; // Default to 'PB' if not found

        $currentDate = now();

        // Determine the start of the fiscal year (starting from July)
        $financialYearStart = $currentDate->month >= 7
            ? $currentDate->copy()->month(7)->startOfMonth()
            : $currentDate->copy()->subYear()->month(7)->startOfMonth();

        // Get the last two digits of the current year for use in the bill number
        $currentYear = substr($currentDate->format('Y'), -2); // Last two digits of the year

        // Count the number of bills in the current fiscal year
        $billCount = PurchaseFarmBill::where('bill_date', '>=', $financialYearStart)
            ->where('bill_date', '<', $financialYearStart->copy()->addYear()) // Ensure it's within the fiscal year
            ->count();

        // Start numbering from 1 for the new fiscal year
        $nextBillNumber = $billCount + 1;

        do {
            // Format the next bill number with leading zeros
            $formattedNumber = str_pad($nextBillNumber, 3, '0', STR_PAD_LEFT);
            // Generate the bill number in the format: [CompanyAbbreviation]-[FiscalYearLast2Digits]-[FormattedNumber]
            $billNumber =  'PB-FRM'  . $currentYear . '-' . $formattedNumber;

            // Check if the bill number already exists
            $billExists = PurchaseFarmBill::where('bill_number', $billNumber)->exists();

            if ($billExists) {
                // If the number exists, increment and retry
                $nextBillNumber++;
            }
        } while ($billExists);

        return $billNumber;
    }




    public function store(Request $request)
    {

        try {
        // Start the database transaction
        DB::beginTransaction();

        // Validate the purchase bill inputs
        $validated = $request->validate([
            'vendor_id' => 'required', // Ensure vendor exists
            'bill_date' => 'required|date',
            'bill_due_days' => 'required',
            'vehicle_no' => 'nullable|string',
            'delivery_mode' => 'nullable|string',
            'freight' => 'required|numeric|min:0',
            'broker_id' => 'nullable', // Ensure broker exists
            'broker_rate' => 'nullable|numeric',
            'broker_amount' => 'nullable|numeric',
            'items.*.product_id' => 'required', // Ensure product exists
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.deduction' => 'nullable|numeric|min:0',
            'items.*.net_quantity' => 'nullable|numeric|min:0',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.gross_amount' => 'nullable|numeric|min:0',
            'items.*.sales_tax_rate' => 'nullable|numeric|min:0',
            'items.*.sales_tax_amount' => 'nullable|numeric|min:0',
            'items.*.withholding_tax_rate' => 'nullable|numeric|min:0',
            'items.*.withholding_tax_amount' => 'nullable|numeric|min:0',
            'items.*.net_amount' => 'required|numeric|min:0',
        ]);

        // Prepare the data for the purchase bill
        $billData = [
            'vendor_id' => $request->vendor_id,
            'order_id' => $request->order_id,
            'bill_date' => $request->bill_date,
            'bill_due_days' => $request->bill_due_days,
            'vehicle_no' => $request->vehicle_no,
            'freight' => $request->freight,
            'broker_id' => $request->broker_id,
            'broker_rate' => $request->broker_rate ?? 0,
            'calculation_method' => $request->calculation_method,
            'broker_amount' => $request->broker_amount ?? 0,
            'broker_wht_rate' => $request->broker_wht_rate ?? 0,
            'broker_wht_amount' => $request->broker_wht_amount ?? 0,
            'broker_amount_with_wht' => $request->broker_amount_with_wht ?? 0,
            'delivery_mode' => $request->delivery_mode,
            'status' => 'posted',
            'company_id' => 1,
            'segment_id' => 1,
            'cost_center_id' => 1,
            'financial_year_id' => 1, // You may set this dynamically as needed
            'comments' => $request->comments,
            'farm_account' => $request->farm_account,
        ];

        // Generate new bill number
        $newBillNumber = $this->generateBillNumber();

        // Create new purchase bill
        $billData['created_by'] = Auth::id();
        $billData['bill_number'] = $newBillNumber; // Generate unique bill number
        $purchaseBill = PurchaseFarmBill::create($billData);

        // Create a new voucher for the purchase bill
        $voucher = Voucher::create([
            'voucher_type' => 'purchase-bill-farm',
            'date' => $request->bill_date,
            'reference_number' => $newBillNumber,
            'total_amount' => collect($request->items)->sum('net_amount'),
            'description' => 'Purchase Bill #' . $newBillNumber,
            'status' => 1,
            'company_id' => session('company_id'),
            'segment_id' => 1,
            'cost_center_id' => 1,
            'created_by' => Auth::id(),
        ]);

        // Save purchase bill items
        foreach ($request->items as $item) {
            PurchaseFarmBillItem::create([
                'purchase_farm_bill_id' => $purchaseBill->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'deduction' => $item['deduction'] ?? 0, // Default deduction to 0 if not provided
                'net_quantity' => $item['net_quantity'] ?? $item['quantity'], // Default net_quantity
                'price' => $item['price'],
                'gross_amount' => $item['gross_amount'] ?? 0, // Default gross amount to 0
                'sales_tax_rate' => $item['sales_tax_rate'] ?? 0, // Default sales tax rate to 0
                'sales_tax_amount' => $item['sales_tax_amount'] ?? 0, // Default sales tax amount to 0
                'withholding_tax_rate' => $item['withholding_tax_rate'] ?? 0, // Default withholding tax rate to 0
                'withholding_tax_amount' => $item['withholding_tax_amount'] ?? 0, // Default withholding tax amount to 0
                'net_amount' => $item['net_amount'],

            ]);
        }

        // Ensure voucher exists before creating voucher details
        if ($voucher) {
            // Calculate total net amount and total sales tax
            $totalNetAmount = collect($request->items)->sum('net_amount');
            $totalSalesTax = collect($request->items)->sum(function ($item) {
                return $item['sales_tax_amount'] ?? 0;
            });

            // Create voucher details for purchases and account payable
            VoucherDetail::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->vendor_id, // Vendor's account
                'amount' => $totalNetAmount,
                'type' => 'credit',
                'narration' => 'Purchases for Bill #' . $newBillNumber,
                'created_by' => Auth::id(),
            ]);

            VoucherDetail::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->farm_account, // Debit account for purchases
                'amount' => $totalNetAmount - $totalSalesTax,
                'type' => 'debit',
                'narration' => 'Purchases for Bill #' . $newBillNumber,
                'created_by' => Auth::id(),
            ]);

            // Add sales tax payable entry if applicable
            if ($totalSalesTax > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 11, // Sales Tax Payable account ID
                    'amount' => $totalSalesTax,
                    'type' => 'debit',
                    'narration' => 'Sales Tax Payable for Bill #' . $newBillNumber,
                    'created_by' => Auth::id(),
                ]);
            }

           // Handle broker accounting if broker exists for brokers
            if ($request->broker_id && $request->broker_id !== '0') {
                // For a specific broker
                $brokerAmount = $request->broker_amount;
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $request->broker_id, // Broker's account
                    'amount' => $brokerAmount,
                    'type' => 'credit',
                    'narration' => 'Brokerage for Bill #' . $newBillNumber,
                    'created_by' => Auth()->id(),
                ]);

                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 10, // Purchase Brokers Expense account ID
                    'amount' => $brokerAmount,
                    'type' => 'debit',
                    'narration' => 'Brokerage for Bill #' . $newBillNumber,
                    'created_by' => Auth()->id(),
                ]);

                    if($request->broker_wht_amount > 0) {
                        //if WHT on brokery amount
                        $brokerWHTAmount = $request->broker_wht_amount;

                        VoucherDetail::create([
                            'voucher_id' => $voucher->id,
                            'account_id' => 14 , // WHT Payable
                            'amount' => $brokerWHTAmount,
                            'type' => 'credit',
                            'narration' => 'Brokerage WHT for Bill #' . $newBillNumber,
                            'created_by' => Auth()->id(),
                        ]);

                        VoucherDetail::create([
                            'voucher_id' => $voucher->id,
                            'account_id' => $request->broker_id,
                            'amount' => $brokerWHTAmount,
                            'type' => 'debit',
                            'narration' => 'Brokerage WHT for Bill #' . $newBillNumber,
                            'created_by' => Auth()->id(),
                        ]);

                     }

            //Handle broker accounting if broker is self
            } elseif ($request->broker_id === '0' && $request->vendor_id) {
                // Handle case for "self" broker, where the account_id should be vendor_id
                $brokerAmount = $request->broker_amount;
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $request->vendor_id, // Vendor's account in case of "self" broker
                    'amount' => $brokerAmount,
                    'type' => 'credit',
                    'narration' => 'Brokerage for Bill #' . $newBillNumber,
                    'created_by' => Auth()->id(),
                ]);
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 10, // Purchase Brokers Expense account ID
                    'amount' => $brokerAmount,
                    'type' => 'debit',
                    'narration' => 'Brokerage for Bill #' . $newBillNumber,
                    'created_by' => Auth()->id(),
                ]);

                if($request->broker_wht_amount > 0) {
                    //if WHT on brokery amount
                    $brokerWHTAmount = $request->broker_wht_amount;

                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => 14 , // WHT Payable
                        'amount' => $brokerWHTAmount,
                        'type' => 'credit',
                        'narration' => 'Brokerage WHT for Bill #' . $newBillNumber,
                        'created_by' => Auth()->id(),
                    ]);

                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => $request->vendor_id,
                        'amount' => $brokerWHTAmount,
                        'type' => 'debit',
                        'narration' => 'Brokerage WHT for Bill #' . $newBillNumber,
                        'created_by' => Auth()->id(),
                    ]);

                }

            } else {

            }


            // Handle freight and delivery mode accounting
            if ($request->delivery_mode == 'ex-mill' && $request->freight > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 8, // Freight-In Expense account ID
                    'amount' => $request->freight,
                    'type' => 'debit',
                    'narration' => 'Freight-In for Bill #' . $newBillNumber,
                    'created_by' => Auth::id(),
                ]);
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 7, // Freight-In Payable account ID
                    'amount' => $request->freight,
                    'type' => 'credit',
                    'narration' => 'Freight-In for Bill #' . $newBillNumber,
                    'created_by' => Auth::id(),
                ]);
            } elseif ($request->delivery_mode == 'delivered' && $request->freight > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 7, // Freight-In Payable account ID
                    'amount' => $request->freight,
                    'type' => 'credit',
                    'narration' => 'Freight-In for Bill #' . $newBillNumber,
                    'created_by' => Auth::id(),
                ]);
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $request->vendor_id, // Vendor's account ID
                    'amount' => $request->freight,
                    'type' => 'debit',
                    'narration' => 'Freight-In for Bill #' . $newBillNumber,
                    'created_by' => Auth::id(),
                ]);
            }
        }

        // Commit the transaction
        DB::commit();

        // Flash success message
        session()->flash('message', 'Purchase Bill and Voucher saved successfully!');
        return redirect('farms/bills/farms');

    } catch (\Exception $e) {
        // Rollback the transaction in case of any error
        DB::rollBack();

        // Flash error message to the session
        session()->flash('formerrors', 'An error occurred while saving the bill. Please try again.');

        // Optionally, throw the exception to log it
        throw $e;
    }
}

    public function edit($id)
    {
        $companyId = session('company_id');
        $purchaseBill = PurchaseFarmBill::with('items.product')->findOrFail($id);
        $vendors = ChartOfAccount::where('is_customer_vendor', 'vendor')
        ->orWhere('is_customer_vendor', 'customer')
        ->get();

        $brokers = ChartOfAccount::where('is_customer_vendor', 'purchase_broker')->where('company_id', $companyId)->get();
        $purchaseOrders = PurchaseOrder::where('vendor_id', $purchaseBill->vendor_id)->where('status', '!=', 'completed')->get();

        $farmaccounts = ChartOfAccount::where(function($query) {
            $query->where('is_farm', 1);
        })
        ->orderBy('name')
        ->get();

        return view('purchase.billsfarms.edit', compact('purchaseBill', 'vendors', 'brokers', 'farmaccounts'));
    }

    public function update(Request $request, $id)
    {
        $purchaseBill = PurchaseFarmBill::findOrFail($id);

        // Validate inputs
        $validated = $request->validate([
            'vendor_id' => 'required',
            'bill_date' => 'required|date',
            'bill_due_days' => 'required|numeric',
            'vehicle_no' => 'nullable|string',
            'freight' => 'nullable|numeric|min:0',
            'delivery_mode' => 'nullable|string',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.net_amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Update purchase bill
            $purchaseBill->update([
                'vendor_id' => $request->vendor_id,
                'order_id' => $request->order_id,
                'bill_date' => $request->bill_date,
                'bill_due_days' => $request->bill_due_days,
                'vehicle_no' => $request->vehicle_no,
                'freight' => $request->freight,
                'broker_id' => $request->broker_id,
                'broker_rate' => $request->broker_rate ?? 0,
                'calculation_method' => $request->calculation_method,
                'broker_amount' => $request->broker_amount ?? 0,
                'broker_wht_rate' => $request->broker_wht_rate ?? 0,
                'broker_wht_amount' => $request->broker_wht_amount ?? 0,
                'broker_amount_with_wht' => $request->broker_amount_with_wht ?? 0,
                'delivery_mode' => $request->delivery_mode,
                'status' => 'posted',
                'company_id' => 1,
                'segment_id' => 1,
                'cost_center_id' => 1,
                'financial_year_id' => 1, // You may set this dynamically as needed
                'comments' => $request->comments,
                'farm_account' => $request->farm_account,
            ]);

          // Delete existing purchase items for this purchase bill
        $purchaseBill->items()->delete();

            // Add updated purchase items
            foreach ($request->items as $item) {
                PurchaseFarmBillItem::create([
                    'purchase_farm_bill_id' => $purchaseBill->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'deduction' => $item['deduction'] ?? 0, // Default deduction to 0 if not provided
                    'net_quantity' => $item['net_quantity'] ?? $item['quantity'], // Default net_quantity
                    'price' => $item['price'],
                    'gross_amount' => $item['gross_amount'] ?? 0, // Default gross amount to 0
                    'sales_tax_rate' => $item['sales_tax_rate'] ?? 0, // Default sales tax rate to 0
                    'sales_tax_amount' => $item['sales_tax_amount'] ?? 0, // Default sales tax amount to 0
                    'withholding_tax_rate' => $item['withholding_tax_rate'] ?? 0, // Default withholding tax rate to 0
                    'withholding_tax_amount' => $item['withholding_tax_amount'] ?? 0, // Default withholding tax amount to 0
                    'net_amount' => $item['net_amount'],
                ]);
            }

            // Retrieve the existing voucher (based on the current purchase bill's reference number)
            $voucher = Voucher::where('reference_number', $purchaseBill->bill_number)->first();
            if (!$voucher) {
                throw new \Exception("Voucher not found for the given bill.");
            }

            // Delete existing voucher details
            VoucherDetail::where('voucher_id', $voucher->id)->delete();

            // Update the voucher details with new data
            $totalNetAmount = collect($request->items)->sum('net_amount');
            $totalSalesTax = collect($request->items)->sum(function ($item) {
                return $item['sales_tax_amount'] ?? 0;
            });

            // Create voucher details
            VoucherDetail::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->vendor_id, // Vendor's account
                'amount' => $totalNetAmount,
                'type' => 'credit',
                'narration' => 'Purchases for Bill #' . $purchaseBill->bill_number,
                'created_by' => Auth::id(),
            ]);

            VoucherDetail::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->farm_account, // Debit account for purchases
                'amount' => $totalNetAmount - $totalSalesTax,
                'type' => 'debit',
                'narration' => 'Purchases for Bill #' . $purchaseBill->bill_number,
                'created_by' => Auth::id(),
            ]);

            // Add sales tax payable entry if applicable
            if ($totalSalesTax > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 11, // Sales Tax Payable account ID
                    'amount' => $totalSalesTax,
                    'type' => 'debit',
                    'narration' => 'Sales Tax Payable for Bill #' . $purchaseBill->bill_number,
                    'created_by' => Auth::id(),
                ]);
            }

            // Handle broker accounting if broker exists
            if ($request->broker_id && $request->broker_id !== '0') {
                $brokerAmount = $request->broker_amount;
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $request->broker_id, // Broker's account
                    'amount' => $brokerAmount,
                    'type' => 'credit',
                    'narration' => 'Brokerage for Bill #' . $purchaseBill->bill_number,
                    'created_by' => Auth::id(),
                ]);

                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 10, // Purchase Brokers Expense account ID
                    'amount' => $brokerAmount,
                    'type' => 'debit',
                    'narration' => 'Brokerage for Bill #' . $purchaseBill->bill_number,
                    'created_by' => Auth::id(),
                ]);

                if ($request->broker_wht_amount > 0) {
                    $brokerWHTAmount = $request->broker_wht_amount;

                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => 14, // WHT Payable
                        'amount' => $brokerWHTAmount,
                        'type' => 'credit',
                        'narration' => 'Brokerage WHT for Bill #' . $purchaseBill->bill_number,
                        'created_by' => Auth::id(),
                    ]);

                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => $request->broker_id,
                        'amount' => $brokerWHTAmount,
                        'type' => 'debit',
                        'narration' => 'Brokerage WHT for Bill #' . $purchaseBill->bill_number,
                        'created_by' => Auth::id(),
                    ]);
                }

            } elseif ($request->broker_id === '0' && $request->vendor_id) {
                // Handle case for "self" broker, where the account_id should be vendor_id
                $brokerAmount = $request->broker_amount;
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $request->vendor_id, // Vendor's account in case of "self" broker
                    'amount' => $brokerAmount,
                    'type' => 'credit',
                    'narration' => 'Brokerage for Bill #' . $purchaseBill->bill_number,
                    'created_by' => Auth::id(),
                ]);

                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 10, // Purchase Brokers Expense account ID
                    'amount' => $brokerAmount,
                    'type' => 'debit',
                    'narration' => 'Brokerage for Bill #' . $purchaseBill->bill_number,
                    'created_by' => Auth::id(),
                ]);

                if ($request->broker_wht_amount > 0) {
                    $brokerWHTAmount = $request->broker_wht_amount;

                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => 14, // WHT Payable
                        'amount' => $brokerWHTAmount,
                        'type' => 'credit',
                        'narration' => 'Brokerage WHT for Bill #' . $purchaseBill->bill_number,
                        'created_by' => Auth::id(),
                    ]);

                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => $request->vendor_id,
                        'amount' => $brokerWHTAmount,
                        'type' => 'debit',
                        'narration' => 'Brokerage WHT for Bill #' . $purchaseBill->bill_number,
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            // Handle freight and delivery mode accounting
            if ($request->delivery_mode == 'ex-mill' && $request->freight > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 8, // Freight-In Expense account ID
                    'amount' => $request->freight,
                    'type' => 'debit',
                    'narration' => 'Freight-In for Bill #' . $purchaseBill->bill_number,
                    'created_by' => Auth::id(),
                ]);
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 7, // Freight-In Payable account ID
                    'amount' => $request->freight,
                    'type' => 'credit',
                    'narration' => 'Freight-In for Bill #' . $purchaseBill->bill_number,
                    'created_by' => Auth::id(),
                ]);
            } elseif ($request->delivery_mode == 'delivered' && $request->freight > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 7, // Freight-In Payable account ID
                    'amount' => $request->freight,
                    'type' => 'credit',
                    'narration' => 'Freight-In for Bill #' . $purchaseBill->bill_number,
                    'created_by' => Auth::id(),
                ]);
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $request->vendor_id, // Vendor's account ID
                    'amount' => $request->freight,
                    'type' => 'debit',
                    'narration' => 'Freight-In for Bill #' . $purchaseBill->bill_number,
                    'created_by' => Auth::id(),
                ]);
            }

            // Commit the transaction
            DB::commit();

            session()->flash('message', 'Purchase Bill and Voucher updated successfully!');
            return redirect('farms/bills/farms');

        } catch (\Exception $e) {
            // Rollback the transaction in case of any error
            DB::rollBack();

            session()->flash('formerrors', 'An error occurred while updating the bill. Please try again.');
            throw $e;
        }
    }






}
