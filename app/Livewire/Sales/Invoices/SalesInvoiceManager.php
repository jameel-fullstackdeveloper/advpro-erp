<?php

namespace App\Livewire\Sales\Invoices;

use Livewire\Component;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesProduct;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\Company;
use App\Models\ChartOfAccount; // Import ChartOfAccount for customers
use Livewire\WithPagination;
use Carbon\Carbon;
use App\Models\Items;
use Illuminate\Support\Facades\DB;

class SalesInvoiceManager extends Component
{
    use WithPagination;

    public $sales_order_id, $invoice_number, $invoice_date, $status, $company_id, $financial_year_id, $created_by, $updated_by;
    public $customer_id;
    public $items = [];
    public $isEditMode = false;
    public $invoiceId;
    public $isLinkedToOrder = false; // To distinguish between linked and unlinked invoices
    public $invoice_due_days = 0;

    public $itemsPerPage = 100;
    public $searchTerm;
    public $startDate, $endDate;

    public $discount_per_bag_rate=0;
    public $discount_per_bag_amount=0;
    public $totalDiscountPerBag = 0;
    public $totalDiscount = 0;
    public $totalSalesTax = 0;
    public $totalFurtherTax = 0;
    public $totalQuantity = 0;
    public $totalExclTax = 0;
    public $totalInclTax = 0;
    public $totalNetAmount = 0;
    public $vehicle_fare_adj = 0;

    //dev challan
    public $farm_name;
    public $farm_address;
    public $vehicle_no;
    public $vehicle_fare=0;
    public $farm_supervisor_mobile;

    public $selectedCustomer = null;
    public $brokers= [];
    public $broker_id, $broker_rate = 0, $broker_amount = 0;

    public $selectedItem, $selectedBroker, $selectedGroup;

    public $totalInvoicesu = 0;
    public $totalBagsu = 0;
    public $totalGrossAmountu = 0;
    public $totalDiscountu = 0;
    public $totalBonustu = 0;
    public $totalSalesTaxu = 0;
    public $totalFurtherSalesTaxu=0;
    public $totalWhtu=0;
    public $totalNetAmountu = 0;
    public $totalFreightu = 0;
    public $totalBrokerageu = 0;

    protected $rules = [
        'invoice_date' => 'required|date',
        'customer_id' => 'required', // Add validation for customer_id
        'vehicle_number' => 'required|string',
        'challan_number' => 'required|string',
        'items.*.product_id' => 'required',
        'items.*.quantity' => 'required|numeric|min:1',
        'items.*.unit_price' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
        'items.*.discount_rate' => 'numeric|min:0',
        'items.*.discount_amount' => 'numeric|min:0',
        'items.*.amount_excl_tax' => 'numeric|min:0',
        'items.*.sales_tax_rate' => 'numeric|min:0',
        'items.*.sales_tax_amount' => 'numeric|min:0',
        'items.*.further_tax_rate' => 'numeric|min:0',
        'items.*.further_tax_amount' => 'numeric|min:0',
        'items.*.amount_incl_tax' => 'numeric|min:0',
        'items.*.amount_incl_tax' => 'numeric|min:0',
    ];

    public function mount()
    {
        abort_if(!auth()->user()->can('sales invocies view'), 403);

        $this->startDate = Carbon::create(2024, 7, 1)->startOfDay()->format('Y-m-d'); // Start of the financial year
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');


        $this->invoice_date = Carbon::now()->format('Y-m-d');

        // Ensure at least one row is always present
        if (empty($this->items)) {
            $this->addItemRow();
        } else {
            // Calculate totals if items already exist
            $this->calculateTotals();
        }

        $this->invoice_number = $this->generateInvoiceNumber();

        // Load brokers
        $this->brokers = ChartOfAccount::where('is_customer_vendor', 'sales_broker')->get();

    }

    public function render()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session

        // Fetch brokers filtered by company_id in the render method
        $this->brokers = ChartOfAccount::where('is_customer_vendor', 'sales_broker')
                                       ->where('company_id', $companyId) // Filter by company_id
                                       ->get();

        $invoices = SalesInvoice::with(['salesOrder', 'items'])
            ->where('company_id', $companyId) // Filter invoices by company_id
            ->when($this->startDate, function ($query) {
                $query->where('invoice_date', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                $query->where('invoice_date', '<=', $this->endDate);
            })
            ->when($this->searchTerm, function ($query) {
                $query->whereHas('salesOrder', function ($query) {
                    $query->where('order_number', 'like', '%' . $this->searchTerm . '%');
                });
            })
            ->when($this->searchTerm, function ($query) {
                $query->whereHas('salesOrder', function ($query) {
                    $query->where('order_number', 'like', '%' . $this->searchTerm . '%');
                });
            })
            ->when($this->selectedCustomer, function ($query) {
                $query->where('customer_id', $this->selectedCustomer);
            })

            ->when($this->selectedItem, function ($query) {
                $query->whereHas('items', function ($query) {
                    $query->where('product_id', $this->selectedItem);
                });
            })
            ->when($this->selectedBroker, function ($query) {
                $query->where('broker_id', $this->selectedBroker);
            })
            ->when($this->selectedGroup, function ($query) {
                $query->whereHas('customer', function ($query) {
                    $query->where('group_id', $this->selectedGroup);
                });
            })
            ->orderBy('sales_invoices.id', 'desc') // Order by the most recent order_date
            ->paginate($this->itemsPerPage);

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


        // Fetch customers filtered by company_id
        $customers = ChartOfAccount::where('is_customer_vendor', 'customer')
                                   ->where('company_id', $companyId)
                                   ->orWhere('is_customer_vendor', 'vendor')
                                   ->orderBy('name')
                                   ->get();

        // Fetch brokers for filtering, filtered by company_id
        $filter_brokers = ChartOfAccount::where('is_customer_vendor', 'sales_broker')
                                        ->where('company_id', $companyId)
                                        ->get();

        // Fetch customer groups filtered by company_id
        $filter_groups = DB::table('chart_of_accounts')
            ->join('chart_of_accounts_groups', 'chart_of_accounts.group_id', '=', 'chart_of_accounts_groups.id')
            ->where('chart_of_accounts.company_id', $companyId) // Filter by company_id
            ->where('chart_of_accounts_groups.is_customer_vendor', 'customer')
            ->where('chart_of_accounts_groups.is_customer_vendor', 'vendor')
            ->select('chart_of_accounts_groups.id', 'chart_of_accounts_groups.name')
            ->distinct()
            ->orderBy('name')
            ->get();



        $this->calculateTotalsTop();

        return view('livewire.sales.invoices.sales-invoice-manager', compact('invoices', 'salesOrders', 'products', 'customers','filter_brokers','filter_groups'));
    }


    public function create($salesOrderId = null)
    {
        $this->resetFields();
        $this->resetValidation();
        $this->isEditMode = false;

        // Check if no items are present, then add at least one row
        if (empty($this->items)) {
            $this->addItemRow(); // Add an initial item row
        }

        if ($salesOrderId) {
            $this->isLinkedToOrder = true;
            $this->loadOrderDetails($salesOrderId);
        } else {
            $this->isLinkedToOrder = false;
        }

        $this->dispatch('showModal_invoice');
    }


    public function store()
    {
        try {
            // Start the database transaction
            DB::beginTransaction();

            // Define custom validation messages
            $customMessages = [
                'items.*.product_id.required' => 'Product is required.',
                'items.*.quantity.required' => 'Quantity is required.',
                'items.*.quantity.min' => 'Quantity must be at least 1.',
                'items.*.unit_price.required' => 'Unit price is required.',
                'items.*.unit_price.min' => 'The field must be at least 1.',
                'items.*.discount_rate.required' => 'Discount rate is required.',
                'items.*.discount_rate.min' => 'Discount rate must be at least 0.',
                'items.*.amount_excl_tax.required' => 'Amount excluding tax is required.',
                'items.*.sales_tax_rate.required' => 'Sales tax rate is required.',
                'items.*.sales_tax_amount.required' => 'Sales tax amount is required.',
                'items.*.further_sales_tax_rate.required' => 'Further tax rate is required.',
                'items.*.further_sales_tax_amount.required' => 'Further tax amount is required.',
                'items.*.amount_incl_tax.required' => 'Amount including tax is required.',
            ];

            // Validate the invoice inputs
            $this->validate([
                'customer_id' => 'required',
                'invoice_date' => 'required|date',
                'invoice_due_days' => 'required',
                'items.*.product_id' => 'required',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:1',
                'items.*.discount_rate' => 'required|numeric|min:0',
                'items.*.discount_amount' => 'required|numeric|min:0',
                'items.*.discount_per_bag_rate' => 'required|numeric|min:0',
                'items.*.discount_per_bag_amount' => 'required|numeric|min:0',
                'items.*.amount_excl_tax' => 'required|numeric|min:0',
                'items.*.sales_tax_rate' => 'required|numeric|min:0',
                'items.*.sales_tax_amount' => 'required|numeric|min:0',
                'items.*.further_sales_tax_rate' => 'required|numeric|min:0',
                'items.*.further_sales_tax_amount' => 'required|numeric|min:0',
                'items.*.amount_incl_tax' => 'required|numeric|min:0',
            ],$customMessages);

            // Create or update the order first
                if (!$this->isEditMode && !$this->isLinkedToOrder) {
                    // Only create a new order if we are not in edit mode and no existing order is linked
                    $salesOrder = SalesOrder::create([
                        'order_number' => $this->generateOrderNumber(), // Generate order number
                        'customer_id' => $this->customer_id,
                        'farm_name' => $this->farm_name,
                        'farm_address' => $this->farm_address,
                        'farm_supervisor_mobile' => $this->farm_supervisor_mobile,
                        'vehicle_no' => $this->vehicle_no,
                        'vehicle_fare' => $this->vehicle_fare ?? 0,
                        'order_date' => $this->invoice_date,
                        'status' => 'confirmed',
                        'company_id' => session('company_id'),
                        'order_comments' => 'Generated from Sale Invoice', // Save comments
                        'created_by' => Auth()->id(),
                        'created' => 1, // 1 for generated by sale module
                    ]);

                    // Save products in the order
                    foreach ($this->items as $item) {
                        SalesOrderItem::create([
                            'sales_order_id' => $salesOrder->id,
                            'product_id' => $item['product_id'],
                            'quantity' => $item['quantity'],
                        ]);
                    }

                    // Set the newly created order as linked to the invoice
                    $this->sales_order_id = $salesOrder->id;
                } else {
                    // If linked to an order, retrieve the existing order
                    $salesOrder = SalesOrder::find($this->sales_order_id);
                }

            // Now create or update the invoice and associate it with the sales order
            $data = [
                'sales_order_id' => $this->sales_order_id, // Use the linked or newly created order ID
                'invoice_date' => $this->invoice_date,
                'invoice_due_days' => $this->invoice_due_days,
                'invoice_number' => $this->invoice_number,
                'customer_id' => $this->customer_id,
                'broker_id' => $this->broker_id,
                'broker_rate' => $this->broker_rate,
                'broker_amount' => $this->broker_amount,
                'status' => 'posted',
                'company_id' => session('company_id'),
                'freight_credit_to' =>  $this->vehicle_fare_adj,
                'financial_year_id' => $this->financial_year_id,

            ];

            if ($this->isEditMode) {
                // Update existing invoice
                $invoice = SalesInvoice::find($this->invoiceId);

                // Use the existing `created_by` field and update the `updated_by` field
                $data['created_by'] = $invoice->created_by;
                $data['updated_by'] = Auth()->id();

                $invoice->update($data);

                // Before creating new vouchers and voucher details, delete existing ones
            $existingVoucher = Voucher::where('reference_number', $this->invoice_number)->first();
            if ($existingVoucher) {
                // Delete all voucher details related to this voucher
                VoucherDetail::where('voucher_id', $existingVoucher->id)->delete();

                // Delete the voucher itself
                $existingVoucher->delete();
            }

            } else {
                // Create new invoice
                $data['created_by'] = Auth()->id();
                // Create new invoice
                $invoice = SalesInvoice::create($data);

            }

            // Save invoice items
            foreach ($this->items as $item) {
                SalesInvoiceItem::updateOrCreate(
                    ['id' => $item['id'] ?? null],
                    [
                        'sales_invoice_id' => $invoice->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'net_amount' => round($item['quantity'] * $item['unit_price'], 2),
                        'discount_rate' => $item['discount_rate'] ?? 0,
                        'discount_amount' => round($item['discount_amount'] ?? 0, 2),
                        'discount_per_bag_rate' => $item['discount_per_bag_rate'] ?? 0,
                        'discount_per_bag_amount' => round($item['discount_per_bag_amount'] ?? 0, 2),
                        'amount_excl_tax' => round($item['amount_excl_tax'] ?? 0, 2),
                        'sales_tax_rate' => $item['sales_tax_rate'] ?? 0,
                        'sales_tax_amount' => round($item['sales_tax_amount'] ?? 0, 2),
                        'further_sales_tax_rate' => $item['further_sales_tax_rate'] ?? 0,
                        'further_sales_tax_amount' => round($item['further_sales_tax_amount'] ?? 0, 2),
                        'amount_incl_tax' => round($item['amount_incl_tax'] ?? 0, 2),
                        'created_by' => Auth()->id(),
                    ]
                );
            }

            // Now create the voucher for sales, discount, and sales tax
            $voucher = Voucher::create([
                'voucher_type' => 'sales-invoice',
                'date' => $this->invoice_date,
                'reference_number' =>  $this->invoice_number, // Generate a voucher number
                'total_amount' => collect($this->items)->sum('amount_incl_tax'),
                'description' => 'Invoice #' . $this->invoice_number,
                'status' => 1,
                'company_id' => session('company_id'),
                'created_by' => Auth()->id(),
            ]);

            // Record the sales in voucher details (credit entry for sales account)
            $salesAccount = 4; // Assuming sales account ID is 9, adjust accordingly
            $totalSales = collect($this->items)->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            VoucherDetail::create([
                'voucher_id' => $voucher->id,
                'account_id' => $salesAccount,
                'amount' => $totalSales,
                'type' => 'credit',
                'narration' => 'Sales from Invoice #' . $this->invoice_number,
                'created_by' => Auth()->id(),
            ]);

            // Record the discount (debit entry for discount account)
            $discountAccount = 3; // Assuming discount account ID is 10, adjust accordingly
            $totalDiscount = collect($this->items)->sum('discount_amount');
            if ($totalDiscount > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $discountAccount, // Discount account ID
                    'amount' => $totalDiscount,
                    'type' => 'debit',
                    'narration' => 'Discount(%) for Invoice #' . $this->invoice_number,
                    'created_by' => Auth()->id(),
                ]);
            }

            // Record the discount per bag(debit entry for discount account)
            $discountAccount = 3; // Assuming discount account ID is 2000, adjust accordingly
            $totalDiscount = collect($this->items)->sum('discount_per_bag_amount');
            if ($totalDiscount > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $discountAccount, // Discount account ID
                    'amount' => $totalDiscount,
                    'type' => 'debit',
                    'narration' => 'Discount(Per Bag) for Invoice #' . $this->invoice_number,
                    'created_by' => Auth()->id(),
                ]);
            }

            // Record the sals tax payable
            $salestaxDiscount = collect($this->items)->sum('sales_tax_amount');
            if ($salestaxDiscount > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 11, // Discount account ID
                    'amount' => $salestaxDiscount,
                    'type' => 'credit',
                    'narration' => 'Sales Tax for Invoice #' . $this->invoice_number,
                    'created_by' => Auth()->id(),
                ]);
            }

            // Update or create voucher details for brokerage if applicable
            if ($this->broker_amount > 0) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $this->broker_id, // Broker's account ID
                    'amount' => $this->broker_amount,
                    'type' => 'credit',
                    'narration' => 'Brokerage for Invoice #' . $this->invoice_number,
                    'created_by' => Auth()->id(),
                ]);

                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 9, // Broker's account ID
                    'amount' => $this->broker_amount,
                    'type' => 'debit',
                    'narration' => 'Brokerage for Invoice #' . $this->invoice_number,
                    'created_by' => Auth()->id(),
                ]);
            }

             // if fright needs to credit to cusmter

            if($this->vehicle_fare_adj == 1 && $this->vehicle_fare > 0 ) {
                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => $this->customer_id, // Accounts receivable account ID
                        'amount' => $this->vehicle_fare, // Debit the total amount including tax
                        'type' => 'credit',
                        'narration' => 'Invoice #' . $this->invoice_number,
                        'created_by' => Auth()->id(),
                    ]);

                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => 6, // Accounts
                        'amount' => $this->vehicle_fare, //Freight-Out Exp
                        'type' => 'debit',
                        'narration' => 'Invoice #' . $this->invoice_number,
                        'created_by' => Auth()->id(),
                    ]);
                } else if($this->vehicle_fare_adj == 2 && $this->vehicle_fare > 0){
                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => 5, // Accounts Freight-Out Payable
                        'amount' => $this->vehicle_fare, // Debit the total amount including tax
                        'type' => 'credit',
                        'narration' => 'Invoice #' . $this->invoice_number,
                        'created_by' => Auth()->id(),
                    ]);

                    //exp
                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => 6, // Accounts
                        'amount' => $this->vehicle_fare, // Freight-Out Exp
                        'type' => 'debit',
                        'narration' => 'Invoice #' . $this->invoice_number,
                        'created_by' => Auth()->id(),
                    ]);

                } else {


                }

            // Get the vehicle number
            $vehicleNo = $this->vehicle_no ?? 'No vehicle'; // Fallback if vehicle_no is not provided

            // Calculate total discounts
            $totalDiscount = collect($this->items)->sum('discount_amount') + collect($this->items)->sum('discount_per_bag_amount');

            // Gather product details (name and quantity)
            $productDetails = collect($this->items)->map(function ($item) {
                // Assuming you have a Product model and 'name' is a column in the products table
                $product = SalesProduct::find($item['product_id']);
                if ($product) {
                    return $product->product_name . ' (' . $item['quantity'] . ')';
                } else {
                    return 'Unknown Product (' . $item['quantity'] . ')'; // Fallback if product is not found
                }
            })->implode(', ');


            // record the customer's debit (debit entry for accounts receivable)
            $totalAmountInclTax = collect($this->items)->sum('amount_incl_tax');
            VoucherDetail::create([
                'voucher_id' => $voucher->id,
                'account_id' => $this->customer_id, // Accounts receivable account ID
                'amount' => $totalAmountInclTax, // Debit the total amount including tax
                'type' => 'debit',
                'narration' => 'Invoice #' . $this->invoice_number . ' - Vehicle: ' . $vehicleNo . ', Discount: ' . number_format($totalDiscount) . ', Products: ' . $productDetails,
                'created_by' => Auth()->id(),
            ]);

            // Commit the transaction if everything is successful
            DB::commit();

            // Dispatch the invoice modal hide event and success message
            $this->dispatch('hideModal_invoice');
            session()->flash('message', 'Sales Invoice saved successfully!');
        } catch (\Exception $e) {
            // Rollback the transaction in case of any error
            DB::rollBack();

            // Flash the error message to the session for the user
            session()->flash('formerrors', 'An error occurred while saving the invoice. Please try again.');

            // Optionally, you can rethrow the exception if needed
            throw $e;
        }
    }

    public function edit($id)
    {
        $this->resetFields(); // Reset all fields before editing
        $this->resetValidation();

        // Find the invoice to be edited
        $invoice = SalesInvoice::with(['items'])->findOrFail($id);

        // Populate the form fields with the invoice data
        $this->invoiceId = $invoice->id;
        $this->invoice_number = $invoice->invoice_number;
        $this->invoice_date = Carbon::parse($invoice->invoice_date)->format('Y-m-d');
        $this->invoice_due_days = $invoice->invoice_due_days;
        $this->customer_id = $invoice->customer_id;
        $this->sales_order_id = $invoice->sales_order_id;
        $this->status = $invoice->status;
        $this->vehicle_fare_adj = $invoice->freight_credit_to;
        $this->broker_id = $invoice->broker_id;
        $this->broker_rate = $invoice->broker_rate;
        $this->broker_amount = $invoice->broker_amount;

        // Populate delivery challan details
        $salesOrder = SalesOrder::find($this->sales_order_id);
        if ($salesOrder) {
            $this->farm_name = $salesOrder->farm_name;
            $this->farm_address = $salesOrder->farm_address;
            $this->vehicle_no = $salesOrder->vehicle_no;
            $this->vehicle_fare = $salesOrder->vehicle_fare;
            $this->farm_supervisor_mobile = $salesOrder->farm_supervisor_mobile;

        }

        // Populate the items (products) in the invoice
        $this->items = $invoice->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_rate' => $item->discount_rate,
                'discount_amount' => round($item->discount_amount, 2),
                'discount_per_bag_rate' => $item->discount_per_bag_rate,
                'discount_per_bag_amount' => round($item->discount_per_bag_amount, 2),
                'net_amount' => round($item->net_amount, 2),
                'amount_excl_tax' => round($item->amount_excl_tax, 2),
                'sales_tax_rate' => $item->sales_tax_rate,
                'sales_tax_amount' => round($item->sales_tax_amount, 2),
                'further_sales_tax_rate' => $item->further_sales_tax_rate,
                'further_sales_tax_amount' => round($item->further_sales_tax_amount, 2),
                'amount_incl_tax' => round($item->amount_incl_tax, 2),
            ];
        })->toArray();

        // Calculate totals after loading items
    $this->calculateTotals();

        // Switch to edit mode
        $this->isEditMode = true;

        // Show the modal for editing the invoice
        $this->dispatch('showModal_invoice');
    }


    public function resetFields()
    {
        $this->sales_order_id = null;
        $this->invoice_number = $this->generateInvoiceNumber();
        $this->invoice_due_days = 0;
        $this->farm_name = '';
        $this->farm_address = '';
        $this->farm_supervisor_mobile = '';
        $this->vehicle_no = '';
        $this->vehicle_fare = 0;
        $this->customer_id = null; // Reset customer_id
        $this->invoice_date = Carbon::now()->format('Y-m-d');
        $this->status = 'posted';
        $this->company_id = null;
        $this->financial_year_id = null;
        $this->items = [];
        $this->vehicle_fare_adj = 0;

        $this->totalQuantity = 0;
        $this->totalNetAmount = 0;
        $this->totalDiscount = 0;
        $this->totalDiscountPerBag = 0;
        $this->totalSalesTax = 0;
        $this->totalFurtherTax = 0;
        $this->totalExclTax = 0;
        $this->totalInclTax = 0;

    }

    public function addItemRow()
    {
        $this->items[] = [
            'product_id' => null,
            'quantity' => 0,
            'unit_price' => 0,
            'discount_rate' => 0,
            'discount_amount' => 0,
            'discount_per_bag_rate' => 0,
            'discount_per_bag_amount' => 0,
            'amount_excl_tax' => 0,
            'sales_tax_rate' => 0,
            'sales_tax_amount' => 0,
            'further_sales_tax_rate' => 0,
            'further_sales_tax_amount' => 0,
            'amount_incl_tax' => 0,
            'net_amount' => 0
        ];

        // Recalculate totals after adding a new row
        $this->calculateTotals();
    }

    public function removeItemRow($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items); // Reset array keys after removing an item

        // Recalculate totals after removing a row
        $this->calculateTotals();
    }

    public function calculateAmounts($index)
    {
        // Access the item by reference
        $item = &$this->items[$index];

        // Convert string inputs to floats and round to two decimal places
        $quantity = round(floatval($item['quantity'] ?? 0), 2);
        $unit_price = round(floatval($item['unit_price'] ?? 0), 2); // Ensure unit price is rounded to two decimal places
        $discount_rate = round(floatval($item['discount_rate'] ?? 0), 2);
        $discount_rate_per_bag = round(floatval($item['discount_per_bag_rate'] ?? 0), 2);
        $sales_tax_rate = round(floatval($item['sales_tax_rate'] ?? 0), 2);
        $further_tax_rate = round(floatval($item['further_sales_tax_rate'] ?? 0), 2);

        // Calculate net amount and round
        $net_amount = round($unit_price * $quantity, 2);
        $item['net_amount'] = $net_amount;

        // Calculate discount amount using the rounded discount_rate and round the result
        $discount_amount = round(($unit_price * $discount_rate / 100) * $quantity, 2);
        $item['discount_amount'] = $discount_amount;

        // Calculate discount per bag amount and round
        $discount_per_bag_amount = round($discount_rate_per_bag * $quantity, 2);
        $item['discount_per_bag_amount'] = $discount_per_bag_amount;

        // Calculate total discount and round
        $total_discount = round($discount_amount + $discount_per_bag_amount, 2);

        // Calculate amount excluding tax and round
        $amount_excl_tax = round(($quantity * $unit_price) - $total_discount, 2);
        $item['amount_excl_tax'] = $amount_excl_tax;

        // Calculate sales tax amount for feed mill and round
        $sales_tax_amount = round($sales_tax_rate * $quantity, 2);
        $item['sales_tax_amount'] = $sales_tax_amount;

        // Calculate further tax amount and round
        $further_tax_amount = round(($amount_excl_tax * $further_tax_rate / 100), 2);
        $item['further_sales_tax_amount'] = $further_tax_amount;

        // Calculate total amount including tax and round
        $item['amount_incl_tax'] = round($amount_excl_tax + $sales_tax_amount, 2);

        // Calculate the totals after every change
        $this->calculateTotals();
    }






    public function calculateTotals()
    {
        $this->totalQuantity = 0;
        $this->totalNetAmount = 0;
        $this->totalDiscount = 0;
        $this->totalDiscountPerBag = 0;
        $this->totalSalesTax = 0;
        $this->totalFurtherTax = 0;
        $this->totalExclTax = 0;
        $this->totalInclTax = 0;

        foreach ($this->items as $item) {
            // Ensure all values are cast to numbers before performing operations
            $this->totalQuantity += round(floatval($item['quantity'] ?? 0), 2);
            $this->totalNetAmount += round(floatval($item['net_amount'] ?? 0), 2);
            $this->totalDiscount += round(floatval($item['discount_amount'] ?? 0), 2);
            $this->totalDiscountPerBag += round(floatval($item['discount_per_bag_amount'] ?? 0), 2);
            $this->totalSalesTax += round(floatval($item['sales_tax_amount'] ?? 0), 2);
            $this->totalFurtherTax += round(floatval($item['further_sales_tax_amount'] ?? 0), 2);
            $this->totalExclTax += round(floatval($item['amount_excl_tax'] ?? 0), 2);
            $this->totalInclTax += round(floatval($item['amount_incl_tax'] ?? 0), 2);
        }
    }


    public function calculateTotalsTop()
    {


        $companyId = session('company_id'); // Retrieve the company_id from the session

        $this->invoices = SalesInvoice::query()
        ->where('company_id', $companyId)
        ->when($this->startDate, fn($query) => $query->where('invoice_date', '>=', $this->startDate))
        ->when($this->endDate, fn($query) => $query->where('invoice_date', '<=', $this->endDate))
        ->when($this->selectedCustomer, fn($query) => $query->where('customer_id', $this->selectedCustomer))
        ->when($this->selectedItem, fn($query) => $query->whereHas('items', fn($q) => $q->where('product_id', $this->selectedItem)))
        ->get();

        $this->totalInvoicesu = $this->invoices->count();
        $this->totalBagsu = $this->invoices->sum(fn($invoice) => $invoice->items->sum('quantity'));
        $this->totalGrossAmountu = $this->invoices->sum(fn($invoice) => $invoice->items->sum('amount_excl_tax'));
        $this->totalDiscountu = $this->invoices->sum(fn($invoice) => $invoice->items->sum('discount_amount'));
        $this->totalBonustu = $this->invoices->sum(fn($invoice) => $invoice->items->sum('discount_per_bag_amount'));
        $this->totalSalesTaxu = $this->invoices->sum(fn($invoice) => $invoice->items->sum('sales_tax_amount'));
        $this->totalFurtherSalesTaxu = $this->invoices->sum(fn($invoice) => $invoice->items->sum('further_sales_tax_amount'));
        $this->totalWhtu = $this->invoices->sum(fn($invoice) => $invoice->items->sum('advance_wht_amount'));
        $this->totalNetAmountu = $this->invoices->sum(fn($invoice) => $invoice->items->sum('amount_incl_tax'));
        $this->totalFreightu = $this->invoices->sum('freight');
        $this->totalBrokerageu = $this->invoices->sum('broker_amount');




    }




    private function generateOrderNumber()
    {

        $company = Company::where('id', session('company_id'))->first(); // Fetch the company record
        $companyAbbreviation = $company ? $company->abv : 'SO'; // Default to 'ORD' if not found

        $currentDate = now();

        // Determine the start of the financial year based on your July start
        $financialYearStart = $currentDate->month >= 7
            ? $currentDate->copy()->month(7)->startOfMonth()
            : $currentDate->copy()->subYear()->month(7)->startOfMonth();

        // Format the current month and year
        $monthYear = $currentDate->format('m') . substr($currentDate->format('Y'), -2);

        // Count the number of vouchers in the current financial year
        $voucherCount = SalesOrder::where('order_date', '>=', $financialYearStart)->count();
        $nextVoucherNumber = $voucherCount + 1;

        do {
            // Format the next voucher number with leading zeros
            $formattedNumber = str_pad($nextVoucherNumber, 3, '0', STR_PAD_LEFT);
            $voucherNumber = $companyAbbreviation . '-SO' . $monthYear . '-' . $formattedNumber;

            // Check if the voucher number already exists
            $voucherExists = SalesOrder::where('order_number', $voucherNumber)->exists();

            if ($voucherExists) {
                $nextVoucherNumber++;
            }
        } while ($voucherExists);

        return $voucherNumber;
    }

    private function generateInvoiceNumber()
    {
        $company = Company::where('id', session('company_id'))->first(); // Fetch the company record
        $companyAbbreviation = $company ? $company->abv : 'INV'; // Default to 'ORD' if not found

        $currentDate = now();

        // Determine the start of the financial year based on your July start
        $financialYearStart = $currentDate->month >= 7
            ? $currentDate->copy()->month(7)->startOfMonth()
            : $currentDate->copy()->subYear()->month(7)->startOfMonth();

        // Format the current month and year
        $monthYear = $currentDate->format('m') . substr($currentDate->format('Y'), -2);

        // Count the number of vouchers in the current financial year
        $voucherCount = SalesInvoice::where('invoice_date', '>=', $financialYearStart)->count();
        $nextVoucherNumber = $voucherCount + 1;

        do {
            // Format the next voucher number with leading zeros
            $formattedNumber = str_pad($nextVoucherNumber, 3, '0', STR_PAD_LEFT);
            $voucherNumber = $companyAbbreviation . '-INV'  . '-' . $formattedNumber;

            // Check if the voucher number already exists
            $voucherExists = SalesInvoice::where('invoice_number', $voucherNumber)->exists();

            if ($voucherExists) {
                $nextVoucherNumber++;
            }
        } while ($voucherExists);

        return $voucherNumber;
    }

    public function updated($propertyName)
    {

        if ($propertyName === 'customer_id') {
            // Fetch payment_term when the customer_id changes
            $customerDetail = \App\Models\CustomerDetail::where('account_id', $this->customer_id)->first();
            if ($customerDetail) {
                $this->invoice_due_days = $customerDetail->payment_terms;
            }
        }

        // This will validate the specific property being updated (real-time validation)
        $this->validateOnly($propertyName, [
            'customer_id' => 'required',
            'invoice_date' => 'required|date',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'items.*.discount_rate' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'required|numeric|min:0',
            'items.*.discount_rate_bag' => 'required|numeric|min:0',
            'items.*.discount_per_bag_amount' => 'required|numeric|min:0',
            'items.*.amount_excl_tax' => 'required|numeric|min:0',
            'items.*.sales_tax_rate' => 'required|numeric|min:0',
            'items.*.sales_tax_amount' => 'required|numeric|min:0',
            'items.*.further_sales_tax_rate' => 'required|numeric|min:0',
            'items.*.further_sales_tax_amount' => 'required|numeric|min:0',
            'items.*.amount_incl_tax' => 'required|numeric|min:0',
        ], $this->customMessages());
    }
    protected function customMessages()
    {
        return [
            'items.*.product_id.required' => 'Product is required.',
            'items.*.quantity.required' => 'Quantity is required.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.unit_price.required' => 'Unit price is required.',
            'items.*.unit_price.min' => 'The field must be at least 1.',
            'items.*.discount_rate.required' => 'Discount rate is required.',
            'items.*.discount_rate.min' => 'Discount rate must be at least 0.',
            'items.*.amount_excl_tax.required' => 'Amount excluding tax is required.',
            'items.*.sales_tax_rate.required' => 'Sales tax rate is required.',
            'items.*.sales_tax_amount.required' => 'Sales tax amount is required.',
            'items.*.further_sales_tax_rate.required' => 'Further sales tax rate is required.',
            'items.*.further_sales_tax_amount.required' => 'Further sales tax amount is required.',
            'items.*.amount_incl_tax.required' => 'Amount including tax is required.',
        ];


    }

    private function generateVoucherNumber()
    {
        $currentDate = now();

        // Determine the start of the financial year based on your July start
        $financialYearStart = $currentDate->month >= 7
            ? $currentDate->copy()->month(7)->startOfMonth()
            : $currentDate->copy()->subYear()->month(7)->startOfMonth();

        // Format the current month and year
        $monthYear = $currentDate->format('m') . substr($currentDate->format('Y'), -2);

        // Count the number of vouchers in the current financial year
        $voucherCount = Voucher::where('date', '>=', $financialYearStart)->count();
        $nextVoucherNumber = $voucherCount + 1;

        do {
            // Format the next voucher number with leading zeros
            $formattedNumber = str_pad($nextVoucherNumber, 3, '0', STR_PAD_LEFT);
            $voucherNumber = 'JV' . $monthYear . '-' . $formattedNumber;

            // Check if the voucher number already exists
            $voucherExists = Voucher::where('reference_number', $voucherNumber)->exists();

            if ($voucherExists) {
                $nextVoucherNumber++;
            }
        } while ($voucherExists);

        return $voucherNumber;
    }

    public function confirmDeletion($id)
    {

        $this->dispatch('swal:confirm-deletion', voucherId: $id);

    }

    public function delete($id)
    {
        // Find the SalesInvoice by ID
        $invoice = SalesInvoice::findOrFail($id);

        // Check if the invoice has associated voucher details
        $voucher = Voucher::where('reference_number', $invoice->invoice_number )->first(); // Assuming the invoice is linked with a voucher by invoice_id

        if ($voucher) {
            // Delete all VoucherDetails associated with the voucher
            VoucherDetail::where('voucher_id', $voucher->id)->delete();

            // Delete the Voucher itself
            $voucher->delete();
        }

        // Delete all SalesInvoiceItems associated with the invoice
        SalesInvoiceItem::where('sales_invoice_id', $id)->delete();

        // Delete the invoice itself
        $invoice->delete();

        // Set a session flash message for success
        session()->flash('message', 'Invoice and associated records deleted successfully.');
    }

}
