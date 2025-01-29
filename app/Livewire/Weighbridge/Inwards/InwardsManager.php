<?php

namespace App\Livewire\Weighbridge\Inwards;

use Livewire\Component;
use App\Models\CustomerDetail;
use App\Models\ChartOfAccount;
use App\Models\SalesOrder;
use App\Models\WeighbridgeOutward;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InwardsManager extends Component
{
    use WithPagination;

    public $purchase_order_id, $truck_number,$sales_invoice_id, $customer_id,$first_weight, $second_weight, $net_weight;

    public $searchTerm;
    public $itemsPerPage = 50;


    protected $queryString = ['searchTerm'];

    public function mount()
    {
        abort_if(!auth()->user()->can('weighbridge view'), 403);

    }
    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    protected $rules = [
        'truck_number' => 'required',
        'first_weight' => 'required|numeric',
        'second_weight' => 'nullable|numeric',
        'purchase_order_id' => 'required|exists:purchase_orders,id',
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
            'purchase_order_id' => $this->purchase_order_id,
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

        // Manually join the tables to get the necessary data, filtered by company_id
        $outwards = DB::table('weighbridge_inwards')
            ->join('weighbridge_inward_orders', 'weighbridge_inwards.id', '=', 'weighbridge_inward_orders.weighbridge_inward_id')
            ->join('purchase_orders', 'weighbridge_inward_orders.purchase_order_id', '=', 'purchase_orders.id')
            ->join('chart_of_accounts', 'purchase_orders.vendor_id', '=', 'chart_of_accounts.id') // Assuming customer_id refers to chart_of_accounts
            ->join('purchase_order_items', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id') // Join sales order items
            ->join('purchase_items', 'purchase_order_items.product_id', '=', 'purchase_items.id') // Join purchase_items using product_id
            ->select(
                'weighbridge_inwards.id as outward_id',
                'weighbridge_inwards.truck_number',
                'weighbridge_inwards.first_weight_datetime',
                'weighbridge_inwards.second_weight_datetime',
                'weighbridge_inwards.first_weight',
                'weighbridge_inwards.second_weight',
                'weighbridge_inwards.net_weight',
                'weighbridge_inwards.party_gross_weight',
                'weighbridge_inwards.party_tare_weight',
                'weighbridge_inwards.party_net_weight',
                'purchase_orders.order_number',
                'purchase_orders.order_date',
                'chart_of_accounts.name as customer_name',
                'weighbridge_inward_orders.order_weight',
                'purchase_items.item_name',  // Fetch product name
                'purchase_order_items.quantity'
            )
            ->where('weighbridge_inwards.company_id', $companyId) // Filter by company_id for weighbridge_inwards
            ->where(function ($query) {
                $query->where('weighbridge_inwards.truck_number', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('purchase_orders.order_number', 'like', '%' . $this->searchTerm . '%');
            })
            ->paginate($this->itemsPerPage);

        return view('livewire.weighbridge.inwards.inwards-manager', [
            'outwards' => $outwards,
        ]);
    }


}
