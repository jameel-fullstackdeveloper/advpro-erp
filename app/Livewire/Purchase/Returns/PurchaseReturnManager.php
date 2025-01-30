<?php

namespace App\Livewire\Purchase\Returns;

use Livewire\Component;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Items;
use App\Models\SalesInvoice;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\Company;
use App\Models\ChartOfAccount; // Import ChartOfAccount for customers
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PurchaseReturnManager extends Component
{
    use WithPagination;

    public $sales_order_id, $sales_invoice_id, $return_number, $return_date, $status, $company_id, $financial_year_id, $created_by, $updated_by;
    public $vendor_id;
    public $items = [];
    public $isEditMode = false;
    public $returnId;
    public $isLinkedToInvoice = false; // To distinguish between linked and unlinked returns

    public $itemsPerPage = 50;
    public $searchTerm;
    public $startDate, $endDate;

    public $totalReturnedQuantity = 0;
    public $totalReturnAmount = 0;
    public $vehicle_fare_adj = 0;

    // Dev challan
    public $farm_name;
    public $farm_address;
    public $vehicle_no;
    public $vehicle_fare = 0;
    public $farm_supervisor_mobile;

    public $selectedCustomer = null;

    protected $rules = [
        'return_date' => 'required|date',
        'vendor_id' => 'required', // Add validation for vendor_id
        'vehicle_number' => 'required|string',
        'challan_number' => 'required|string',
        'items.*.product_id' => 'required',
        'items.*.return_quantity' => 'required|numeric|min:1', // Adjust to capture return quantity
        'items.*.unit_price' => 'required|numeric|min:0',
        'items.*.return_amount' => 'numeric|min:0',
    ];

    public function mount()
    {
        abort_if(!auth()->user()->can('purchases return view'), 403);

        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');

        $this->return_date = Carbon::now()->format('Y-m-d');

        // Ensure at least one row is always present
        if (empty($this->items)) {
            $this->addItemRow();
        } else {
            // Calculate totals if items already exist
            $this->calculateTotals();
        }

        $this->return_number = $this->generateReturnNumber();
    }

    public function render()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session

        // Fetch sales returns filtered by company_id
        $returns = PurchaseReturn::with(['items']) // Only eager-load the items, not the sales invoice
            ->where('company_id', $companyId) // Filter by company_id
            ->when($this->startDate, function ($query) {
                $query->where('return_date', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                $query->where('return_date', '<=', $this->endDate);
            })
            ->when($this->searchTerm, function ($query) {
                $query->where('return_number', 'like', '%' . $this->searchTerm . '%');
            })
            ->when($this->selectedCustomer, function ($query) {
                $query->where('vendor_id', $this->selectedCustomer);
            })
            ->paginate($this->itemsPerPage);

        // Fetch products filtered by company_id
        $products = Items::where('item_type', 'purchase')
        ->orderBy('name', 'asc')
        ->get();

        // Fetch customers filtered by company_id
        $customers = ChartOfAccount::where('is_customer_vendor', 'vendor')
                                   ->where('company_id', $companyId)
                                   ->orWhere('is_customer_vendor', 'customer')
                                   ->get();

        return view('livewire.purchase.returns.purchase-return-manager', compact('returns', 'products', 'customers'));
    }


    public function create($invoiceId = null)
    {
        $this->resetFields();
        $this->resetValidation();
        $this->isEditMode = false;

        // Check if no items are present, then add at least one row
        if (empty($this->items)) {
            $this->addItemRow(); // Add an initial item row
        }

        if ($invoiceId) {
            $this->isLinkedToInvoice = true;
            $this->loadInvoiceDetails($invoiceId);
        } else {
            $this->isLinkedToInvoice = false;
        }

        $this->dispatch('showModal_return');
    }

    public function store()
    {
        try {
            // Start the database transaction
            DB::beginTransaction();

            // Validate the return inputs
            $this->validate([
                'vendor_id' => 'required',
                'return_date' => 'required|date',
                'items.*.product_id' => 'required',
                'items.*.return_quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:1',
                'items.*.return_amount' => 'required|numeric|min:0',
            ]);

            // Create or update the sales return
            $data = [
                'return_date' => $this->return_date,
                'return_number' => $this->return_number,
                'vendor_id' => $this->vendor_id,
                'status' => 'posted',
                'company_id' => session('company_id'),
            ];

            if ($this->isEditMode) {
                // Update existing return
                $return = PurchaseReturn::find($this->returnId);
                // Use the existing `created_by` field and update the `updated_by` field
                $data['created_by'] = $return->created_by;
                $data['updated_by'] = Auth()->id();

                $return->update($data);

                // Delete existing voucher and voucher details
                $voucher = Voucher::where('reference_number', $this->return_number)->first();
                if ($voucher) {
                    // Delete associated voucher details
                    VoucherDetail::where('voucher_id', $voucher->id)->delete();

                    // Delete the voucher itself
                    $voucher->delete();
                }
            } else {
                // Create new return
                $data['created_by'] = Auth()->id();
                $return = PurchaseReturn::create($data);
            }

            // Save return items
            foreach ($this->items as $item) {
                PurchaseReturnItem::updateOrCreate(
                    ['id' => $item['id'] ?? null],
                    [
                        'purchase_return_id' => $return->id,
                        'product_id' => $item['product_id'],
                        'return_quantity' => $item['return_quantity'],
                        'unit_price' => $item['unit_price'],
                        'return_amount' => $item['return_amount'],
                        'created_by' => Auth()->id(),
                    ]
                );
            }

            // Calculate the total return amount
            $totalReturnAmount = collect($this->items)->sum('return_amount');

             // Gather product details (name and return quantity)
            $productDetails = collect($this->items)->map(function ($item) {
                // Assuming you have a Product model and 'name' is a column in the products table
                $product = Items::find($item['product_id']);
                if ($product) {
                    return $product->product_name . ' (' . $item['return_quantity'] . ')';
                } else {
                    return 'Unknown Product (' . $item['return_quantity'] . ')'; // Fallback if product is not found
                }
            })->implode(', ');


            // Create a new voucher
            $voucher = Voucher::create([
                'date' => $this->return_date,
                'voucher_type' => 'purchase-return',
                'reference_number' => $this->return_number,
                'total_amount' => $totalReturnAmount,
                'description' => 'Purchase return voucher',
                'status' => 1,
                'company_id' => session('company_id'),
                'created_by' => Auth()->id(),
            ]);

            // Debit Purchase Returns account
            VoucherDetail::create([
                'voucher_id' => $voucher->id,
                'account_id' => 12, // Purchase Return account ID
                'amount' => $totalReturnAmount,
                'type' => 'credit',
                'narration' => 'Purchase Return ' . $this->return_number . ' - Products: ' . $productDetails,
                'created_by' => Auth()->id(),
            ]);

            // Credit Customer account
            VoucherDetail::create([
                'voucher_id' => $voucher->id,
                'account_id' => $this->vendor_id, // Customer's account ID in chart of accounts
                'amount' => $totalReturnAmount,
                'type' => 'debit', // Correct this to credit instead of debit
                'narration' => 'Purchase Return ' . $this->return_number . ' - Products: ' . $productDetails,
                'created_by' => Auth()->id(),
            ]);

            // Commit the transaction if everything is successful
            DB::commit();

            // Dispatch the return modal hide event and success message
            $this->dispatch('hideModal_return');
            session()->flash('message', 'Sales Return  saved successfully!');
        } catch (\Exception $e) {
            // Rollback the transaction in case of any error
            DB::rollBack();

            // Flash the error message to the session for the user
            session()->flash('formerrors', 'An error occurred while saving the return. Please try again.');

            // Optionally, you can rethrow the exception if needed
            throw $e;
        }
    }


    public function edit($id)
    {
        $this->resetFields(); // Reset all fields before editing
        $this->resetValidation();

        // Find the return to be edited
        $return = PurchaseReturn::with(['items'])->findOrFail($id);

        // Populate the form fields with the return data
        $this->returnId = $return->id;
        $this->return_number = $return->return_number;
        $this->return_date = Carbon::parse($return->return_date)->format('Y-m-d');
        $this->vendor_id = $return->vendor_id;
        //$this->sales_invoice_id = $return->sales_invoice_id;
        $this->status = $return->status;

        // Populate the items (products) in the return
        $this->items = $return->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'return_quantity' => $item->return_quantity,
                'unit_price' => $item->unit_price,
                'return_amount' => $item->return_amount,
            ];
        })->toArray();

        // Calculate totals after loading items
        $this->calculateTotals();

        // Switch to edit mode
        $this->isEditMode = true;

        // Show the modal for editing the return
        $this->dispatch('showModal_return');
    }

    public function resetFields()
    {
        //$this->sales_invoice_id = null;
        $this->return_number = $this->generateReturnNumber();
        $this->vendor_id = null; // Reset vendor_id
        $this->return_date = Carbon::now()->format('Y-m-d');
        $this->status = 'posted';
        $this->company_id = null;
        $this->financial_year_id = null;
        $this->items = [];
        $this->vehicle_fare_adj = 0;

        $this->totalReturnedQuantity = 0;
        $this->totalReturnAmount = 0;
    }

    public function addItemRow()
    {
        $this->items[] = [
            'product_id' => null,
            'return_quantity' => 0,
            'unit_price' => 0,
            'return_amount' => 0
        ];
    }

    public function removeItemRow($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items); // Reset array keys after removing an item
    }

    public function calculateAmounts($index)
    {
        // Access the item by reference
        $item = &$this->items[$index];

        // Convert string inputs to floats to prevent string operations
        $return_quantity = floatval($item['return_quantity'] ?? 0);
        $unit_price = floatval($item['unit_price'] ?? 0);

        $return_amount = $unit_price * $return_quantity;
        $item['return_amount'] = round($return_amount, 2);

        // Calculate the totals after every change
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->totalReturnedQuantity = 0;
        $this->totalReturnAmount = 0;

        foreach ($this->items as $item) {
            $this->totalReturnedQuantity += floatval($item['return_quantity'] ?? 0);
            $this->totalReturnAmount += floatval($item['return_amount'] ?? 0);
        }
    }

    private function generateReturnNumber()
    {
        $company = Company::where('id', session('company_id'))->first(); // Fetch the company record
        $companyAbbreviation = $company ? $company->abv : 'SO'; // Default to 'SO' if not found

        $currentDate = now();

        // Determine the start of the fiscal year (starting from July)
        $financialYearStart = $currentDate->month >= 7
            ? $currentDate->copy()->month(7)->startOfMonth()
            : $currentDate->copy()->subYear()->month(7)->startOfMonth();

        // Get the last two digits of the current year for use in the return number
        $currentYear = substr($currentDate->format('Y'), -2); // Last two digits of the year

        // Count the number of sales returns in the current fiscal year
        $returnCount = PurchaseReturn::where('return_date', '>=', $financialYearStart)
            ->where('return_date', '<', $financialYearStart->copy()->addYear()) // Ensure it’s within the fiscal year
            ->count();

        // Start numbering from 1 for the new fiscal year
        $nextReturnNumber = $returnCount + 1;

        do {
            // Format the next return number with leading zeros
            $formattedNumber = str_pad($nextReturnNumber, 3, '0', STR_PAD_LEFT);
            // Generate the return number in the format: [CompanyAbbreviation]-[FiscalYearLast2Digits]-[FormattedNumber]
            $returnNumber = $companyAbbreviation . '-PR'  . $currentYear . '-' . $formattedNumber;

            // Check if the return number already exists
            $returnExists = PurchaseReturn::where('return_number', $returnNumber)->exists();

            if ($returnExists) {
                // If the number exists, increment and retry
                $nextReturnNumber++;
            }
        } while ($returnExists);

        return $returnNumber;
    }


    public function confirmDeletionSalesRetrun($id) {

        DB::transaction(function () use ($id) {
            // Find the purchase bill
            $saleRet = PurchaseReturn::findOrFail($id);

            // Check if the purchase bill has related vouchers by querying the `vouchers` table
            $voucher = Voucher::where('reference_number', $saleRet->return_number)->first();


            if ($voucher) {
                // Delete related voucher details
                VoucherDetail::where('voucher_id', $voucher->id)->delete();

                // Delete the voucher
                $voucher->delete();
            }

            // Delete the purchase bill items
            PurchaseReturnItem::where('purchase_return_id', $saleRet->id)->delete();

            // Delete the purchase bill
            $saleRet->delete();

            // Flash a success message
            session()->flash('message', 'Sales Return related entries deleted successfully.');
        }, 5); // Retry transaction 5 times in case of deadlock

    }
}
