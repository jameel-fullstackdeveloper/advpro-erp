<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesProduct;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\CustomerDetail;
use App\Models\Company;
use App\Models\Costcenter;
use App\Models\ChartOfAccount; // Import ChartOfAccount for customers
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Items;


class InvoiceController extends Controller
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

        $companyId = session('company_id'); // Retrieve the company_id from the session

        // Fetch sales orders filtered by company_id
        $salesOrders = SalesOrder::where('company_id', $companyId)->get();

        $products = Items::where(function ($query) {
            $query->where('item_type', 'sale') // for item_type 'sale'
                  ->orWhere(function ($query) {
                      $query->where('item_type', 'purchase') // for item_type 'purchase'
                            ->where('can_be_sale', 1); // can_be_sale should be 1
                  });
        })
        ->orderBy('name')
        ->get();

        $customers = ChartOfAccount::where(function($query) {
            $query->where('is_customer_vendor', 'customer')
                  //->orWhere('is_customer_vendor', 'vendor')
                  ->orWhere('is_customer_vendor', 'farm');
        })
        ->orderBy('name')
        ->get();



        $brokers = ChartOfAccount::where('is_customer_vendor', 'sales_broker')
                ->where('company_id', $companyId) // Filter by company_id
                ->orderBy('name')
                ->get();

        //Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber();

        $segments = Company::all();  // Fetch all segments (companies)

        return view('sales.invoices.create', compact('products', 'customers', 'brokers', 'invoiceNumber','segments'));
    }

    public function store(Request $request) {

        // Get current year
        $currentYear = date('Y');

        // Define your maximum allowed back year (e.g., 2 years ago)
        $allowedYear = $currentYear - 2;


            // Validate the invoice inputs
            $customMessages = [
                'items.*.product_id.required' => 'Product is required.',
                'items.*.quantity.required' => 'Quantity is required.',
                'items.*.quantity.min' => 'Quantity must be at least 1.',
                'items.*.unit_price.required' => 'Unit price is required.',
                'items.*.unit_price.min' => 'The field must be at least 1.',
                'items.*.net_amount.required' => 'Amount excluding tax is required.',
                'items.*.amount_excl_tax.required' => 'Amount excluding tax is required.',
                'items.*.amount_incl_tax.required' => 'Amount including tax is required.',
            ];

            // Validate form data
            $validated = $request->validate([
                'customer_id' => 'required',
                'invoice_number' => 'required|unique:sales_invoices,invoice_number',
                'invoice_date' => 'required|date|date_format:Y-m-d|after_or_equal:' . $allowedYear . '-01-01|before_or_equal:' . $currentYear . '-12-31',
                'invoice_due_days' => 'required',
                'farm_supervisor_mobile' => 'nullable|numeric|digits:11',
                'items.*.product_id' => 'required',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.unit_price' => 'required|numeric|min:1',
                'items.*.net_amount' => 'required|numeric|min:0',
                'items.*.amount_excl_tax' => 'required|numeric|min:0',
                'items.*.amount_incl_tax' => 'required|numeric|min:0',
            ], $customMessages);


            DB::beginTransaction();

            try {
                // Create new Sales Order
                $salesOrder = SalesOrder::create([
                    'order_number' => $this->generateOrderNumber(), // Generate order number
                    'customer_id' => $request->customer_id,
                    'farm_name' => $request->farm_name,
                    'farm_address' => $request->farm_address,
                    'farm_supervisor_mobile' => $request->farm_supervisor_mobile,
                    'vehicle_no' => $request->vehicle_no,
                    'vehicle_fare' => $request->vehicle_fare ?? 0,
                    'order_date' => $request->invoice_date,
                    'status' => 'confirmed',
                    'company_id' => session('company_id'),
                    'order_comments' => 'Generated from Sale Invoice', // Save comments
                    'created_by' => Auth()->id(),
                    'created' => 1, // 1 for generated by sale module
                ]);

                // Save products in the order
                foreach ($request->items as $item) {
                    SalesOrderItem::create([
                        'sales_order_id' => $salesOrder->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }

                // Link the order to the invoice
                $request->sales_order_id = $salesOrder->id;

                $brokerRate = $request->broker_rate ?? 0; // Set to 0 if not provided
                $broker_amount = $request->broker_amount ?? 0; // Set to 0 if not provided
                // Create the invoice
                $invoiceData = [
                    'sales_order_id' => $request->sales_order_id,
                    'invoice_date' => $request->invoice_date,
                    'invoice_due_days' => $request->invoice_due_days,
                    'invoice_number' => $request->invoice_number,
                    'customer_id' => $request->customer_id,
                    'broker_id' => $request->broker_id,
                    'broker_rate' => $brokerRate,
                    'calculation_method' => $request->calculation_method,
                    'broker_amount' => $broker_amount,
                    'comments' => $request->comments,
                    'status' => 'posted',
                    'segment_id' => 1,
                    'cost_center_id' => 1,
                    'company_id' => session('company_id'),
                    'freight_credit_to' => $request->vehicle_fare_adj,
                    'financial_year_id' => $request->financial_year_id,
                ];

                // Create the new invoice
                $invoiceData['created_by'] = Auth()->id();
                $invoice = SalesInvoice::create($invoiceData);

                // Save invoice items
                foreach ($request->items as $item) {
                    SalesInvoiceItem::create([
                        'sales_invoice_id' => $invoice->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'net_amount' => round($item['net_amount'] ?? 0, 2),
                        'discount_rate' => $item['discount_rate'] ?? 0,
                        'discount_amount' => round($item['discount_amount'] ?? 0, 2),
                        'discount_per_bag_rate' => $item['discount_per_bag_rate'] ?? 0,
                        'discount_per_bag_amount' => round($item['discount_per_bag_amount'] ?? 0, 2),
                        'amount_excl_tax' => round($item['amount_excl_tax'] ?? 0, 2),
                        'amount_incl_tax' => round($item['amount_incl_tax'] ?? 0, 2),
                        'created_by' => Auth()->id(),
                    ]);
                }

                // Create a voucher
                $voucher = Voucher::create([
                    'voucher_type' => 'sales-invoice',
                    'date' => $request->invoice_date,
                    'reference_number' => $request->invoice_number,
                    'total_amount' => collect($request->items)->sum('amount_incl_tax'),
                    'description' => 'Invoice #' . $request->invoice_number,
                    'status' => 1,
                    'company_id' => session('company_id'),
                    'segment_id' => 1,
                    'cost_center_id' => 1,
                    'created_by' => Auth()->id(),
                ]);

                // Voucher details: Sales, discount, sales tax, etc.
                $salesAccount = 4; // Sales account ID
                $totalSales = collect($request->items)->sum(function ($item) {
                    return $item['quantity'] * $item['unit_price'];
                });

                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $salesAccount,
                    'amount' => $totalSales,
                    'type' => 'credit',
                    'narration' => 'Sales from Invoice #' . $request->invoice_number,
                    'segment_id' => 1,
                    'cost_center_id' => 1,
                    'created_by' => Auth()->id(),
                ]);

                 // Record the discount (debit entry for discount account) percentage
            $discountAccount = 3; // Assuming discount account ID is 10, adjust accordingly
            $totalDiscount = collect($request->items)->sum('discount_amount');
            if ($totalDiscount > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $discountAccount, // Discount account ID
                    'amount' => $totalDiscount,
                    'type' => 'debit',
                    'narration' => 'Discount(%) for Invoice #' . $request->invoice_number,
                    'segment_id' => 1,
                    'cost_center_id' => 1,
                    'created_by' => Auth()->id(),
                ]);
            }

             // Record the discount (debit entry for discount account) percentage
             $discountbagAccount = 3; // Assuming discount account ID is 10, adjust accordingly
             $totalDiscountbag = collect($request->items)->sum('discount_per_bag_amount');
             if ($totalDiscountbag > 0) {
                 VoucherDetail::create([
                     'voucher_id' => $voucher->id,
                     'account_id' => $discountbagAccount, // Discount account ID
                     'amount' => $totalDiscountbag,
                     'type' => 'debit',
                     'narration' => 'Discount(per bag) for Invoice #' . $request->invoice_number,
                     'segment_id' => 1,
                     'cost_center_id' => 1,
                     'created_by' => Auth()->id(),
                 ]);
             }

            // Record the sals tax payable
            $salestaxDiscount = collect($request->items)->sum('sales_tax_amount');
            if ($salestaxDiscount > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 11, // Sales Tax account ID
                    'amount' => $salestaxDiscount,
                    'type' => 'credit',
                    'narration' => 'Sales Tax for Invoice #' . $request->invoice_number,
                    'segment_id' => 1,
                    'cost_center_id' => 1,
                    'created_by' => Auth()->id(),
                ]);
            }

            // Record the furher tax payable
            $furtherTax = collect($request->items)->sum('further_sales_tax_amount');
            if ($furtherTax > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 13, // Sales Tax account ID
                    'amount' => $furtherTax,
                    'type' => 'credit',
                    'narration' => 'Further Tax for Invoice #' . $request->invoice_number,
                    'segment_id' => 1,
                    'cost_center_id' => 1,
                    'created_by' => Auth()->id(),
                ]);
            }


            // Record the advance wht tax payable
            $advanceTax = collect($request->items)->sum('advance_wht_amount');
            if ($advanceTax > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 14, // Sales Tax account ID
                    'amount' => $advanceTax,
                    'type' => 'credit',
                    'narration' => 'Advance WHT for Invoice #' . $request->invoice_number,
                    'segment_id' => 1,
                    'cost_center_id' => 1,
                    'created_by' => Auth()->id(),
                ]);
            }

            // Update or create voucher details for brokerage if applicable
            if ($request->broker_amount > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $request->broker_id, // Broker's account ID
                    'amount' => $request->broker_amount,
                    'type' => 'credit',
                    'narration' => 'Brokerage for Invoice #' . $request->invoice_number,
                    'segment_id' => 1,
                    'cost_center_id' => 1,
                    'created_by' => Auth()->id(),
                ]);

                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 9, // Broker's account ID
                    'amount' => $request->broker_amount,
                    'type' => 'debit',
                    'narration' => 'Brokerage for Invoice #' . $request->invoice_number,
                    'segment_id' => 1,
                    'cost_center_id' => 1,
                    'created_by' => Auth()->id(),
                ]);
            }

             // if fright needs to credit to cusmter

            if($request->vehicle_fare_adj == 1 && $request->vehicle_fare > 0 ) {
                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => $request->customer_id, // Accounts receivable account ID
                        'amount' => $request->vehicle_fare, // Debit the total amount including tax
                        'type' => 'credit',
                        'narration' => 'Invoice #' . $request->invoice_number . ' Freight',
                        'segment_id' => 1,
                        'cost_center_id' => 1,
                        'created_by' => Auth()->id(),
                    ]);

                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => 6, // Accounts
                        'amount' => $request->vehicle_fare, //Freight-Out Exp
                        'type' => 'debit',
                        'narration' => 'Invoice #' . $request->invoice_number,
                        'segment_id' => 1,
                        'cost_center_id' => 1,
                        'created_by' => Auth()->id(),
                    ]);
                } else if($request->vehicle_fare_adj == 2 && $request->vehicle_fare > 0){
                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => 5, // Accounts Freight-Out Payable
                        'amount' => $request->vehicle_fare, // Debit the total amount including tax
                        'type' => 'credit',
                        'narration' => 'Invoice #' . $request->invoice_number,
                        'segment_id' => 1,
                        'cost_center_id' => 1,
                        'created_by' => Auth()->id(),
                    ]);

                    //exp
                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => 6, // Accounts
                        'amount' => $request->vehicle_fare, // Freight-Out Exp
                        'type' => 'debit',
                        'narration' => 'Invoice #' . $request->invoice_number,
                        'segment_id' => 1,
                        'cost_center_id' => 1,
                        'created_by' => Auth()->id(),
                    ]);

                }

            // Get the vehicle number
            $vehicleNo = $request->vehicle_no ?? 'No vehicle'; // Fallback if vehicle_no is not provided

            // Calculate total discounts
            $totalDiscount = collect($request->items)->sum('discount_amount') + collect($request->items)->sum('discount_per_bag_amount');

            // Gather product details (name and quantity)
            $productDetails = collect($request->items)->map(function ($item) {
                // Assuming you have a Product model and 'name' is a column in the products table
                $product = Items::find($item['product_id']);
                if ($product) {
                    return $product->name . ' (' . $item['quantity'] . ' bags)';
                } else {
                    return 'Unknown Product (' . $item['quantity'] . ')'; // Fallback if product is not found
                }
            })->implode(', ');


            // record the customer's debit (debit entry for accounts receivable)
            $totalAmountInclTax = collect($request->items)->sum('amount_incl_tax');
            VoucherDetail::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->customer_id, // Accounts receivable account ID
                'amount' => $totalAmountInclTax, // Debit the total amount including tax
                'type' => 'debit',
                //'narration' => 'Invoice #' . $request->invoice_number . ' - Vehicle: ' . $vehicleNo . ', Discount: ' . number_format($totalDiscount) . ', Products: ' . $productDetails,
                'narration' => 'Invoice #' . $request->invoice_number . ' - Vehicle: ' . $vehicleNo . ', Products: ' . $productDetails,
                'segment_id' => 1,
                'cost_center_id' => 1,

                'created_by' => Auth()->id(),
            ]);

            // Commit the transaction if everything is successful
            DB::commit();

            session()->flash('message', 'Sales Invoice saved successfully!');
            return redirect('sales/invoices');

            } catch (\Exception $e) {
             // Rollback the transaction in case of an error
                DB::rollBack();
                session()->flash('formerrors', 'An error occurred while saving the invoice. Please try again.');
                return redirect()->back()->withErrors($e->getMessage()); // Stay on the same page and show the error

            }

    }

    private function generateInvoiceNumber()
    {
        $company = Company::where('id', session('company_id'))->first(); // Fetch the company record
        $companyAbbreviation = $company ? $company->abv : 'INV'; // Default to 'INV' if not found

        $currentDate = now();

        // Determine the start of the fiscal year (1st July)
        $fiscalYearStart = $currentDate->month >= 7
            ? $currentDate->copy()->month(7)->startOfMonth()
            : $currentDate->copy()->subYear()->month(7)->startOfMonth();

        // Extract the fiscal year (start and end year)
        $fiscalYear = $fiscalYearStart->format('Y') . '-' . $fiscalYearStart->copy()->addYear()->format('Y');

        // Get the last two digits of the current year for use in the invoice number
        $currentYear = substr($currentDate->format('Y'), -2); // Last two digits of the year

        // Count the number of invoices in the current fiscal year
        $invoiceCount = SalesInvoice::where('invoice_date', '>=', $fiscalYearStart)
            ->where('invoice_date', '<', $fiscalYearStart->copy()->addYear()) // Ensure it's within the fiscal year
            ->count();

        // Start numbering from 1 for the new fiscal year
        $nextInvoiceNumber = $invoiceCount + 1;

        do {
            // Format the next invoice number with leading zeros
            $formattedNumber = str_pad($nextInvoiceNumber, 3, '0', STR_PAD_LEFT);
            // Generate the invoice number in the format: [CompanyAbbreviation]-[FiscalYearLast2Digits]-[FormattedNumber]
            $invoiceNumber = 'INV-FM'  . $currentYear . '-' . $formattedNumber;

            // Check if the invoice number already exists in the database (including the fiscal year logic)
            $invoiceExists = SalesInvoice::where('invoice_number', $invoiceNumber)->exists();

            if ($invoiceExists) {
                // If the number exists, increment and retry
                $nextInvoiceNumber++;
            }
        } while ($invoiceExists);

        return $invoiceNumber;
    }


    public function getCustomerDetails($customerId)
    {
        $customer = CustomerDetail::where('account_id', $customerId)->first();

        // Debugging: Check if the customer exists and fields are present
        if ($customer) {
            // Check if payment_terms and discount are available
            if (!isset($customer->payment_terms) || !isset($customer->discount)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer data is incomplete'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'invoice_due_days' => $customer->payment_terms, // Assuming this is the correct field for due days
                'discount_rate' => $customer->discount // Assuming this is the correct field for discount rate
            ]);
        }

        // Return an error response if the customer is not found
        return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
    }

    public function getProductDetails($productId)
    {
        $product = Items::where('id', $productId)->first();

        // Debugging: Check if the customer exists and fields are present
        if ($product) {
            // Check if payment_terms and discount are available
            if (!isset($product->sale_price)) {
                return response()->json([
                    'success' => false,
                    'message' => 'product data is incomplete'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'price' => $product->sale_price, // Assuming this is the correct field for due days
            ]);
        }

        // Return an error response if the product is not found
        return response()->json(['success' => false, 'message' => 'product not found'], 404);
    }

    public function getBrokerDetails($brokerId)
    {
        $broker = CustomerDetail::where('account_id', $brokerId)->first();

        // Debugging: Check if the customer exists and fields are present
        if ($broker) {
            // Check if payment_terms and discount are available
            if (!isset($broker->broker_rate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'broker data is incomplete'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'broker_rate' => $broker->broker_rate, // Assuming this is the correct field for due days
            ]);
        }

        // Return an error response if the broker is not found
        return response()->json(['success' => false, 'message' => 'broker not found'], 404);
    }


    private function generateOrderNumber()
    {
        $company = Company::where('id', session('company_id'))->first(); // Fetch the company record
        $companyAbbreviation = $company ? $company->abv : 'SO'; // Default to 'SO' if not found

        $currentDate = now();

        // Determine the start of the fiscal year (1st July)
        $fiscalYearStart = $currentDate->month >= 7
            ? $currentDate->copy()->month(7)->startOfMonth()
            : $currentDate->copy()->subYear()->month(7)->startOfMonth();

        // Extract the fiscal year (start and end year)
        $fiscalYear = $fiscalYearStart->format('Y') . '-' . $fiscalYearStart->copy()->addYear()->format('Y');

        // Get the last two digits of the current year for use in the order number
        $currentYear = substr($currentDate->format('Y'), -2); // Last two digits of the year

        // Count the number of orders in the current fiscal year
        $voucherCount = SalesOrder::where('order_date', '>=', $fiscalYearStart)
            ->where('order_date', '<', $fiscalYearStart->copy()->addYear()) // Ensure it's within the fiscal year
            ->count();

        // Start numbering from 1 for the new fiscal year
        $nextVoucherNumber = $voucherCount + 1;

        do {
            // Format the next voucher number with leading zeros
            $formattedNumber = str_pad($nextVoucherNumber, 3, '0', STR_PAD_LEFT);
            // Generate the voucher number in the format: [CompanyAbbreviation]-[FiscalYearLast2Digits]-[FormattedNumber]
            $voucherNumber = $companyAbbreviation . '-SO'  . $currentYear . '-' . $formattedNumber;

            // Check if the voucher number already exists in the database (including the fiscal year logic)
            $voucherExists = SalesOrder::where('order_number', $voucherNumber)->exists();

            if ($voucherExists) {
                // If the number exists, increment and retry
                $nextVoucherNumber++;
            }
        } while ($voucherExists);

        return $voucherNumber;
    }




    public function edit($id)
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session
        $invoice = SalesInvoice::with(['items'])->where('company_id', $companyId)->findOrFail($id);

        // Fetch additional related data
        $salesOrders = SalesOrder::where('id', $invoice->sales_order_id)->first();

     //   dd($salesOrders);


        $products = Items::where(function ($query) {
            $query->where('item_type', 'sale') // for item_type 'sale'
                ->orWhere(function ($query) {
                    $query->where('item_type', 'purchase') // for item_type 'purchase'
                            ->where('can_be_sale', 1); // can_be_sale should be 1
                });
        })
        ->orderBy('name')
        ->get();


        $customers = ChartOfAccount::where(function($query) {
                               $query->where('is_customer_vendor', 'customer')
                                      //->orWhere('is_customer_vendor', 'vendor')
                                      ->orWhere('is_customer_vendor', 'farm');
                           })
                           ->orderBy('name')
                           ->get();

        $brokers = ChartOfAccount::where('is_customer_vendor', 'sales_broker')
        ->orderBy('name')
                                  ->get();



        $segments = Company::all();  // Fetch all segments (companies)
        $costcenters = Costcenter::where('segment_id', $invoice->segment_id)->get();


        // Pass the invoice along with other data to the view
        return view('sales.invoices.edit', compact('invoice', 'products', 'customers', 'brokers', 'salesOrders','segments','costcenters'));
    }


    public function update(Request $request, $id)
    {
           // Get current year
           $currentYear = date('Y');

           // Define your maximum allowed back year (e.g., 2 years ago)
           $allowedYear = $currentYear - 2;

        $customMessages = [
            'items.*.product_id.required' => 'Product is required.',
            'items.*.quantity.required' => 'Quantity is required.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.unit_price.required' => 'Unit price is required.',
            'items.*.unit_price.min' => 'The field must be at least 1.',
            'items.*.net_amount.required' => 'Amount excluding tax is required.',
            'items.*.amount_incl_tax.required' => 'Amount including tax is required.',
        ];

        // Validate form data
        $validated = $request->validate([
            'customer_id' => 'required',
            'invoice_number' => 'required|unique:sales_invoices,invoice_number', // Ensure invoice_number is unique
            'invoice_date' => 'required|date|date_format:Y-m-d|after_or_equal:' . $allowedYear . '-01-01|before_or_equal:' . $currentYear . '-12-31',
            'invoice_due_days' => 'required',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:1',
            'items.*.net_amount' => 'required|numeric|min:0',
            'items.*.amount_incl_tax' => 'required|numeric|min:0',
        ], $customMessages);

        DB::beginTransaction();

        try {
            $invoice = SalesInvoice::findOrFail($id);

            // Find the existing sales order by ID
            $salesOrder = SalesOrder::findOrFail($invoice->sales_order_id); // Replace $orderId with the correct order ID

            // Update sales order details
            $salesOrder->update([
                'customer_id' => $request->customer_id,
                'farm_name' => $request->farm_name,
                'farm_address' => $request->farm_address,
                'farm_supervisor_mobile' => $request->farm_supervisor_mobile,
                'vehicle_no' => $request->vehicle_no,
                'vehicle_fare' => $request->vehicle_fare ?? 0,
                'order_date' => $request->invoice_date,
                'status' => 'confirmed',
                'company_id' => session('company_id'),
                'order_comments' => 'Updated from Sale Invoice', // Save comments to indicate update
                'created_by' => Auth()->id(),
            ]);

            // Delete existing order items before saving new ones (if needed)
            $salesOrder->items()->delete();

            // Save updated products in the order
            foreach ($request->items as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }


            // Update invoice details
            $invoice->update([
                'invoice_date' => $request->invoice_date,
                'invoice_number' => $request->invoice_number,
                'invoice_due_days' => $request->invoice_due_days,
                'customer_id' => $request->customer_id,
                'broker_id' => $request->broker_id,
                'broker_rate' => $request->broker_rate ?? 0,
                'broker_amount' => $request->broker_amount ?? 0,
                'calculation_method' => $request->calculation_method,
                'freight_credit_to' => $request->vehicle_fare_adj,
                'segment_id' => 1,
                'cost_center_id' => 1,
                'updated_by' => Auth()->id(),
            ]);

            // Delete existing invoice items and voucher details
            $invoice->items()->delete();

            // Delete existing vouchers and voucher details if they exist
            $existingVoucher = Voucher::where('reference_number', $invoice->invoice_number)->first();
            if ($existingVoucher) {
                // Delete all voucher details related to this voucher
                VoucherDetail::where('voucher_id', $existingVoucher->id)->delete();

                // Delete the voucher itself
                $existingVoucher->delete();
            }

            // Save updated invoice items with 'created_by' field
            foreach ($request->items as $item) {
                $invoice->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'net_amount' => round($item['net_amount'] ?? 0, 2),
                    'discount_rate' => $item['discount_rate'] ?? 0,
                    'discount_amount' => round($item['discount_amount'] ?? 0, 2),
                    'discount_per_bag_rate' => $item['discount_per_bag_rate'] ?? 0,
                    'discount_per_bag_amount' => round($item['discount_per_bag_amount'] ?? 0, 2),
                    'amount_excl_tax' => round($item['amount_excl_tax'] ?? 0, 2),
                    'amount_incl_tax' => round($item['amount_incl_tax'] ?? 0, 2),
                    'created_by' => Auth()->id(),
                ]);
            }

            // Create new voucher
            $voucher = Voucher::create([
                'voucher_type' => 'sales-invoice',
                'date' => $request->invoice_date,
                'reference_number' => $invoice->invoice_number,
                'total_amount' => collect($request->items)->sum('amount_incl_tax'),
                'description' => 'Invoice #' . $request->invoice_number,
                'status' => 1, // Active status
                'segment_id' => 1,
                'cost_center_id' => 1,
                'company_id' => session('company_id'),
                'created_by' => Auth()->id(),
            ]);

            // Create voucher details (Sales, Discount, Tax, etc.)
            $salesAccount = 4; // Sales account ID
            $totalSales = collect($request->items)->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            VoucherDetail::create([
                'voucher_id' => $voucher->id,
                'account_id' => $salesAccount,
                'amount' => $totalSales,
                'type' => 'credit',
                'narration' => 'Sales from Invoice #' . $request->invoice_number,
                'segment_id' => 1,
                'cost_center_id' => 1,
                'created_by' => Auth()->id(),
            ]);

            // Record the discount (debit entry for discount account)
            $discountAccount = 3; // Assuming discount account ID
            $totalDiscount = collect($request->items)->sum('discount_amount');
            if ($totalDiscount > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $discountAccount,
                    'amount' => $totalDiscount,
                    'type' => 'debit',
                    'narration' => 'Discount(%) for Invoice #' . $request->invoice_number,
                    'segment_id' => 1,
                    'cost_center_id' => 1,
                    'created_by' => Auth()->id(),
                ]);
            }

            // Record the discount (debit entry for discount account) percentage
            $discountbagAccount = 3; // Assuming discount account ID is 10, adjust accordingly
            $totalDiscountbag = collect($request->items)->sum('discount_per_bag_amount');
            if ($totalDiscountbag > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $discountbagAccount, // Discount account ID
                    'amount' => $totalDiscountbag,
                    'type' => 'debit',
                    'narration' => 'Discount(per bag) for Invoice #' . $request->invoice_number,
                    'segment_id' => 1,
                    'cost_center_id' => 1,
                    'created_by' => Auth()->id(),
                ]);
            }

            // Record sales tax payable
            $salesTax = collect($request->items)->sum('sales_tax_amount');
            if ($salesTax > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 11, // Sales Tax payable account ID
                    'amount' => $salesTax,
                    'type' => 'credit',
                    'narration' => 'Sales Tax for Invoice #' . $request->invoice_number,
                    'segment_id' => 1,
                    'cost_center_id' => 1,
                    'created_by' => Auth()->id(),
                ]);
            }

            // Record further tax payable
            $furtherTax = collect($request->items)->sum('further_sales_tax_amount');
            if ($furtherTax > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 13, // Further tax payable account ID
                    'amount' => $furtherTax,
                    'type' => 'credit',
                    'narration' => 'Further Tax for Invoice #' . $request->invoice_number,
                    'segment_id' => 1,
                    'cost_center_id' => 1,
                    'created_by' => Auth()->id(),
                ]);
            }

            // Record advance WHT payable
            $advanceTax = collect($request->items)->sum('advance_wht_amount');
            if ($advanceTax > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 14, // Advance WHT payable account ID
                    'amount' => $advanceTax,
                    'type' => 'credit',
                    'narration' => 'Advance WHT for Invoice #' . $request->invoice_number,
                    'segment_id' => 1,
                    'cost_center_id' => 1,
                    'created_by' => Auth()->id(),
                ]);
            }

            // Record brokerage if applicable
             if ($request->broker_amount > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $request->broker_id, // Broker's account ID
                    'amount' => $request->broker_amount,
                    'type' => 'credit',
                    'narration' => 'Brokerage for Invoice #' . $request->invoice_number,
                    'segment_id' => 1,
                    'cost_center_id' => 1,
                    'created_by' => Auth()->id(),
                ]);

                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 9, // Broker's account ID
                    'amount' => $request->broker_amount,
                    'type' => 'debit',
                    'narration' => 'Brokerage for Invoice #' . $request->invoice_number,
                    'segment_id' => 1,
                    'cost_center_id' => 1,
                    'created_by' => Auth()->id(),
                ]);
            }


            // freight
            if($request->vehicle_fare_adj == 1 && $request->vehicle_fare > 0 ) {
                        VoucherDetail::create([
                            'voucher_id' => $voucher->id,
                            'account_id' => $request->customer_id, // Accounts receivable account ID
                            'amount' => $request->vehicle_fare, // Debit the total amount including tax
                            'type' => 'credit',
                            'narration' => 'Invoice #' . $request->invoice_number . ' Freight',
                            'segment_id' => 1,
                            'cost_center_id' => 1,
                            'created_by' => Auth()->id(),
                        ]);

                        VoucherDetail::create([
                            'voucher_id' => $voucher->id,
                            'account_id' => 6, // Accounts
                            'amount' => $request->vehicle_fare, //Freight-Out Exp
                            'type' => 'debit',
                            'narration' => 'Invoice #' . $request->invoice_number,
                            'segment_id' => 1,
                            'cost_center_id' => 1,
                            'created_by' => Auth()->id(),
                        ]);
                    } else if($request->vehicle_fare_adj == 2 && $request->vehicle_fare > 0){
                        VoucherDetail::create([
                            'voucher_id' => $voucher->id,
                            'account_id' => 5, // Accounts Freight-Out Payable
                            'amount' => $request->vehicle_fare, // Debit the total amount including tax
                            'type' => 'credit',
                            'narration' => 'Invoice #' . $request->invoice_number,
                            'segment_id' => 1,
                            'cost_center_id' => 1,
                            'created_by' => Auth()->id(),
                        ]);

                        //exp
                        VoucherDetail::create([
                            'voucher_id' => $voucher->id,
                            'account_id' => 6, // Accounts
                            'amount' => $request->vehicle_fare, // Freight-Out Exp
                            'type' => 'debit',
                            'narration' => 'Invoice #' . $request->invoice_number,
                            'segment_id' => 1,
                            'cost_center_id' => 1,
                            'created_by' => Auth()->id(),
                        ]);

                    }

            // Get the vehicle number
            $vehicleNo = $request->vehicle_no ?? 'No vehicle'; // Fallback if vehicle_no is not provided


            // Gather product details (name and quantity)
            $productDetails = collect($request->items)->map(function ($item) {
                // Assuming you have a Product model and 'name' is a column in the products table
                $product = SalesProduct::find($item['product_id']);
                if ($product) {
                    return $product->product_name . ' (' . $item['quantity'] . ' bags)';
                } else {
                    return 'Unknown Product (' . $item['quantity'] . ')'; // Fallback if product is not found
                }
            })->implode(', ');

            // record the customer's debit (debit entry for accounts receivable)
            $totalAmountInclTax = collect($request->items)->sum('amount_incl_tax');
            VoucherDetail::create([
                'voucher_id' => $voucher->id,
                'account_id' => $request->customer_id, // Accounts receivable account ID
                'amount' => $totalAmountInclTax, // Debit the total amount including tax
                'type' => 'debit',
                //'narration' => 'Invoice #' . $request->invoice_number . ' - Vehicle: ' . $vehicleNo . ', Discount: ' . number_format($totalDiscount) . ', Products: ' . $productDetails,
                'narration' => 'Invoice #' . $request->invoice_number . ' - Vehicle: ' . $vehicleNo .  ', Products: ' . $productDetails,
                'segment_id' => 1,
                'cost_center_id' => 1,

                'created_by' => Auth()->id(),
            ]);

            // Commit the transaction if everything is successful
            DB::commit();

            session()->flash('message', 'Sales Invoice updated successfully!');
            return redirect('sales/invoices');
        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollBack();
            session()->flash('formerrors', 'An error occurred while updating the invoice. Please try again.');
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function getSegmentDetails($segmentId)
    {
        $costcenters = Costcenter::where('segment_id', $segmentId)->get();

        if ($costcenters->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No cost centers found for the selected segment'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'costcenters' => $costcenters
        ]);
    }







}
