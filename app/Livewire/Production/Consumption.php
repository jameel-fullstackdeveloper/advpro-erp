<?php

namespace App\Livewire\Production;

use Livewire\Component;
use App\Models\Production;
use App\Models\SalesProduct;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Items;

class Consumption extends Component
{
    use WithPagination;

    public $itemsPerPage = 50;
    public $startDate, $endDate;
    public $searchTerm = '';
    public $product_id;


    public function mount()
    {
        abort_if(!auth()->user()->can('inventory view'), 403);
        $this->startDate = Carbon::now()->firstOfMonth()->startOfDay()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
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

        $finished_Products = Items::where('item_type', 'sale')->get(); // Fetch finished products with company_id = 1

        // Fetch purchase items filtered by company_id and search term
        $products = Production::where('company_id', $companyId) // Filter by company_id
        ->where('comments', 'like', $searchTerm) // Apply search filter
        ->whereBetween('production_date', [$this->startDate, $this->endDate])
        ->when($this->product_id, function ($query) {
            return $query->where('product_id', $this->product_id); // Filter by product_id if set
        })
        ->paginate($this->itemsPerPage); // Paginate the results


          // Calculate the sum of each column
        $totalBatchExecuted = $products->sum('lots');
        $totalExpectedBags = $products->sum('defaultbags_perlot');
        $totalShortageBags = $products->sum('short_perlot');
        $totalExcessBags = $products->sum('excess_perlot');
        $totalActualProduction = $products->sum('actual_produced');

        return view('livewire.production.consumption', [
            'productions' => $products,
            'finished_Products' => $finished_Products,
            'totalBatchExecuted' => $totalBatchExecuted,
            'totalExpectedBags' => $totalExpectedBags,
            'totalShortageBags' => $totalShortageBags,
            'totalExcessBags' => $totalExcessBags,
            'totalActualProduction' => $totalActualProduction
        ]);
    }


    public function confirmDeletion($id)
    {
        $this->dispatch('swal:confirm-deletion', voucherId: $id);
    }


    public function deleteProduction($id)
    {
        // Find the production record
        $production = Production::findOrFail($id);

        // Delete related production details (assuming there's a relationship defined)
        $production->details()->delete(); // This deletes all related production details

        // Now delete the production record
        $production->delete();

        // Flash message to notify the user
        session()->flash('message', 'Production and related details deleted successfully.');
    }




}
