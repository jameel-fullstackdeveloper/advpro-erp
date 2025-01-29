<?php

namespace App\Livewire\Inventory\Adjustments;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StockFinishedAdjustment;
use App\Models\SalesProduct;
use Illuminate\Support\Facades\Auth;

class StockFinishedAdjustmentManager extends Component
{
    use WithPagination;

    // Properties for CRUD operations
    public $adj_date, $material_id, $shortage, $exccess, $created_by, $updated_by, $stockMaterialAdjustmentId;
    public $isEditMode = false;
    public $searchTerm = '';
    public $itemsPerPage = 50;
    public $counts = [];
    public $selected_id;
    public $isOpen = false;
    public $items = []; // Holds the list of items based on the type selected
    public $inputType = 'shortage'; // Default input type

    // Pagination settings
    protected $paginationTheme = 'bootstrap';

    // Validation rules
    protected $rules = [
        'adj_date' => 'required|date',
        'material_id' => 'required',
        'shortage' => 'nullable|numeric',
        'exccess' => 'nullable|numeric',

    ];

    // Mount method to initialize properties
    public function mount()
    {
        $this->adj_date = date('Y-m-d'); // Set current date as default

    }

    public function render()
    {
        $stockMaterialAdjustments = StockFinishedAdjustment::query()
        ->with('salesItem') // Eager load the related purchase item
        ->where(function ($query) {
            if ($this->searchTerm) {
                $query->whereHas('salesItem', function($q) {
                    // Adjust this to search on the correct column (item_name or product_name)
                    $q->where('name ', 'like', '%' . $this->searchTerm . '%');
                });
            }
        })
        ->paginate($this->itemsPerPage);

        $this->items = SalesProduct::all();

        return view('livewire.inventory.adjustments.showfinished', [
            'stockMaterialAdjustments' => $stockMaterialAdjustments,
            'counts' => $this->counts,
            'items' => $this->items,
        ]);
    }


    // Create or Update stock material adjustment
    public function store()
    {
        $this->validate();

        if ($this->isEditMode) {
            $stockMaterialAdjustment = StockFinishedAdjustment::find($this->stockMaterialAdjustmentId);
            $stockMaterialAdjustment->update([
                'adj_date' => $this->adj_date,
                'material_id' => $this->material_id,
                'shortage' => $this->shortage,
                'exccess' => $this->exccess,
                'created_by' => $this->created_by,
                'updated_by' => $this->updated_by ?? $this->created_by,
            ]);
        } else {
            StockFinishedAdjustment::create([
                'adj_date' => $this->adj_date,
                'material_id' => $this->material_id,
                'shortage' => $this->shortage,
                'exccess' => $this->exccess,
                'created_by' => Auth::id(),
                //'updated_by' => $this->updated_by ?? $this->created_by,
            ]);
        }

        session()->flash('message', $this->isEditMode ? 'Stock material adjustment updated successfully.' : 'Stock material adjustment created successfully.');
        $this->closeModal();
        $this->resetInputFields();
    }

    // Reset fields after save or cancel
    public function resetFields()
    {
        $this->adj_date = '';
        $this->material_id = '';
        $this->shortage = '';
        $this->exccess = '';
        $this->created_by = '';
        $this->updated_by = '';
        $this->isEditMode = false;
        $this->stockMaterialAdjustmentId = null;
        $this->items = []; // Reset the items list
    }

    // Open modal for editing or creating new adjustment
    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    // Set data for editing
    public function edit($id)
    {
        $stockMaterialAdjustment = StockFinishedAdjustment::find($id);

        $this->stockMaterialAdjustmentId = $stockMaterialAdjustment->id;
        $this->adj_date = $stockMaterialAdjustment->adj_date;
        $this->material_id = $stockMaterialAdjustment->material_id;
        $this->shortage = $stockMaterialAdjustment->shortage;
        $this->exccess = $stockMaterialAdjustment->exccess;
        $this->created_by = $stockMaterialAdjustment->created_by;
        $this->updated_by = $stockMaterialAdjustment->updated_by;
        $this->selected_id = $id;
        $this->isEditMode = true;
        $this->openModal();
    }

    // Delete a stock material adjustment
    public function delete($id)
    {
        $stockMaterialAdjustment = StockFinishedAdjustment::find($id);
        $stockMaterialAdjustment->delete();
        session()->flash('message', 'Stock material adjustment deleted successfully.');
    }

    // Handle pagination
    public function updatingItemsPerPage()
    {
        $this->resetPage();
    }

    // Filter by category logic (if needed for other categorization)
    public function filterByCategory($category)
    {
        $this->selectedCategory = $category;
        $this->resetPage();
    }

    public function openModal()
    {
        $this->isOpen = true;
        $this->dispatch('showModal_stock_adj');
    }

    public function closeModal()
    {
        $this->resetInputFields();
        $this->resetValidation(); // Clear validation error messages
        $this->isOpen = false;
        $this->dispatch('hideModal_stock_adj');
    }


    private function resetInputFields()
    {
        $this->adj_date = date('Y-m-d');
        $this->type = null;
        $this->material_id = null;
        $this->shortage = 0;
        $this->exccess = 0;
        $this->selected_id = null;
    }

    public function confirmDeletion($id)
    {
        // This method can be used to confirm and trigger the deletion
        //$this->delete($id);

        $this->dispatch('swal:confirm-deletion', voucherId: $id);

    }
}
