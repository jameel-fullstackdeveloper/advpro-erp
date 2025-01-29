<?php

namespace App\Livewire\Purchase\Orders;

use Livewire\Component;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ChartOfAccount;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Models\Items;
use App\Models\ItemGroup;

class OrderDetailManager extends Component
{
    use WithPagination;

    public $isEditMode = false;
    public $orderId;
    public $order_number;
    public $vendor_id;
    public $product_id;
    public $order_date;
    public $total_quantity;
    public $remaining_quantity=0;
    public $status = 'init';
    public $company_id=1;
    public $itemsPerPage = 50;
    public $searchTerm = '';
    public $price = 0;
    public $comments = '';
    public $delivery_mode = '';
    public $credit_days = 0;
    public $broker_id=0; // Add this property to store broker selection

    public $items = []; // To store multiple order items

    protected $rules = [
        'vendor_id' => 'required',
        'order_date' => 'required|date',
        'status' => 'required|string',
        'delivery_mode' => 'required|string',
        'credit_days' => 'required|integer|min:0',
        'items.*.product_id' => 'required|integer',
        'items.*.quantity' => 'required|numeric|min:1',
        'items.*.price' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        abort_if(!auth()->user()->can('purchases orders view'), 403);
        $this->order_date = Carbon::now()->format('Y-m-d'); // Default to today's date
        $this->company_id = 1; // Example default company
    }

    public function create()
    {
        $this->reset();
        $this->resetInputFields();
        $this->resetValidation();
        $this->isEditMode = false;
        $this->addItem(); // Add an initial product row
        $this->dispatch('showModal_order');
    }

    public function store()
    {
        // Validate the input fields
        $this->validate();

        DB::transaction(function () {
            // Check if it's an update or a new order
            $isUpdate = $this->orderId ? true : false;

            // Retrieve the existing order number for updates, or generate a new one for new orders
            $orderNumber = $isUpdate
                ? PurchaseOrder::find($this->orderId)->order_number
                : $this->generateOrderNumber();

            // Create or update the Purchase Order
            $purchaseOrder = PurchaseOrder::updateOrCreate(
                ['id' => $this->orderId],
                [
                    'order_date' => Carbon::parse($this->order_date)->toDateString(),
                    'order_number' => $orderNumber, // Use existing or new order number
                    'vendor_id' => $this->vendor_id,
                    'status' => $this->status,
                    'company_id' => session('company_id'),
                    'comments' => $this->comments,
                    'delivery_mode' => $this->delivery_mode,
                    'credit_days' => $this->credit_days,
                    'broker_id' => $this->broker_id,
                    'created_by' => $isUpdate ? PurchaseOrder::find($this->orderId)->created_by : Auth::id(), // Set created_by only for new orders
                    'updated_by' => $isUpdate ? Auth::id() : null, // Set updated_by for updates
                ]
            );

            // If editing, remove all existing items before re-adding
            if ($this->isEditMode) {
                $purchaseOrder->items()->delete();
            }

            // Create purchase order items
            foreach ($this->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total_price' => $item['quantity'] * $item['price'],
                ]);
            }
        });

        // Reset the form
        $this->resetInputFields();
        $this->resetValidation();

        session()->flash('message', $this->orderId ? 'Purchase Order updated successfully.' : 'Purchase Order created successfully.');
        $this->dispatch('hideModal_order');
    }


    public function edit($id)
    {
        $purchaseOrder = PurchaseOrder::with('items')->findOrFail($id);
        $this->orderId = $purchaseOrder->id;
        $this->vendor_id = $purchaseOrder->vendor_id;
        $this->order_date = Carbon::parse($purchaseOrder->order_date)->format('Y-m-d');
        $this->status = $purchaseOrder->status;
        $this->comments = $purchaseOrder->comments;
        $this->delivery_mode = $purchaseOrder->delivery_mode;
        $this->credit_days = $purchaseOrder->credit_days;
        $this->broker_id = $purchaseOrder->broker_id;

        // Load the items into the items array for editing
        $this->items = $purchaseOrder->items->toArray();

        $this->isEditMode = true;
        $this->dispatch('showModal_order');
    }

    public function confirmDeletion($id)
    {
        // Fetch the PurchaseOrder model by ID
        $purchaseOrder = PurchaseOrder::find($id);

        // Check if the PurchaseOrder exists
        if (!$purchaseOrder) {
            session()->flash('error', 'Purchase Order not found.');
            return;
        }

        // Check if the PurchaseOrder is linked to any PurchaseBills
        if ($purchaseOrder->purchaseBills()->exists()) {
            // Flash an error message if linked PurchaseBills exist
            session()->flash('error', 'Cannot delete Purchase Order. Linked Purchase Bills exist.');
        } else {
            // Proceed to delete if no linked PurchaseBills are found
            $purchaseOrder->delete();
            session()->flash('message', 'Purchase Order deleted successfully.');
        }
    }


    private function resetInputFields()
    {
        $this->vendor_id = '';
        $this->product_id = '';
        $this->order_date = Carbon::now()->format('Y-m-d');
        $this->total_quantity = '';
        $this->remaining_quantity = 0;
        $this->status = 'init';
        $this->comments = '';
        $this->broker_id = 0;
        $this->isEditMode = false;
        $this->items = []; // Reset items
    }

    private function generateOrderNumber()
    {
        $company = Company::where('id', session('company_id'))->first(); // Fetch the company record
        $companyAbbreviation = $company ? $company->abv : 'PO'; // Default to 'PO' if not found

        $currentDate = now();

        // Determine the start of the fiscal year (starting from July)
        $financialYearStart = $currentDate->month >= 7
            ? $currentDate->copy()->month(7)->startOfMonth()
            : $currentDate->copy()->subYear()->month(7)->startOfMonth();

        // Get the last two digits of the current year for use in the order number
        $currentYear = substr($currentDate->format('Y'), -2); // Last two digits of the year

        // Count the number of purchase orders in the current fiscal year for the specific company
        $orderCount = PurchaseOrder::where('company_id', $company->id)
            ->where('order_date', '>=', $financialYearStart)
            ->where('order_date', '<', $financialYearStart->copy()->addYear()) // Ensure it’s within the fiscal year
            ->count();

        // Start numbering from 1 for the new fiscal year
        $nextOrderNumber = $orderCount + 1;

        do {
            // Format the next order number with leading zeros
            $formattedNumber = str_pad($nextOrderNumber, 3, '0', STR_PAD_LEFT);
            // Generate the order number in the format: [CompanyAbbreviation]-[FiscalYearLast2Digits]-[FormattedNumber]
            $orderNumber = $companyAbbreviation . '-PO'  . $currentYear . '-' . $formattedNumber;

            // Check if the order number already exists for the specific company
            $orderExists = PurchaseOrder::where('company_id', $company->id)
                ->where('order_number', $orderNumber)
                ->exists();

            if ($orderExists) {
                // If the number exists, increment and retry
                $nextOrderNumber++;
            }
        } while ($orderExists);

        return $orderNumber;
    }




    public function render()
    {
        $searchTerm = '%' . $this->searchTerm . '%';


            $purchaseOrders = PurchaseOrder::with(['vendor', 'items.product'])
            ->paginate($this->itemsPerPage);

        // Calculate remaining quantity for each order
        foreach ($purchaseOrders as $order) {
            foreach ($order->items as $item) {
                // Sum the received quantities based on the product_id and purchase_order_id
                $receivedQuantity = DB::table('purchase_bill_items')
                    ->join('purchase_bills', 'purchase_bill_items.purchase_bill_id', '=', 'purchase_bills.id')
                    ->where('purchase_bills.order_id', $order->id) // Match the order
                    ->where('purchase_bill_items.product_id', $item->product_id) // Match the product
                    ->sum('purchase_bill_items.net_quantity'); // Sum received quantities

                // Remaining quantity = Ordered quantity - Received quantity
                $item->remaining_quantity =  $receivedQuantity - $item->ordered_quantity ;
            }
        }

        // Fetch vendors filtered by company_id
        $vendors = ChartOfAccount::where('is_customer_vendor', 'vendor')
                                ->orderBy('name')
                                ->get();

        // Fetch all products filtered by company_id
        $allProducts = Items::where('item_type', 'purchase')
        ->orderBy('name')
        ->get();


        // Fetch brokers filtered by company_id
        $brokers = ChartOfAccount::where('is_customer_vendor', 'purchase_broker')
                                ->get();

        return view('livewire.purchase.orders.order-detail-manager', [
            'purchaseOrders' => $purchaseOrders,
            'vendors' => $vendors,
            'allProducts' => $allProducts,
            'brokers' => $brokers,
        ]);
    }




    public function addItem()
    {
        $this->items[] = ['product_id' => '', 'quantity' => 1, 'price' => 0];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items); // Reindex array after removal
    }

}

