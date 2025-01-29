<?php

namespace App\Livewire\Inventory\Sales;

use Livewire\Component;
use App\Models\SalesProduct;
use App\Models\SalesProductGroup;
use App\Models\Items;
use App\Models\ItemGroup;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\PurchaseBillItem;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReturnItem;

use App\Models\SalesOrderItem;
use App\Models\SalesReturnItem;
use App\Models\StockMaterialAdjustment;
use App\Models\ProductionDetail;
use App\Models\SalesInvoiceItem;



class Product extends Component
{
    use WithPagination;

    public $productId, $product_name, $group_id, $quantity, $price, $balance, $financial_year_id, $status, $company_id, $item_type ;
    public $isEditMode = false;
    public $itemsPerPage = 50;
    public $searchTerm = '';

    protected $rules = [
        'product_name' => 'required|string|max:255',
        'group_id' => 'required',
        'price' => 'required|numeric|min:0',
        'balance' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        abort_if(!auth()->user()->can('inventory view'), 403);
    }

    public function create()
    {
        $this->reset();
        $this->isEditMode = false;
        $this->dispatch('showModal_product');
    }

    public function edit($id)
    {
        $product = Items::findOrFail($id);
        $this->productId = $product->id;
        $this->product_name = $product->name;
        $this->group_id = $product->item_group_id;
        $this->price = $product->sale_price;
        $this->balance = $product->balance;
        $this->company_id = $product->company_id;

        $this->resetValidation();
        $this->isEditMode = true;
        $this->dispatch('showModal_product');
    }

    public function store()
    {
        $this->validate();

        // Prepare the data array for create or update
        $data = [
            'name' => $this->product_name,
            'item_group_id' => $this->group_id,
            'item_type' => 'sale',
            'sale_price' => $this->price,
            'balance' => $this->balance,
            'company_id' => session('company_id'),
            'created_by' => Auth::id(),
        ];

        // If it's an update, add the 'updated_by' field
        if ($this->productId) {
            $data['updated_by'] = Auth::id();
        }

        // Perform update or create
        Items::updateOrCreate(
            ['id' => $this->productId],
            $data
        );

        session()->flash('message', $this->productId ? 'Product updated successfully.' : 'Product added successfully.');
        $this->dispatch('hideModal_product');
        $this->resetInputFields();
        $this->resetValidation();
    }





    public function render()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session
        $searchTerm = '%' . $this->searchTerm . '%';

        // Fetch products filtered by company_id and search term
        $products = Items::with('itemGroup')
            ->where('company_id', $companyId) // Filter by company_id
            ->where('item_type', 'sale') // Filter by company_id
            ->where('name', 'like', $searchTerm) // Apply search filter
            ->paginate($this->itemsPerPage);

        return view('livewire.inventory.sales.product', ['products' => $products]);
    }


    private function resetInputFields()
    {
        $this->productId = null;
        $this->product_name = '';
        $this->group_id = '';
        $this->quantity = '';
        $this->price = '';
        $this->balance = '';
        $this->financial_year_id = null;
        $this->status = null;
        $this->company_id = null;
        $this->isEditMode = false;
    }



    public function deleteProdcut($id)
    {
        $product = Items::findOrFail($id);

    // Check if the product is associated with any of the mentioned tables
    $isAssociated = PurchaseBillItem::where('product_id', $product->id)->exists() ||
                    PurchaseOrderItem::where('product_id', $product->id)->exists() ||
                    PurchaseReturnItem::where('product_id', $product->id)->exists() ||
                    SalesInvoiceItem::where('product_id', $product->id)->exists() ||
                    SalesOrderItem::where('product_id', $product->id)->exists() ||
                    SalesReturnItem::where('product_id', $product->id)->exists() ||
                    StockMaterialAdjustment::where('material_id', $product->id)->exists() ||
                    ProductionDetail::where('production_id', $product->id)->exists() ||
                    ProductionDetail::where('raw_material_id', $product->id)->exists();

    // If the product is associated, do not delete
    if ($isAssociated) {
        session()->flash('error', 'Product cannot be deleted as it is associated with other records.');
        return;
    }

    // Proceed to delete if no associations found
    $product->delete();

    session()->flash('message', 'Product deleted successfully.');
    }

    public function confirmDeletion($id)
    {

        $this->dispatch('swal:confirm-deletion', voucherId: $id);

    }




}
