<?php

namespace App\Livewire\Purchase\Bills;

use Livewire\Component;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\PurchaseOrder;
use App\Models\ChartOfAccount;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseItem;
use App\Models\Company;
use App\Models\Items;
use App\Models\ItemGroup;


class PurchaseBillManager extends Component
{
    use WithPagination;

    public $isEditMode = false;
    public $billId;
    public $bill_number;
    public $vendor_id;
    public $order_id;
    public $bill_date;
    public $bill_due_days = 0;
    public $total_amount = 0;
    public $status = 'init';
    public $company_id = 1;
    public $itemsPerPage = 50;
    public $searchTerm = '';
    public $comments = '';
    public $purchaseOrders = [];
    public $items = [];
    public $allProducts = [];
    public $brokers = [];
    public $delivery_mode;
    public $vehicle_no;
    public $freight=0;


    public $broker_id, $broker_rate = 0, $broker_amount = 0;

    //filter
    public $filer_vendor_id;
    public $start_date;
    public $end_date;
    public $filter_items;



    protected $rules = [
        'vehicle_no' => 'required',
        'vendor_id' => 'required',
        'order_id' => 'required',
        'bill_date' => 'required|date',
        'status' => 'required|string',
        'items.*.product_id' => 'required|integer',
        'items.*.quantity' => 'required|numeric|min:1',
        'items.*.net_quantity' => 'required|numeric|min:1',
        'items.*.price' => 'required|numeric|min:0',
        'items.*.gross_amount' => 'required|numeric|min:0',
        'items.*.net_amount' => 'required|numeric|min:0',
        'delivery_mode' => 'required',

    ];

    public function mount()
    {
        abort_if(!auth()->user()->can('purchases bills view'), 403);

        $companyId = session('company_id');

        $this->bill_date = Carbon::now()->format('Y-m-d'); // Default to today's date
        $this->allProducts = Items::where('item_type', 'purchase')
        ->orderBy('name')
        ->get(); // Fetch all products with company_id = 1

        $this->brokers = ChartOfAccount::where('is_customer_vendor', 'purchase_broker')
        ->where('company_id', $companyId) // Add the company_id condition
        ->get(); //

        // Set start_date to the first day of the current month
        $this->start_date = Carbon::now()->startOfMonth()->format('Y-m-d');

        // Set end_date to today's date
        $this->end_date = Carbon::now()->endOfMonth()->format('Y-m-d');

        $this->bill_number = $this->generateBillNumber();

    }

    public function updatedVendorId()
    {
        // Fetch purchase orders related to the selected vendor
        if ($this->vendor_id) {
            $this->purchaseOrders = PurchaseOrder::where('vendor_id', $this->vendor_id)->get();
        } else {
            $this->purchaseOrders = [];
        }

        $this->order_id = null; // Reset the order dropdown
        $this->items = []; // Clear the items if vendor changes
    }

    public function updatedOrderId()
    {
        // Fetch order details and populate items based on selected order
        if ($this->order_id) {
            $order = PurchaseOrder::with('items')->findOrFail($this->order_id);

            // Set the delivery_mode and broker_id from the order details
            $this->delivery_mode = $order->delivery_mode;
            $this->broker_id = $order->broker_id;


            $this->items = [];



            foreach ($order->items as $item) {
                $this->items[] = [
                    'product_id' => $item->product_id,
                    'quantity' => 0,
                    'deduction' => 0,
                    'net_quantity' => 0,
                    'price' => $item->price,
                    'gross_amount' => 0,
                    'sales_tax_rate' => 0,
                    'sales_tax_amount' => 0,
                    'net_amount' => 0
                ];
            }
        }
    }

    public function create()
    {
        $this->resetInputFields();
        $this->resetValidation();
        $this->isEditMode = false;

        //$this->addItem(); // Add an initial product row
        $this->dispatch('showModal_bill');
    }

    public function store()
    {
        try {
            // Start the database transaction
            DB::beginTransaction();

            // Validate the purchase bill inputs
            $this->validate([
                'vendor_id' => 'required', // Ensure vendor exists
                'bill_date' => 'required|date',
                'bill_due_days' => 'required',
                'vehicle_no' => 'nullable|string',
                'order_id' => 'required|exists:purchase_orders,id', // Ensure order exists
                'delivery_mode' => 'nullable|string',
                'freight' => 'required|numeric|min:0',
                'broker_id' => 'nullable', // Ensure broker exists
                'broker_rate' => 'nullable|numeric|min:0',
                'broker_amount' => 'nullable|numeric|min:0',
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
                'vendor_id' => $this->vendor_id,
                'order_id' => $this->order_id,
                'bill_date' => $this->bill_date,
                'bill_due_days' => $this->bill_due_days,
                'vehicle_no' => $this->vehicle_no,
                'freight' => $this->freight,
                'broker_id' => $this->broker_id,
                'broker_rate' => $this->broker_rate,
                'broker_amount' => $this->broker_amount,
                'delivery_mode' => $this->delivery_mode,
                'status' => 'posted',
                'company_id' => session('company_id'),
                'financial_year_id' => 1, // You may set this dynamically as needed
                'comments' => $this->comments,
            ];

            //
            $new_bill_number= $this->generateBillNumber();

            if ($this->isEditMode) {
                // Update existing bill
                $purchaseBill = PurchaseBill::findOrFail($this->billId);
                // Use the existing `created_by` field and update the `updated_by` field
                $billData['created_by'] = $purchaseBill->created_by;
                $billData['updated_by'] = Auth()->id();

                $purchaseBill->update($billData);

                $this->bill_number =$purchaseBill->bill_number;

                // Delete old bill items before adding new ones
                PurchaseBillItem::where('purchase_bill_id', $purchaseBill->id)->delete();

                // Remove old voucher details before creating new ones
                $voucher = Voucher::where('reference_number', $this->bill_number)->first();

                if ($voucher) {
                    VoucherDetail::where('voucher_id', $voucher->id)->delete();
                } else {
                    // If no voucher exists, create a new one
                    $voucher = Voucher::create([
                        'voucher_type' => 'purchase-bill',
                        'date' => $this->bill_date,
                        'reference_number' => $this->bill_number,
                        'total_amount' => collect($this->items)->sum('net_amount'),
                        'description' => 'Purchase Bill #' . $this->bill_number,
                        'status' => 1,
                        'company_id' => session('company_id'),
                        'created_by' => Auth()->id(),
                    ]);
                }
            } else {
                // Create a new purchase bill
                // Create new invoice
                $billData['created_by'] = Auth()->id();
                $billData['bill_number'] = $this->generateBillNumber(); // Generate unique bill number
                $purchaseBill = PurchaseBill::create($billData);

                // Create a new voucher
                $voucher = Voucher::create([
                    'voucher_type' => 'purchase-bill',
                    'date' => $this->bill_date,
                    'reference_number' => $new_bill_number,
                    'total_amount' => collect($this->items)->sum('net_amount'),
                    'description' => 'Purchase Bill #' . $new_bill_number,
                    'status' => 1,
                    'company_id' => session('company_id'),
                    'created_by' => Auth()->id(),
                ]);
            }

            // Save purchase bill items
            foreach ($this->items as $item) {
                PurchaseBillItem::create([
                    'purchase_bill_id' => $purchaseBill->id,
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
                $totalNetAmount = collect($this->items)->sum('net_amount');
                $totalSalesTax = collect($this->items)->sum(function ($item) {
                    return $item['sales_tax_amount'] ?? 0;
                });

                // Create voucher details for purchases and account payable
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $this->vendor_id, // Vendor's account
                    'amount' => collect($this->items)->sum('net_amount'),
                    'type' => 'credit',
                    'narration' => 'Purchases for Bill #' . $new_bill_number,
                    'created_by' => Auth()->id(),
                ]);

                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 12, // Debit account for purchases
                    'amount' => $totalNetAmount - $totalSalesTax,
                    'type' => 'debit',
                    'narration' => 'Purchases for Bill #' . $new_bill_number,
                    'created_by' => Auth()->id(),
                ]);

                if($totalSalesTax > 0 ) {
                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => 11, // Sales Tax Payable account ID (update to the correct account ID)
                        'amount' => $totalSalesTax,
                        'type' => 'debit',
                        'narration' => 'Sales Tax Payable for Bill #' . $new_bill_number,
                        'created_by' => Auth()->id(),
                    ]);
                }


                // Handle broker accounting if broker exists
                if ($this->broker_id && $this->broker_id !== 'self') {
                    // For a specific broker
                    $brokerAmount = $this->broker_amount;
                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => $this->broker_id, // Broker's account
                        'amount' => $brokerAmount,
                        'type' => 'credit',
                        'narration' => 'Brokerage for Bill #' . $new_bill_number,
                        'created_by' => Auth()->id(),
                    ]);
                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => 10, // Purchase Brokers Expense account ID
                        'amount' => $brokerAmount,
                        'type' => 'debit',
                        'narration' => 'Brokerage for Bill #' . $new_bill_number,
                        'created_by' => Auth()->id(),
                    ]);
                } elseif ($this->broker_id === 'self' && $this->vendor_id) {
                    // Handle case for "self" broker, where the account_id should be vendor_id
                    $brokerAmount = $this->broker_amount;
                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => $this->vendor_id, // Vendor's account in case of "self" broker
                        'amount' => $brokerAmount,
                        'type' => 'credit',
                        'narration' => 'Brokerage for Bill #' . $new_bill_number,
                        'created_by' => Auth()->id(),
                    ]);
                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => 10, // Purchase Brokers Expense account ID
                        'amount' => $brokerAmount,
                        'type' => 'debit',
                        'narration' => 'Brokerage for Bill #' . $new_bill_number,
                        'created_by' => Auth()->id(),
                    ]);
                } else {

                }


                // Handle freight and delivery mode accounting
                if ($this->delivery_mode == 'ex-mill' && $this->freight > 0) {
                    // Freight-In Expense
                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => 8, // Freight-In Expense account ID
                        'amount' => $this->freight,
                        'type' => 'debit',
                        'narration' => 'Freight-In for Bill #' . $new_bill_number,
                        'created_by' => Auth()->id(),
                    ]);
                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => 7, // Freight-In Payable account ID
                        'amount' => $this->freight,
                        'type' => 'credit',
                        'narration' => 'Freight-In for Bill #' . $new_bill_number,
                        'created_by' => Auth()->id(),
                    ]);
                } elseif ($this->delivery_mode == 'delivered' && $this->freight > 0) {
                    // Freight-In Payable
                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => 7, // Freight-In Payable account ID
                        'amount' => $this->freight,
                        'type' => 'credit',
                        'narration' => 'Freight-In for Bill #' . $new_bill_number,
                        'created_by' => Auth()->id(),
                    ]);
                    // Vendor account
                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => $this->vendor_id, // Vendor's account ID
                        'amount' => $this->freight,
                        'type' => 'debit',
                        'narration' => 'Freight-In for Bill #' . $new_bill_number,
                        'created_by' => Auth()->id(),
                    ]);
                }
            }

            // Commit the transaction
            DB::commit();

            // Flash success message
            session()->flash('message', 'Purchase Bill and Voucher saved successfully!');

            // Hide modal (if applicable)
            $this->dispatch('hideModal_bill');
        } catch (\Exception $e) {
            // Rollback the transaction in case of any error
            DB::rollBack();

            // Flash error message to the session
            session()->flash('formerrors', 'An error occurred while saving the bill. Please try again.');

            // Optionally, throw the exception to log it
            throw $e;
        }
    }



    public function addItem()
    {
        $this->items[] = [
                'product_id' => '',
                'quantity' => 0,
                'deduction' => 0,
                'net_quantity' => 0,
                'price' => 0,
                'gross_amount' => 0,
                'sales_tax_rate' => 0,
                'sales_tax_amount' => 0,
                'net_amount' => 0
                ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items); // Reindex array after removal
    }

    private function resetInputFields()
    {
        $this->vendor_id = '';
        $this->order_id = '';
        $this->bill_date = Carbon::now()->format('Y-m-d');
        $this->bill_due_days = 0;
        $this->total_amount = 0;
        $this->status = 'init';
        $this->comments = '';
        $this->purchaseOrders = [];
        $this->items = [];
        $this->delivery_mode = '';
        $this->broker_id ='';
        $this->broker_rate =0;
        $this->broker_amount =0;
        $this->vehicle_no ='';
        $this->bill_number ='';
        $this->freight = 0;


    }

    public function render()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session
        $searchTerm = '%' . $this->searchTerm . '%';

    // Ensure filter_items is always an array
        $filterItems = $this->filter_items;

        // Build query with filters
        $query = PurchaseBill::with(['vendor', 'order', 'items.product']);

        // Apply start_date and end_date filters if provided
        if ($this->start_date) {
            $query->where('bill_date', '>=', Carbon::parse($this->start_date));
        }
        if ($this->end_date) {
            $query->where('bill_date', '<=', Carbon::parse($this->end_date));
        }

        // Apply vendor filter
        if ($this->filer_vendor_id) {
            $query->where('vendor_id', $this->filer_vendor_id);
        }

        // Apply item filter (only if an item is selected)
        if ($this->filter_items) {
            $query->whereHas('items', function ($query) use ($filterItems, $companyId) {
                // Use where to filter by product_id (single item)
                $query->where('product_id', $filterItems);
                     // Filter by company_id for items
            });
        }

        // Fetch the filtered purchase bills with pagination
        $purchaseBills = $query->orderBy('purchase_bills.id', 'desc')
                            ->paginate($this->itemsPerPage);

        // Fetch vendors filtered by company_id
        $vendors = ChartOfAccount::where('is_customer_vendor', 'vendor')
                                    ->orderBy('name')
                                    ->get();

        // Fetch products filtered by company_id
        $allProducts = Items::where('item_type', 'purchase')
        ->orderBy('name')
        ->get();

        return view('livewire.purchase.bills.purchase-bill-manager', [
            'purchaseBills' => $purchaseBills,
            'vendors' => $vendors,
            'allProducts' => $allProducts,
        ]);
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
        $billCount = PurchaseBill::where('bill_date', '>=', $financialYearStart)
            ->where('bill_date', '<', $financialYearStart->copy()->addYear()) // Ensure it's within the fiscal year
            ->count();

        // Start numbering from 1 for the new fiscal year
        $nextBillNumber = $billCount + 1;

        do {
            // Format the next bill number with leading zeros
            $formattedNumber = str_pad($nextBillNumber, 3, '0', STR_PAD_LEFT);
            // Generate the bill number in the format: [CompanyAbbreviation]-[FiscalYearLast2Digits]-[FormattedNumber]
            $billNumber = $companyAbbreviation . '-PB'  . $currentYear . '-' . $formattedNumber;

            // Check if the bill number already exists
            $billExists = PurchaseBill::where('bill_number', $billNumber)->exists();

            if ($billExists) {
                // If the number exists, increment and retry
                $nextBillNumber++;
            }
        } while ($billExists);

        return $billNumber;
    }



    public function edit($id)
    {
        $purchaseBill = PurchaseBill::with('items')->findOrFail($id);

        // Set the vendor_id to ensure purchase orders are fetched
        $this->vendor_id = $purchaseBill->vendor_id;

        // Fetch purchase orders related to the selected vendor
        $this->purchaseOrders = PurchaseOrder::where('vendor_id', $this->vendor_id)->get();

        // Set the component properties to the existing bill's values
        $this->billId = $purchaseBill->id;
        $this->order_id = $purchaseBill->order_id;
        $this->bill_date = Carbon::parse($purchaseBill->bill_date)->format('Y-m-d');
        $this->bill_due_days = $purchaseBill->bill_due_days;
        $this->broker_id = $purchaseBill->broker_id;
        $this->broker_rate = $purchaseBill->broker_rate;
        $this->broker_amount = $purchaseBill->broker_amount;
        $this->delivery_mode = $purchaseBill->delivery_mode;
        $this->vehicle_no = $purchaseBill->vehicle_no;
        $this->freight = $purchaseBill->freight;
        $this->status = $purchaseBill->status;
        $this->comments = $purchaseBill->comments;

        // Load the items into the items array for editing
        $this->items = $purchaseBill->items->map(function ($item) {
            return [
                'id' => $item->id, // Include the ID to avoid duplication
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'deduction' => $item->deduction,
                'net_quantity' => $item->net_quantity,
                'price' => $item->price,
                'gross_amount' => $item->gross_amount,
                'sales_tax_rate' => $item->sales_tax_rate,
                'sales_tax_amount' => $item->sales_tax_amount,
                'withholding_tax_rate' => $item->withholding_tax_rate,
                'withholding_tax_amount' => $item->withholding_tax_amount,
                'net_amount' => $item->net_amount,
            ];
        })->toArray();

        $this->isEditMode = true; // Set edit mode to true

        // Show the modal for editing
        $this->dispatch('showModal_bill');
    }


    public function calculateAmounts($index)
    {
        // Ensure values are numeric by setting default to 0 if empty
        $quantity = !empty($this->items[$index]['quantity']) ? (float) $this->items[$index]['quantity'] : 0;
        $price = !empty($this->items[$index]['price']) ? (float) $this->items[$index]['price'] : 0;
        $deduction = !empty($this->items[$index]['deduction']) ? (float) $this->items[$index]['deduction'] : 0;

        $sales_tax_rate = !empty($this->items[$index]['sales_tax_rate']) ? (float) $this->items[$index]['sales_tax_rate'] : 0;
        $withholding_tax_rate = !empty($this->items[$index]['withholding_tax_rate']) ? (float) $this->items[$index]['withholding_tax_rate'] : 0;

        $net_quantity = max(0, $quantity - $deduction);
        $gross_amount = $net_quantity * $price;

        // Calculate sales tax and withholding tax amounts
        $sales_tax_amount = ($gross_amount * $sales_tax_rate) / 100;
        $withholding_tax_amount = ($gross_amount * $withholding_tax_rate) / 100;

        // Update values in the array
        $this->items[$index]['net_quantity'] = $net_quantity;
        $this->items[$index]['gross_amount'] = $gross_amount;
        $this->items[$index]['sales_tax_rate'] = $sales_tax_rate;
        $this->items[$index]['sales_tax_amount'] = $sales_tax_amount;
        $this->items[$index]['withholding_tax_rate'] = $withholding_tax_rate;
        $this->items[$index]['withholding_tax_amount'] = $withholding_tax_amount;

        // Calculate net amount (after taxes)
        $net_amount = $gross_amount + $sales_tax_amount - $withholding_tax_amount;
        $this->items[$index]['net_amount'] = $net_amount;
    }


    public function confirmDeletionBill($id)
    {
        $this->dispatch('swal:confirm-deletion', voucherId: $id);
    }

    public function deletePurchaseBill($id)
    {
        DB::transaction(function () use ($id) {
            // Find the purchase bill
            $purchaseBill = PurchaseBill::findOrFail($id);

            // Check if the purchase bill has related vouchers by querying the `vouchers` table
            $voucher = Voucher::where('reference_number', $purchaseBill->bill_number)->first();


            if ($voucher) {
                // Delete related voucher details
                VoucherDetail::where('voucher_id', $voucher->id)->delete();

                // Delete the voucher
                $voucher->delete();
            }

            // Delete the purchase bill items
            PurchaseBillItem::where('purchase_bill_id', $purchaseBill->id)->delete();

            // Delete the purchase bill
            $purchaseBill->delete();

            // Flash a success message
            session()->flash('message', 'Purchase Bill and related entries deleted successfully.');
        }, 5); // Retry transaction 5 times in case of deadlock
    }







}
