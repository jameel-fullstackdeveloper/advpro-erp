<?php

namespace App\Livewire\Weighbridge\Inwards;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FirstweightManager extends Component
{
    use WithPagination;

    public $inward_id;
    public $first_weight_datetime;
    public $truck_number;
    public $first_weight;
    public $driveroption;
    public $driver_name;
    public $driver_mobile;

    public $billty_number;
    public $freight = 0;
    public $total_bags = 0;
    public $party_gross_weight = 0;
    public $party_tare_weight = 0;
    public $party_net_weight = 0;
    public $comments;

    public $itemsPerPage = 50;
    public $searchTerm;
    public $isEditMode = false;

    public $selectedOrders = [];
    public $ordersForTruck = [];
    public $selectedOrder;

    public $errorMessage;

    public $isSubmitting = false;

    protected $rules = [
        'first_weight_datetime' => 'required|date',
        'truck_number' => 'required|string',
        'first_weight' => 'required|numeric',
        'driveroption' => 'required|string',
        'billty_number' => 'required|string',
        'freight' => 'required|integer',
        'total_bags' => 'required|integer',
        'party_gross_weight' => 'required|integer',
        'party_tare_weight' => 'required|integer',
        'party_net_weight' => 'required|integer',

    ];

    public function mount()
    {
        abort_if(!auth()->user()->can('weighbridge view'), 403);

        $this->first_weight_datetime = Carbon::now()->format('d-m-Y h:i');
    }

    public function create()
    {
        $this->resetFields();
        $this->isEditMode = false;
        $this->dispatch('showModal_firstweight');
        $this->dispatch('initializeSocketForWeight_inwards'); // This event triggers the socket initialization
    }

    public function edit($id)
    {
        $this->isEditMode = true;

        // Fetch the weighbridge inward data
        $inward = DB::table('weighbridge_inwards')->where('id', $id)->first();

        // Set the form fields with existing data
        $this->inward_id = $inward->id;
        $this->first_weight_datetime = Carbon::parse($inward->first_weight_datetime)->format('Y-m-d\TH:i');
        $this->truck_number = $inward->truck_number;
        $this->first_weight = $inward->first_weight;
        $this->driveroption = $inward->driveroption;
        $this->billty_number = $inward->billty_number;
        $this->freight = $inward->freight;
        $this->total_bags = $inward->total_bags;
        $this->party_gross_weight = $inward->party_gross_weight;
        $this->party_tare_weight = $inward->party_tare_weight;
        $this->party_net_weight = $inward->party_net_weight;
        $this->comments = $inward->comments;

        // Fetch associated purchase orders for this weighbridge inward
        $associatedOrders = DB::table('weighbridge_inward_orders')
            ->join('purchase_orders', 'weighbridge_inward_orders.purchase_order_id', '=', 'purchase_orders.id')
            ->join('chart_of_accounts', 'purchase_orders.vendor_id', '=', 'chart_of_accounts.id')
            ->join('purchase_order_items', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->join('purchase_items', 'purchase_order_items.product_id', '=', 'purchase_items.id')
            ->where('weighbridge_inward_orders.weighbridge_inward_id', $id)
            ->select(
                'purchase_orders.id as order_id',
                'purchase_orders.order_number',
                'chart_of_accounts.name as customer_name',
                'purchase_items.item_name as product_name',
                'purchase_order_items.quantity as product_quantity'
            )
            ->get();

        // Populate ordersForTruck array with associated orders and items
        $this->ordersForTruck = [];

        foreach ($associatedOrders as $order) {
            $this->ordersForTruck[$order->order_id][$order->product_name] = [
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'product_name' => $order->product_name,
                'quantity' => $order->product_quantity,
            ];
    }

    // Set selectedOrder to the first associated order (if available)
    if (!empty($associatedOrders)) {
        $firstOrder = $associatedOrders->first();
        $this->selectedOrder = $firstOrder->order_id . '-' . $firstOrder->product_name;
    }

    // Dispatch the event to show the modal
    $this->dispatch('showModal_firstweight');
    }




    public function confirmDeletionFirstWeight($id)
    {
        $this->deleteFirstWeight($id);
    }

    public function deleteFirstWeight($id)
    {
        DB::table('weighbridge_inwards')->where('id', $id)->delete();
        session()->flash('message', 'First Weight record deleted successfully.');
    }

    public function store()
    {
        $this->validate();

        if (empty($this->ordersForTruck)) {
            $this->addError('ordersForTruck', 'At least one order must be added to the truck.');
            return;
        }

          // Disable the submit button
        $this->isSubmitting = true;

        DB::beginTransaction();

        try {
            if ($this->isEditMode) {
                $inward = DB::table('weighbridge_inwards')->where('id', $this->inward_id)->first();
            }

            if (isset($inward)) {
                DB::table('weighbridge_inwards')->where('id', $this->inward_id)->update([
                    'first_weight_datetime' => Carbon::parse($this->first_weight_datetime),
                    'truck_number' => $this->truck_number,
                    'first_weight' => $this->first_weight,
                    'driveroption' => $this->driveroption,
                    'billty_number' => $this->billty_number,
                    'freight' => $this->freight,
                    'total_bags' => $this->total_bags,
                    'party_gross_weight' => $this->party_gross_weight,
                    'party_tare_weight' => $this->party_tare_weight,
                    'party_net_weight' => $this->party_net_weight,
                    'status' => 0,
                    'company_id' => session('company_id'),
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);
                $inward_id = $this->inward_id;
            } else {
                $inward_id = DB::table('weighbridge_inwards')->insertGetId([
                    'first_weight_datetime' => Carbon::parse($this->first_weight_datetime),
                    'truck_number' => $this->truck_number,
                    'first_weight' => $this->first_weight,
                    'driveroption' => $this->driveroption,
                    'billty_number' => $this->billty_number,
                    'freight' => $this->freight,
                    'total_bags' => $this->total_bags,
                    'party_gross_weight' => $this->party_gross_weight,
                    'party_tare_weight' => $this->party_tare_weight,
                    'party_net_weight' => $this->party_net_weight,
                    'status' => 0,
                    'comments' => $this->comments,
                    'company_id' => session('company_id'),
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                ]);
            }

            DB::table('weighbridge_inward_orders')->where('weighbridge_inward_id', $inward_id)->delete();

            $orderInsertData = [];
            foreach ($this->ordersForTruck as $orderId => $products) {
                foreach ($products as $productName => $details) {
                    $orderInsertData[] = [
                        'weighbridge_inward_id' => $inward_id,
                        'purchase_order_id' => $orderId,
                        'order_weight' => $details['quantity'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            DB::table('weighbridge_inward_orders')->insert($orderInsertData);

            DB::commit();

            session()->flash('message', $this->isEditMode ? 'First Weight updated successfully.' : 'First Weight created successfully.');
            $this->resetFields();
            $this->ordersForTruck = [];
            $this->dispatch('hideModal_firstweight');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error saving Weighbridge Inward: ' . $e->getMessage());
            $this->errorMessage = $e->getMessage();
        } finally {
            // Re-enable the submit button
            $this->isSubmitting = false;
        }
    }

    public function resetFields()
    {
        $this->first_weight_datetime = Carbon::now()->format('Y-m-d h:i A');
        $this->truck_number = '';
        $this->first_weight = '';
        $this->driver_name = '';
        $this->driver_mobile = '';
        $this->billty_number = '';
        $this->freight = 0;
        $this->total_bags = 0;
        $this->party_gross_weight = 0;
        $this->party_tare_weight = 0;
        $this->party_net_weight = 0;
        $this->driveroption = '';
        $this->comments = '';
        $this->selectedOrder = '';
        $this->ordersForTruck = [];
    }

    public function addOrderToTruck()
    {
        if (!$this->selectedOrder) {
            $this->addError('selectedOrder', 'Please select an order.');
            return;
        }

        [$selectedOrderId, $selectedProductName] = explode('-', $this->selectedOrder);

        $order = DB::table('purchase_orders')
            ->leftJoin('chart_of_accounts', 'purchase_orders.vendor_id', '=', 'chart_of_accounts.id')
            ->leftJoin('purchase_order_items', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->leftJoin('purchase_items', 'purchase_order_items.product_id', '=', 'purchase_items.id')
            ->select(
                'purchase_orders.id as order_id',
                'purchase_orders.order_number',
                'chart_of_accounts.name as customer_name',
                'purchase_items.item_name as product_name',
                'purchase_order_items.quantity as product_quantity'
            )
            ->where('purchase_orders.id', $selectedOrderId)
            ->where('purchase_items.item_name', $selectedProductName)
            ->first();

        if (!$order) {
            $this->addError('selectedOrder', 'Selected order or product not found.');
            return;
        }

        if (isset($this->ordersForTruck[$order->order_id][$order->product_name])) {
            $this->addError('selectedOrder', 'This product is already added to the truck.');
            return;
        }

        $this->ordersForTruck[$order->order_id][$order->product_name] = [
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'product_name' => $order->product_name,
            'quantity' => $order->product_quantity,
        ];

        $this->selectedOrder = null;
    }

    public function removeOrderFromTruck($orderId, $productName)
    {
        if (isset($this->ordersForTruck[$orderId][$productName])) {
            unset($this->ordersForTruck[$orderId][$productName]);

            if (empty($this->ordersForTruck[$orderId])) {
                unset($this->ordersForTruck[$orderId]);
            }
        }
    }

    public function render()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session

        // Fetch paginated first weight records along with vendor name and item name
        $inwardsfirstweight = DB::table('weighbridge_inwards')
            ->join('weighbridge_inward_orders', 'weighbridge_inwards.id', '=', 'weighbridge_inward_orders.weighbridge_inward_id')
            ->join('purchase_orders', 'weighbridge_inward_orders.purchase_order_id', '=', 'purchase_orders.id')
            ->join('chart_of_accounts', 'purchase_orders.vendor_id', '=', 'chart_of_accounts.id') // Fetching vendor name
            ->join('purchase_order_items', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->join('purchase_items', 'purchase_order_items.product_id', '=', 'purchase_items.id') // Fetching product name
            ->select(
                'weighbridge_inwards.id',
                'weighbridge_inwards.created_at',
                'weighbridge_inwards.truck_number',
                'weighbridge_inwards.billty_number',
                'weighbridge_inwards.total_bags',
                'weighbridge_inwards.freight',
                'weighbridge_inwards.first_weight',
                'weighbridge_inwards.party_gross_weight',
                'weighbridge_inwards.party_tare_weight',
                'weighbridge_inwards.party_net_weight',
                'chart_of_accounts.name as vendor_name', // Correct selection for vendor name
                'purchase_items.item_name as product_name' // Correct selection for item name
            )
            ->where('weighbridge_inwards.company_id', $companyId) // Filter by company_id
            ->where(function ($query) {
                if ($this->searchTerm) {
                    $query->where('truck_number', 'like', '%' . $this->searchTerm . '%')
                        ->orWhere('chart_of_accounts.name', 'like', '%' . $this->searchTerm . '%');
                }
            })
            ->whereNull('weighbridge_inwards.net_weight') // Where net weight is null
            ->orderBy('weighbridge_inwards.created_at', 'desc')
            ->paginate($this->itemsPerPage);

        // Fetch purchase orders for the dropdown
        $orders = DB::table('purchase_orders')
            ->leftJoin('chart_of_accounts', 'purchase_orders.vendor_id', '=', 'chart_of_accounts.id')
            ->leftJoin('purchase_order_items', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->leftJoin('purchase_items', 'purchase_order_items.product_id', '=', 'purchase_items.id')
            ->select(
                'purchase_orders.id as order_id',
                'purchase_orders.order_number',
                'purchase_orders.status as order_status',
                'chart_of_accounts.name as customer_name',
                'purchase_items.item_name as product_name',
                'purchase_order_items.quantity as product_quantity'
            )
            ->where('purchase_orders.company_id', $companyId) // Filter by company_id
            ->where('purchase_orders.status', '!=', 'completed')
            ->get()
            ->groupBy('order_id');

        return view('livewire.weighbridge.inwards.firstweight-manager', [
            'inwardsfirstweight' => $inwardsfirstweight,
            'orders' => $orders,
        ]);
    }



}
