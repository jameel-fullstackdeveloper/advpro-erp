<?php

namespace App\Livewire\Sales\Orders;

use Livewire\Component;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesOrderLog;
use App\Models\ChartOfAccount;
use App\Models\SalesProduct;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Items;

class SalesOrderManager extends Component
{
    use WithPagination;

    public $orderId, $order_number, $customer_id, $order_date, $status = '', $company_id, $financial_year_id, $farm_name,$farm_address,$created_by, $updated_by;
    public $products = []; // Will hold an array of products and quantities dynamically
    public $isEditMode = false;
    public $itemsPerPage = 50;
    public $searchTerm = '';
    public $comments = '';
    public $startDate;
    public $endDate;
    public $logs = [];
    public $order_status='pending';
    public $vehicle_no;
    public $vehicle_fare;
    public $farm_supervisor_mobile;

    protected $rules = [
        'customer_id' => 'required',
        'order_date' => 'required|date',
        'status' => 'required',
        'products.*.product_id' => 'required',
        'products.*.quantity' => 'required|integer|min:1',
        'comments' => 'nullable|string|max:255',
        'farm_name' => 'nullable|string|max:255',
        'farm_address' => 'nullable|string|max:255',
      ];

     // Real-time validation for startDate and endDate
     public function updatedStartDate()
     {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);
     }

     public function updatedEndDate()
     {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);
     }

    public function mount()
    {
        abort_if(!auth()->user()->can('sales orders view'), 403);

        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->order_date = Carbon::now()->format('Y-m-d'); // Ensure correct format
        $this->created_by = Auth::id();
        $this->company_id = 1;
        $this->order_number = $this->generateOrderNumber();
    }

    public function create()
    {
        $this->reset();
        $this->resetInputFields();
        $this->resetValidation();
        $this->addProductRow(); // Add an initial product row
        $this->isEditMode = false;
        $this->dispatch('showModal_order');
    }

    public function edit($id)
    {
        $salesOrder = SalesOrder::with('items')->findOrFail($id);
        $this->logs = SalesOrderLog::where('sales_order_id', $id)->with('user')->get();

        $this->orderId = $salesOrder->id;
        $this->order_number = $salesOrder->order_number;
        $this->customer_id = $salesOrder->customer_id;
        $this->order_date = $salesOrder->order_date->format('Y-m-d');
        $this->farm_name = $salesOrder->farm_name;
        $this->farm_address = $salesOrder->farm_address;
        $this->farm_supervisor_mobile = $salesOrder->farm_supervisor_mobile;
        $this->vehicle_no = $salesOrder->vehicle_no;
        $this->vehicle_fare = $salesOrder->vehicle_fare;
        $this->comments = $salesOrder->order_comments;
        $this->order_status = $salesOrder->status;

        // Load associated products and quantities
        $this->products = $salesOrder->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        $this->isEditMode = true;
        $this->dispatch('showModal_order');
    }

    public function store()
    {
        // Validate only the fields relevant to saving the order, bypassing the startDate and endDate
        $this->validate([
            'customer_id' => 'required',
            'order_date' => 'required|date',
            'products.*.product_id' => 'required',
            'products.*.quantity' => 'required|integer|min:1',
            'comments' => 'nullable|string|max:255',
            'farm_name' => 'nullable|string|max:255',
            'farm_address' => 'nullable|string|max:255',
            'farm_supervisor_mobile' => 'nullable|string|max:11',
            'vehicle_no' => 'nullable|string|max:10',
            'vehicle_fare' => 'nullable|integer',
        ]);

        // Check if it's an update or a new order
        $isUpdate = $this->orderId ? true : false;

         // Fetch the existing order number if it's an update
        $existingOrderNumber = $isUpdate ? SalesOrder::find($this->orderId)->order_number : $this->generateOrderNumber();

        $oldOrderData = $isUpdate ? SalesOrder::with('items')->find($this->orderId)->toArray() : null;


        // Create or update Sales Order
        $salesOrder = SalesOrder::updateOrCreate(
            ['id' => $this->orderId],
            [
                'order_number' => $existingOrderNumber,
                'customer_id' => $this->customer_id,
                'farm_name' => $this->farm_name ,
                'farm_address' => $this->farm_address,
                'farm_supervisor_mobile' => $this->farm_supervisor_mobile,
                'vehicle_no' => $this->vehicle_no,
                'vehicle_fare' => $this->vehicle_fare ?? 0,
                'order_date' => Carbon::parse($this->order_date)->toDateString(),
                'status' => $this->order_status,
                'company_id' => session('company_id'),
                'order_comments' => $this->comments, // Save comments
                'financial_year_id' => $this->financial_year_id,
                'created_by' => $isUpdate ? SalesOrder::find($this->orderId)->created_by : Auth::id(), // Set created_by only for new orders
                'updated_by' => $isUpdate ? Auth::id() : null, // Set updated_by for updates
                'created' => 0, // 1 for genrated by order module
            ]
        );

        $newOrderData = $salesOrder->toArray();
         $productChanges = [];

         if ($isUpdate) {
            // Track changes in products (Add/Edit/Remove)
            $oldItems = SalesOrderItem::where('sales_order_id', $this->orderId)->get();
            $newItems = collect($this->products);

            foreach ($newItems as $newItem) {
                $productName = SalesProduct::find($newItem['product_id'])->product_name ?? 'Unknown Product';

                $oldItem = $oldItems->firstWhere('product_id', $newItem['product_id']);
                if ($oldItem) {
                    if ($oldItem->quantity != $newItem['quantity']) {
                        $productChanges[] = [
                            'action' => 'updated',
                            'product_name' => $productName,
                            'old_quantity' => $oldItem->quantity,
                            'new_quantity' => $newItem['quantity'],
                        ];
                    }
                    $oldItems = $oldItems->filter(function ($item) use ($oldItem) {
                        return $item->id !== $oldItem->id;
                    });
                } else {
                    $productChanges[] = [
                        'action' => 'added',
                        'product_name' => $productName,
                        'new_quantity' => $newItem['quantity'],
                    ];
                }
            }

            // Handle removed products
            foreach ($oldItems as $oldItem) {
                $productName = SalesProduct::find($oldItem->product_id)->product_name ?? 'Unknown Product';

                $productChanges[] = [
                    'action' => 'removed',
                    'product_name' => $productName,
                    'old_quantity' => $oldItem->quantity,
                ];
            }

            // Log the changes in the order data
            $changes = array_diff_assoc($newOrderData, $oldOrderData);
            if (!empty($changes) || !empty($productChanges)) {
                SalesOrderLog::create([
                    'sales_order_id' => $salesOrder->id,
                    'user_id' => Auth::id(),
                    'action' => 'updated',
                    'old_data' => json_encode($oldOrderData),
                    'new_data' => json_encode($newOrderData),
                    'product_changes' => json_encode($productChanges),
                ]);
            }

            // Update the updated_by field
            $salesOrder->updated_by = Auth::id();
            $salesOrder->save();
        } else {
            // Log creation of a new order
           SalesOrderLog::create([
                'sales_order_id' => $salesOrder->id,
                'user_id' => Auth::id(),
                'action' => 'created',
                'new_data' => json_encode($newOrderData),
            ]);
        }

        // Clear existing order items before updating (in case of an update)
        SalesOrderItem::where('sales_order_id', $salesOrder->id)->delete();

        // Save selected products and their quantities
        foreach ($this->products as $product) {
            SalesOrderItem::create([
                'sales_order_id' => $salesOrder->id,
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
            ]);
        }

        $this->resetInputFields();
        $this->resetValidation();

        session()->flash('message', $this->orderId ? 'Sales Order updated successfully.' : 'Sales Order added successfully.');
        $this->dispatch('hideModal_order');
    }

    public function addProductRow()
    {
        $this->products[] = ['product_id' => '', 'quantity' => 1];
    }

    public function removeProductRow($index)
    {
        unset($this->products[$index]);
        $this->products = array_values($this->products); // Re-index the array
    }

    public function confirmDeletion($id)
    {
        // This method can be used to confirm and trigger the deletion
        $this->deleteOrder($id);
    }


    public function deleteOrder($id)
    {
        // Find the order by ID
        $order = SalesOrder::findOrFail($id);

        // Delete related SalesOrderItem records
        SalesOrderItem::where('sales_order_id', $order->id)->delete();

        // Now delete the SalesOrder
        $order->delete();

        session()->flash('message', 'Order and its related items deleted successfully.');

    }


    public function filter(){

        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

    }


    public function render()
    {
        $searchTerm = '%' . $this->searchTerm . '%';
        $companyId = session('company_id'); // Retrieve the company_id from the session

        // Fetch sales orders with eager loading of customer details, group title, and avatar
        $salesOrders = SalesOrder::with(['items.product', 'userCreated', 'userUpdated'])
            ->join('chart_of_accounts', 'sales_orders.customer_id', '=', 'chart_of_accounts.id')
            ->join('customer_details', 'chart_of_accounts.id', '=', 'customer_details.account_id') // Join customer details
            ->where('sales_orders.company_id', $companyId) // Filter by company_id
            ->where(function ($query) use ($searchTerm) {
                $query->where('sales_orders.order_number', 'like', $searchTerm)
                    ->orWhere('chart_of_accounts.name', 'like', $searchTerm); // Searching in ChartOfAccount
            })
            ->when($this->startDate && $this->endDate, function($query) {
                // Parse startDate and endDate to include the full day range
                $start = Carbon::parse($this->startDate)->startOfDay();
                $end = Carbon::parse($this->endDate)->endOfDay();
                $query->whereBetween('sales_orders.order_date', [$start, $end]);
            })
            ->when($this->status, function($query) {
                $query->where('sales_orders.status', $this->status);
            })
            ->select('sales_orders.*') // Ensure we are only selecting columns from sales_orders
            ->with('customer.coaGroupTitle') // Load group title from ChartOfAccount
            ->orderBy('sales_orders.id', 'desc') // Order by the most recent order_date
            ->paginate($this->itemsPerPage);

        // Loop through each sales order and fetch the corresponding weighbridge data manually
        foreach ($salesOrders as $order) {
            $weighbridgeData = DB::table('weighbridge_outward_order')
                ->join('weighbridge_outwards', 'weighbridge_outward_order.weighbridge_outward_id', '=', 'weighbridge_outwards.id')
                ->leftJoin('users', 'weighbridge_outwards.updated_by', '=', 'users.id') // Join with users table
                ->where('weighbridge_outward_order.sales_order_id', $order->id)
                ->select('weighbridge_outwards.*','users.name')
                ->first();

            $order->weighbridge_outward_data = $weighbridgeData; // Attach the weighbridge data to the order object
        }


        // Filter customers based on the company_id
        $customers = ChartOfAccount::where('is_customer_vendor', 'customer')
                                ->where('company_id', $companyId) // Filter by company_id
                                ->orWhere('is_customer_vendor', 'vendor')
                                ->get();



        $allProducts = Items::where(function ($query) {
                                    $query->where('item_type', 'sale') // for item_type 'sale'
                                          ->orWhere(function ($query) {
                                              $query->where('item_type', 'purchase') // for item_type 'purchase'
                                                    ->where('can_be_sale', 1); // can_be_sale should be 1
                                          });
                                })
                                ->where('company_id', $companyId)
                                ->get();

        return view('livewire.sales.orders.sales-order-manager', [
            'salesOrders' => $salesOrders,
            'customers' => $customers,
            'allProducts' => $allProducts,
        ]);
    }



    private function resetInputFields()
    {
        // Reset all input fields and set default values
        $this->order_number = $this->generateOrderNumber();
        $this->customer_id = '';
        $this->farm_name = '';
        $this->farm_address = '';
        $this->farm_supervisor_mobile = '';
        $this->vehicle_no = '';
        $this->vehicle_fare = '';
        $this->comments = '';
        $this->order_date = Carbon::now()->format('Y-m-d'); // Default to today's date
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d'); // Set startDate to the beginning of the month
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d'); // Set endDate to the end of the month
        $this->order_status = 'pending';
        $this->products = []; // Reset products
        $this->isEditMode = false;
    }

    private function generateOrderNumber()
    {
        $company = Company::where('id', session('company_id'))->first(); // Fetch the company record
        $companyAbbreviation = $company ? $company->abv : 'ORD'; // Default to 'ORD' if not found

        $currentDate = now();

        // Determine the start of the fiscal year (starting from July)
        $financialYearStart = $currentDate->month >= 7
            ? $currentDate->copy()->month(7)->startOfMonth()
            : $currentDate->copy()->subYear()->month(7)->startOfMonth();

        // Get the last two digits of the current year for use in the order number
        $currentYear = substr($currentDate->format('Y'), -2); // Last two digits of the year

        // Count the number of sales orders in the current fiscal year for the specific company
        $orderCount = SalesOrder::where('company_id', $company->id)
            ->where('order_date', '>=', $financialYearStart)
            ->where('order_date', '<', $financialYearStart->copy()->addYear()) // Ensure it's within the fiscal year
            ->count();

        // Start numbering from 1 for the new fiscal year
        $nextOrderNumber = $orderCount + 1;

        do {
            // Format the next order number with leading zeros
            $formattedNumber = str_pad($nextOrderNumber, 3, '0', STR_PAD_LEFT);
            // Generate the order number in the format: [CompanyAbbreviation]-[FiscalYearLast2Digits]-[FormattedNumber]
            $orderNumber = $companyAbbreviation . '-SO'  . $currentYear . '-' . $formattedNumber;

            // Check if the order number already exists for the specific company
            $orderExists = SalesOrder::where('company_id', $company->id)
                ->where('order_number', $orderNumber)
                ->exists();

            if ($orderExists) {
                // If the number exists, increment and retry
                $nextOrderNumber++;
            }
        } while ($orderExists);

        return $orderNumber;
    }



}
