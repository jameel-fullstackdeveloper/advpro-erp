<?php

namespace App\Livewire\Weighbridge\Inwards;

use Livewire\Component;
use App\Models\CustomerDetail;
use App\Models\ChartOfAccount;
use App\Models\WeighbridgeInward;
use App\Models\SalesOrder;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SecondweightManager extends Component
{
    use WithPagination;

    // Form fields
    public $second_weight_datetime;
    public $selectedVehicle;
    public $selectedOrder;
    public $ordersForTruck = [];
    public $vehicles = [];
    public $orders = [];
    // Fields fetched when vehicle is selected
    public $truck_number;
    public $driveroption;
    public $driver_name;
    public $driver_mobile;
    public $billty_number;
    public $total_bags;
    public $freight;
    public $party_gross_weight;
    public $party_tare_weight;
    public $party_net_weight;
    public $first_weight_s = '';
    public $second_weight_s = '';
    public $net_weight_s = '';

    public $isSubmitting = false;

    public function mount()
    {

        abort_if(!auth()->user()->can('weighbridge view'), 403);

        // Initialize date-time for second weight
        $this->second_weight_datetime = Carbon::now()->format('Y-m-d\TH:i');

        // Fetch available vehicles (trucks)
        $this->vehicles = DB::table('weighbridge_inwards')
            ->select('id', 'truck_number')
            ->where('weighbridge_inwards.net_weight', '=', null)
            ->where('company_id', session('company_id')) // Filter by company_id
            ->get();
    }

    public function updatedSelectedVehicle($vehicleId)
    {
         // If no vehicle is selected, reset all the input fields
        if (!$vehicleId) {
            $this->reset([
                'truck_number',
                'driveroption',
                'billty_number',
                'total_bags',
                'freight',
                'party_gross_weight',
                'party_tare_weight',
                'party_net_weight',
                'first_weight_s',
                'second_weight_s',
                'net_weight_s',
                'ordersForTruck'
            ]);

            return;
        }

        // Fetch all related data for the selected vehicle from the weighbridge_inwards table
        $inward = DB::table('weighbridge_inwards')
            ->where('id', $vehicleId)
            ->first();

        if ($inward) {
            // Populate form fields with data from first weight entry
            $this->truck_number = $inward->truck_number;
            $this->driveroption = $inward->driveroption;
            $this->billty_number = $inward->billty_number;
            $this->total_bags = $inward->total_bags;
            $this->freight = $inward->freight;
            $this->party_gross_weight = $inward->party_gross_weight;
            $this->party_tare_weight = $inward->party_tare_weight;
            $this->party_net_weight = $inward->party_net_weight;
            $this->first_weight_s = $inward->first_weight;

            // Fetch purchase orders linked to this vehicle via weighbridge_inward_orders table
            $orders = DB::table('weighbridge_inward_orders')
                ->join('purchase_orders', 'weighbridge_inward_orders.purchase_order_id', '=', 'purchase_orders.id')
                ->join('chart_of_accounts', 'purchase_orders.vendor_id', '=', 'chart_of_accounts.id')
                ->join('purchase_order_items', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
                ->join('purchase_items', 'purchase_order_items.product_id', '=', 'purchase_items.id')
                ->select(
                    'purchase_orders.id as order_id',
                    'purchase_orders.order_number',
                    'chart_of_accounts.name as customer_name',
                    'purchase_items.id as product_id',
                    'purchase_items.item_name as product_name',
                    'purchase_order_items.quantity as product_quantity'
                )
                ->where('weighbridge_inward_orders.weighbridge_inward_id', $vehicleId)  // Linking the vehicle to the purchase order via weighbridge_inward_orders
                ->get();

            // Structure the orders and products for display
            $this->ordersForTruck = [];
            foreach ($orders as $orderDetail) {
                if (!isset($this->ordersForTruck[$orderDetail->order_id])) {
                    $this->ordersForTruck[$orderDetail->order_id] = [
                        'order_number' => $orderDetail->order_number,
                        'customer_name' => $orderDetail->customer_name,
                        'products' => [],
                    ];
                }

                $this->ordersForTruck[$orderDetail->order_id]['products'][] = [
                    'product_id' => $orderDetail->product_id,
                    'product_name' => $orderDetail->product_name,
                    'quantity' => $orderDetail->product_quantity,
                ];
            }

            // If there is no order or product to show, give feedback
            if (empty($this->ordersForTruck)) {
                $this->addError('selectedOrder', 'No associated orders found for this truck.');
            }
        }

        $this->dispatch('initializeSocketForWeight_inwards_s'); // This event triggers the socket initialization
    }



    public function calculateNetWeight()
    {
        if ($this->first_weight_s && $this->second_weight_s) {
            $this->net_weight_s = $this->first_weight_s -  $this->second_weight_s ;
        }
    }

    public function store()
    {

        // Validate the form fields
        $this->validate([
            'second_weight_datetime' => 'required|date',
            'selectedVehicle' => 'required|integer',
            'first_weight_s' => 'required|numeric',
            'second_weight_s' => 'required|numeric',
            'net_weight_s' => 'required|numeric',
            'ordersForTruck' => 'required|array|min:1',
        ]);

        $this->isSubmitting = true;

        try {
            // Start the database transaction
            DB::beginTransaction();





            // Step 1: Update the second weight in the weighbridge_inwards table
            DB::table('weighbridge_inwards')
                ->where('id', $this->selectedVehicle)
                ->update([
                    'second_weight' => $this->second_weight_s,
                    'net_weight' => $this->net_weight_s,
                    'second_weight_datetime' => $this->second_weight_datetime,
                    'updated_by' => auth()->user()->id,
                    'status' => 1,
                    'updated_at' => now(),
                ]);

            // Step 2: Create a new purchase bill
            foreach ($this->ordersForTruck as $orderId => $orderDetails) {
                $order = DB::table('purchase_orders')->find($orderId);

                // Step 3: Create or update the purchase bill for each order
                $purchaseBillData = [
                    'vendor_id' => $order->vendor_id,
                    'order_id' => $orderId,
                    'vehicle_no' => $this->truck_number, // From weighbridge
                    'bill_date' => Carbon::now()->format('Y-m-d'),
                    'bill_number' => $this->generateBillNumber(), // Implement this function to generate bill numbers
                    'status' => 'Init',
                    'is_weighbridge' => 1,
                    'freight' => $this->freight, // Freight from weighbridge data
                    'broker_id' => 0, // Adjust this if broker is involved
                    'broker_rate'=> 0, // Adjust if applicable
                    'broker_amount' => 0, // Adjust if applicable
                    'delivery_mode'=> 'weighbridge', // Set delivery mode to weighbridge or as per your business logic
                    'company_id' => session('company_id'),
                    'comments' => 'Purchase bill created from weighbridge',
                    'created_by' => auth()->id(),
                ];

                // Create a new purchase bill
                $purchaseBill = PurchaseBill::create($purchaseBillData);

                // Step 4: Add purchase bill items using second weight as quantity
                foreach ($orderDetails['products'] as $product) {
                    // Log product data for debugging
                    \Log::info('Product being processed:', (array) $product);

                    if (isset($product['product_id']) && $product['product_id'] !== null) {
                        $net_quantity = $this->net_weight_s; // Use the calculated net weight as quantity

                        PurchaseBillItem::create([
                            'purchase_bill_id' => $purchaseBill->id,
                            'product_id' => $product['product_id'], // Ensure product_id exists
                            'quantity' => $net_quantity, // Net weight from weighbridge as quantity
                            'deduction' => 0, // Adjust if there are deductions
                            'net_quantity' => $net_quantity, // Same as net weight for now
                            'price' => 0, // Set price or fetch from product/order data
                            'gross_amount' => 0, // Calculate gross amount (quantity * price)
                            'sales_tax_rate' => 0, // Adjust as needed
                            'sales_tax_amount' => 0, // Adjust as needed
                            'withholding_tax_rate' => 0, // Adjust as needed
                            'withholding_tax_amount' => 0, // Adjust as needed
                            'net_amount' => 0, // Adjust as needed, based on deductions and taxes
                            'created_by' => auth()->id(),
                        ]);
                    } else {
                        \Log::error('Product ID missing or null:', (array) $product);
                    }
                }

            }

            // Commit transaction
            DB::commit();

            // Clear form
            $this->reset([
                'truck_number',
                'driveroption',
                'billty_number',
                'total_bags',
                'freight',
                'party_gross_weight',
                'party_tare_weight',
                'party_net_weight',
                'first_weight_s',
                'second_weight_s',
                'net_weight_s',
                'ordersForTruck'
            ]);

            session()->flash('message', 'Second weight and purchase bill created successfully.');
            $this->dispatch('saved');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred while processing: ' . $e->getMessage());
        } finally {
            // Re-enable the submit button
            $this->isSubmitting = false;
        }
    }


    public function render()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session

        // Fetch orders using raw queries filtered by company_id
        $this->orders = DB::table('purchase_orders')
            ->leftJoin('chart_of_accounts', 'purchase_orders.vendor_id', '=', 'chart_of_accounts.id')
            ->select(
                'purchase_orders.id as order_id',
                'purchase_orders.order_number',
                'chart_of_accounts.name as vendor_name'
            )
            ->where('purchase_orders.status', 'pending')
            ->where('purchase_orders.company_id', $companyId) // Filter by company_id for purchase orders
            ->where('chart_of_accounts.company_id', $companyId) // Filter by company_id for vendors
            ->get();

        return view('livewire.weighbridge.inwards.secondweight-manager', [
            'vehicles' => $this->vehicles,
            'orders' => $this->orders,
        ]);
    }


    private function generateBillNumber()
    {
        $currentDate = now();

        // Determine the start of the financial year (starting from July)
        $financialYearStart = $currentDate->month >= 7
            ? $currentDate->copy()->month(7)->startOfMonth()
            : $currentDate->copy()->subYear()->month(7)->startOfMonth();

        // Format the current month and year as MMYY
        $monthYear = $currentDate->format('m') . substr($currentDate->format('Y'), -2);

        // Count the number of bills in the current financial year
        $billCount = PurchaseBill::where('bill_date', '>=', $financialYearStart)->count();
        $nextBillNumber = $billCount + 1;

        // Format the next bill number with leading zeros (e.g., 001, 002, etc.)
        $formattedNumber = str_pad($nextBillNumber, 3, '0', STR_PAD_LEFT);
        $billNumber = 'PB' . $monthYear . '-' . $formattedNumber;

        // Ensure the bill number is unique, regenerate if needed
        while (PurchaseBill::where('bill_number', $billNumber)->exists()) {
            $nextBillNumber++;
            $formattedNumber = str_pad($nextBillNumber, 3, '0', STR_PAD_LEFT);
            $billNumber = 'PB' . $monthYear . '-' . $formattedNumber;
        }

        return $billNumber;
    }
}
