<?php

namespace App\Livewire\Weighbridge\Outwards;

use Livewire\Component;
use App\Models\CustomerDetail;
use App\Models\ChartOfAccount;
use App\Models\WeighbridgeOutward;
use App\Models\SalesOrder;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesProduct;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Company;

class SecondweightManager extends Component
{

    use WithPagination;

    public $outward_id, $second_weight_s_datetime, $truck_number;
    public $first_weight_s = '';
    public $second_weight_s = '';
    public $net_weight_s = '';
    public $driver_name = '';
    public $driver_mobile = '';
    public $driveroption ='';
    public $itemsPerPage = 50;
    public $searchTerm;
    public $isEditMode = false;
    public $selectedVehicle;
    public $selectedOrders = []; // To store multiple selected orders
    public $ordersForTruck = []; // To store assigned orders for the selected vehicle
    public $selectedOrder; // This holds the single selected order before adding to the truck

    public $isSubmitting = false;

    protected $rules = [
        'second_weight_s_datetime' => 'required|date',
        'selectedVehicle' => 'required|string',
        'first_weight_s' => 'required|numeric',
        'second_weight_s' => 'required|numeric',
        'net_weight_s' => 'required|numeric',

    ];



    public function mount()
    {
        abort_if(!auth()->user()->can('weighbridge view'), 403);
        // Set the second_weight_s_datetime to the current date and time
        $this->second_weight_s_datetime = Carbon::now()->format('Y-m-d\TH:i');
    }

    public function addOrderToTruck()
    {
        if ($this->selectedOrder) {
            // Fetch the order and related products
            $order = DB::table('sales_orders')
                ->leftJoin('chart_of_accounts', 'sales_orders.customer_id', '=', 'chart_of_accounts.id')
                ->select(
                    'sales_orders.id as order_id',
                    'sales_orders.order_number',
                    'sales_orders.customer_id',
                    'sales_orders.status as order_status',
                    'chart_of_accounts.name as customer_name',
                    'sales_orders.farm_name',
                    'sales_orders.farm_address',
                )
                ->where('sales_orders.id', $this->selectedOrder)
                ->first();


            // Fetch products for the order from the sales_orders_item table
            $products = DB::table('sales_order_items')
            ->join('sales_products', 'sales_order_items.product_id', '=', 'sales_products.id')
            ->where('sales_order_items.sales_order_id', $this->selectedOrder) // Use sales_order_id for the join
            ->select('sales_products.id','sales_products.product_name', 'sales_order_items.quantity')
            ->get();



            if ($order && !array_key_exists($this->selectedOrder, $this->ordersForTruck)) {
                // Add order along with products to the list
                $this->ordersForTruck[$this->selectedOrder] = [
                    'order' => $order,
                    'products' => $products
                ];
            }

            $this->selectedOrder = null; // Reset the dropdown after adding
        }
    }

    public function removeOrderFromTruck($orderId)
    {
        if (isset($this->ordersForTruck[$orderId])) {
            unset($this->ordersForTruck[$orderId]);
        }
    }

    public function store()
    {

         // Check if at least one order is assigned to the truck
         if (count($this->ordersForTruck) === 0) {
            // Add validation error for the orders
            $this->addError('ordersForTruck', 'At least one order must be assigned to the truck.');
            return;
        }


        // Validate the inputs
        $this->validate();

        // Check if the form is already being submitted
        if ($this->isSubmitting) {
            return;
        }

        // Set the submission state to true
        $this->isSubmitting = true;

        // Start a transaction for safety in case of errors
        DB::beginTransaction();

        try {
            // Fetch the existing weighbridge_outward record and update it
            $outward = WeighbridgeOutward::findOrFail($this->selectedVehicle);
            $outward->second_weight = $this->second_weight_s;
            $outward->net_weight = $this->net_weight_s; // Calculate net weight
            $outward->second_weight_datetime = Carbon::parse($this->second_weight_s_datetime)->setTimezone('Asia/Karachi');
            $outward->status = 1; // Mark as completed
            $outward->company_id = session('company_id');
            $outward->updated_by = Auth::id();
            $outward->save();

            // Save the orders for this outward record into the weighbridge_outward_order table
            foreach ($this->ordersForTruck as $orderId => $orderDetails) {

                //dd();
                DB::table('weighbridge_outward_order')->insert([
                    'weighbridge_outward_id' => $outward->id,
                    'sales_order_id' => $orderId,
                    'order_weight' => $orderDetails['products']->sum('quantity'), // Assuming 'quantity' is the weight per order
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update the sales order status to "delivered" in the sales_orders table
                DB::table('sales_orders')
                    ->where('id', $orderId)
                    ->update(['status' => 'delivered']);


                /* for days */
                $mycustomerDetail = CustomerDetail::where('account_id', $orderDetails['order']->customer_id)->first();


                // Step 1: Generate the Invoice for each order
                // Create the invoice with status "draft"
                $invoiceData = [
                    'sales_order_id' => $orderId, // Link each invoice to the respective order
                    'invoice_number' => $this->generateInvoiceNumber(), // Implement a method to generate invoice number
                    'invoice_date' => now(),
                    'invoice_due_days' => $mycustomerDetail->payment_terms,
                    'customer_id' => $orderDetails['order']->customer_id,
                    'status' => 'draft', // Invoice in draft status
                    'company_id' => session('company_id'),
                    'is_weighbridge' => 1,
                    'financial_year_id' => 1, // Assuming you have this property
                    'created_by' => Auth::id(),
                ];

                // Create the invoice for the current order
                $invoice = SalesInvoice::create($invoiceData);

                // Step 2: Generate the Invoice Items for each order
                // Save products for the order in the invoice



                foreach ($orderDetails['products'] as $product) {

                    SalesInvoiceItem::create([
                        'sales_invoice_id' => $invoice->id,
                        'product_id' => $product->id,
                        'quantity' => $product->quantity,
                        'unit_price' => 0, // Assuming price is available in order details
                        'net_amount' => 0,
                        'discount_rate' => 0, // Assuming no discount for now, update as needed
                        'discount_amount' => 0,
                        'amount_excl_tax' => 0,
                        'sales_tax_rate' => 0, // Update with actual tax if needed
                        'sales_tax_amount' => 0, // Update with actual tax if needed
                        'further_sales_tax_rate' => 0,
                        'further_sales_tax_amount' =>0,
                        'amount_incl_tax' => 0, // Assuming no tax for now
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            // Commit the transaction
            DB::commit();

            // Flash success message
            session()->flash('message', 'Second Weight and Orders assigned successfully.');

            // Reset the form fields
            $this->resetFields();
            $this->dispatch('saved');

        } catch (\Exception $e) {
            // If there's any error, rollback the transaction
            DB::rollBack();

            // Add an error message
            $this->addError('transaction', 'Something went wrong while saving the second weight and orders.'

         . $e->getMessage() );
        }
    }

    public function resetFields()
    {
        $this->second_weight_s_datetime = Carbon::now()->format('Y-m-d\TH:i');
        $this->second_weight_s = '';
        $this->selectedVehicle = '';
        $this->ordersForTruck = [];
        $this->driveroption= '';
        $this->driveroption= '';
        $this->driver_name= '';
        $this->driver_mobile= '';
        $this->first_weight_s= '';
        $this->second_weight_s= '';
        $this->net_weight_s= '';

    }

    public function render()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session

        // Fetch vehicles that haven't been weighed for second weight and filter by company_id
        $vehicles = WeighbridgeOutward::whereNull('second_weight')
            ->where('company_id', $companyId) // Filter by company_id
            ->get();

        // Fetch sales orders that are not yet delivered, along with related customer and farm details, and filter by company_id
        $orders = DB::table('sales_orders')
            ->leftJoin('chart_of_accounts', 'sales_orders.customer_id', '=', 'chart_of_accounts.id')
            ->select(
                'sales_orders.id as order_id',
                'sales_orders.order_number',
                'sales_orders.status as order_status',
                'chart_of_accounts.name as customer_name',
                'sales_orders.farm_name'
            )
            ->where('sales_orders.status', '!=', 'delivered')
            ->where('sales_orders.status', '!=', 'pending')
            ->where('sales_orders.status', '!=', 'canceled')
            ->where('sales_orders.created', '=', 0)
            ->where('sales_orders.company_id', $companyId) // Filter by company_id
            ->get();

        return view('livewire.weighbridge.outwards.secondweight-manager', [
            'vehicles' => $vehicles,
            'orders' => $orders,
        ]);
    }


    // This method triggers when the user selects a vehicle
    public function updatedSelectedVehicle($vehicleId)
    {
        if ($vehicleId) {
            // Fetch the WeighbridgeOutward record for the selected vehicle
            $outward = WeighbridgeOutward::find($vehicleId);

            if ($outward) {
                // Populate the fields with the corresponding data
                $this->truck_number = $outward->truck_number;
                $this->first_weight_s = $outward->first_weight;
                $this->driver_name = $outward->driver_name;
                $this->driver_mobile = $outward->driver_mobile;
                $this->driveroption = $outward->driveroption;
            }
        } else {
            // Reset fields if no vehicle is selected
            $this->truck_number = '';
            $this->first_weight_s = '';
            $this->driver_name = '';
            $this->driver_mobile = '';
            $this->driveroption = '';
        }

        $this->dispatch('initializeSocketForWeight_s'); // This event triggers the socket initialization
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


}
