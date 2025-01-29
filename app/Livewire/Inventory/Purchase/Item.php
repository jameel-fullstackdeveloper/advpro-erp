<?php

namespace App\Livewire\Inventory\Purchase;

use Livewire\Component;
use App\Models\PurchaseItem;
use App\Models\PurchaseItemGroup;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\Items;
use App\Models\ItemGroup;
use App\Models\PurchaseBillItem;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReturnItem;

use App\Models\SalesOrderItem;
use App\Models\SalesReturnItem;
use App\Models\StockMaterialAdjustment;
use App\Models\ProductionDetail;
use App\Models\SalesInvoiceItem;


class Item extends Component
{
    use WithPagination;

    public $productId, $product_name, $group_id, $quantity, $price, $balance, $financial_year_id, $status, $company_id, $item_type;

    public $isEditMode = false;
    public $itemsPerPage = 50;
    public $searchTerm = '';
    public $can_be_sale = 0;

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
        $this->resetValidation();
        $this->reset();
        $this->isEditMode = false;
        $this->dispatch('showModal_item');
    }

    public function edit($id)
    {
        $product = Items::findOrFail($id);
        $this->productId = $product->id;
        $this->product_name = $product->name;
        $this->group_id = $product->item_group_id;
        $this->price = $product->purchase_price;
        $this->balance = $product->balance;
        $this->can_be_sale = $product->can_be_sale;
        $this->company_id = $product->company_id;

        $this->resetValidation();
        $this->isEditMode = true;
        $this->dispatch('showModal_item');
    }

    public function store()
    {
        $this->validate();

        // Check if productId exists to determine if it's an update or create operation
        $data = [
            'name' => $this->product_name,
            'item_group_id' => $this->group_id,
            'item_type' => 'purchase',
            'purchase_price' => $this->price,
            'balance' => $this->balance,
            'can_be_sale' =>$this->can_be_sale,
            'company_id' => session('company_id'),
            'created_by' => Auth::id(),
        ];

        // If the item is being updated, also set the 'updated_by' field
        if ($this->productId) {
            $data['updated_by'] = Auth::id();
        }

        // Use updateOrCreate method
        Items::updateOrCreate(
            ['id' => $this->productId],
            $data
        );

        session()->flash('message', $this->productId ? 'Item updated successfully.' : 'Item added successfully.');
        $this->dispatch('hideModal_item');
        $this->resetInputFields();
        $this->resetValidation();
    }

    public function deleteProduct($id)
    {
        $product = PurchaseItem::findOrFail($id);
        $product->delete();

        session()->flash('message', 'Item deleted successfully.');
    }

    public function render()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session
        $searchTerm = '%' . $this->searchTerm . '%';

        // Fetch products filtered by company_id and search term
        $products = Items::with('itemGroup')
            ->where('company_id', $companyId) // Filter by company_id
            ->where('item_type', 'purchase') // Filter by company_id
            ->where('name', 'like', $searchTerm) // Apply search filter
            ->paginate($this->itemsPerPage);

        return view('livewire.inventory.purchase.item', ['products' => $products]);
    }


    private function resetInputFields()
    {
        $this->productId = null;
        $this->item_name = '';
        $this->group_id = '';
        $this->price = '';
        $this->balance = '';
        $this->can_be_sale = 0;
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
