<?php

namespace App\Livewire\Weighbridge\Outwards;

use Livewire\Component;
use App\Models\CustomerDetail;
use App\Models\ChartOfAccount;
use App\Models\SalesOrder;
use App\Models\WeighbridgeOutward;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Company;


class OutwardsManager extends Component
{
    use WithPagination; // Use the WithPagination trait

    public $sales_order_id, $truck_number,$sales_invoice_id, $customer_id,$first_weight, $second_weight, $net_weight;

    public $searchTerm;
    public $itemsPerPage = 50;
    public $startDate;
    public $endDate;
    public $customerFilter;
    public $productFilter;


    protected $queryString = ['searchTerm', 'startDate', 'endDate', 'customerFilter', 'productFilter'];


    public function mount()
    {
        abort_if(!auth()->user()->can('weighbridge view'), 403);
        // Initialize filters with default values
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
    }


    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function updated($propertyName)
    {
        $this->resetPage();
    }



    protected $rules = [
        'truck_number' => 'required',
        'first_weight' => 'required|numeric',
        'second_weight' => 'nullable|numeric',
        'sales_order_id' => 'required|exists:sales_orders,id',
    ];

    public function calculateNetWeight()
    {
        if ($this->first_weight && $this->second_weight) {
            $this->net_weight = $this->second_weight - $this->first_weight;
        }
    }

    public function submit()
    {
        $this->validate();

        WeighbridgeOutward::create([
            'sales_order_id' => $this->sales_order_id,
            'truck_number' => $this->truck_number,
            'first_weight' => $this->first_weight,
            'second_weight' => $this->second_weight,
            'net_weight' => $this->net_weight,
        ]);

        session()->flash('message', 'Weighbridge Outwards record saved successfully.');
    }


    public function render()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session

        // Query logic with filters
        $outwards = DB::table('weighbridge_outwards')
            ->join('weighbridge_outward_order', 'weighbridge_outwards.id', '=', 'weighbridge_outward_order.weighbridge_outward_id')
            ->join('sales_orders', 'weighbridge_outward_order.sales_order_id', '=', 'sales_orders.id')
            ->join('chart_of_accounts', 'sales_orders.customer_id', '=', 'chart_of_accounts.id')
            ->join('sales_order_items', 'sales_orders.id', '=', 'sales_order_items.sales_order_id')
            ->join('sales_products', 'sales_order_items.product_id', '=', 'sales_products.id')
            ->select(
                'weighbridge_outwards.id as outward_id',
                'weighbridge_outwards.truck_number',
                'weighbridge_outwards.first_weight_datetime',
                'weighbridge_outwards.second_weight_datetime',
                'weighbridge_outwards.first_weight',
                'weighbridge_outwards.second_weight',
                'weighbridge_outwards.net_weight',
                'weighbridge_outwards.driver_name',
                'weighbridge_outwards.driver_mobile',
                'sales_orders.order_number',
                'sales_orders.order_date',
                'sales_orders.farm_name',
                'sales_orders.farm_address',
                'sales_orders.farm_supervisor_mobile',
                'chart_of_accounts.name as customer_name',
                'weighbridge_outward_order.order_weight',
                'sales_products.product_name',
                'sales_order_items.quantity'
            )
            ->where('weighbridge_outwards.company_id', $companyId) // Filter by company_id for weighbridge_outwards
            ->where('sales_orders.company_id', $companyId) // Filter by company_id for sales_orders
            ->when($this->startDate, function ($query) {
                return $query->whereDate('weighbridge_outwards.first_weight_datetime', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                return $query->whereDate('weighbridge_outwards.first_weight_datetime', '<=', $this->endDate);
            })
            ->when($this->customerFilter, function ($query) {
                return $query->where('chart_of_accounts.id', $this->customerFilter);
            })
            ->when($this->productFilter, function ($query) {
                return $query->where('sales_products.id', $this->productFilter);
            })
            ->where(function ($query) {
                $query->where('weighbridge_outwards.truck_number', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('sales_orders.order_number', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('chart_of_accounts.name', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('sales_products.product_name', 'like', '%' . $this->searchTerm . '%');
            })
            ->orderBy('weighbridge_outwards.first_weight_datetime', 'desc') // Order by newest first
            ->paginate($this->itemsPerPage);

        // Filter customers by company_id
        $customers = ChartOfAccount::where('is_customer_vendor', 'customer')
            ->where('company_id', $companyId) // Filter by company_id for customers
            ->get();

        // Filter products by company_id
        $products = DB::table('sales_products')
            ->select('id', 'product_name')
            ->where('company_id', $companyId) // Filter by company_id for products
            ->get();

        return view('livewire.weighbridge.outwards.outwards-manager', [
            'outwards' => $outwards,
            'customers' => $customers,
            'products' => $products,
        ]);
    }
}





