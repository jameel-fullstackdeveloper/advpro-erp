<?php

namespace App\Livewire\Accounting\Chartofaccount;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\ChartOfAccountGroup;
use App\Models\ChartOfAccountsType;
use App\Models\User;

class Coagroup extends Component
{
     use WithPagination;

    public $name;
    public $type_id;
    public $selected_id;
    public $isOpen_coa_head = false;
    public $is_customer_vendor;
    public $searchTerm = '';
    public $itemsPerPage = 50;

    protected $paginationTheme = 'bootstrap'; // Use Bootstrap pagination

    public function mount()
    {
        abort_if(!auth()->user()->can('accounting chart of account view'), 403);

     }

    public function render()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session

        $query = ChartOfAccountGroup::where('company_id', $companyId); // Filter by company_id

        // Filter out records where is_customer_vendor is 'customer' or 'vendor'
        $query->where(function ($q) {
            //$q->whereNull('is_customer_vendor')  // Include records where is_customer_vendor is null
           // ->orWhereNotIn('is_customer_vendor', ['customer', 'vendor']);  // Exclude 'customer' and 'vendor'
        });

        // Add search functionality
        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                ->orWhere('type', 'like', '%' . $this->searchTerm . '%');
            });
        }

        return view('livewire.accounting.chartofaccount.coagroup', [
            'accountHeads' => $query->paginate($this->itemsPerPage),
            'accountTypes' => ChartOfAccountsType::all(), // You can also filter this by company_id if necessary
        ]);
    }


    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string',
            'type_id' => 'required|integer',

        ]);

        ChartOfAccountGroup::create([
            'name' => $this->name,
            'type_id' => $this->type_id,
            'is_customer_vendor' => $this->is_customer_vendor,
            'company_id' => session('company_id'),
            'created_by' => Auth::id(),
        ]);

        session()->flash('message', 'Created successfully.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $accountHead = ChartOfAccountGroup::findOrFail($id);
        $this->selected_id = $id;
        $this->name = $accountHead->name;
        $this->is_customer_vendor = $accountHead->is_customer_vendor;
        $this->type_id = $accountHead->type_id;


        $this->openModal();
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string',
            'type_id' => 'required|integer',

        ]);

        if ($this->selected_id) {
            $accountHead = ChartOfAccountGroup::findOrFail($this->selected_id);
            $accountHead->update([
                'name' => $this->name,
                'type_id' => $this->type_id,
                'is_customer_vendor' => $this->is_customer_vendor,
                'company_id' => session('company_id'),
                'updated_by' => Auth::id(),
            ]);

            session()->flash('message', 'Updated successfully.');
            $this->closeModal();
            $this->resetInputFields();
        }
    }

    public function openModal()
    {
        $this->isOpen_coa_head = true;
        $this->dispatch('showModal_group');
    }

    public function closeModal()
    {
        $this->resetInputFields();
        $this->resetValidation(); // Clear validation error messages
        $this->isOpen_coa_head = false;
        $this->dispatch('hideModal_group');
    }

    public function deletegroup($id)
    {
        $accountHead = ChartOfAccountGroup::findOrFail($id);

        // Check if there are any associated ChartOfAccount records
        if ($accountHead->chartOfAccounts()->exists()) {
            session()->flash('error', 'Cannot delete because it has child accounts.');
            return;
        }

        $accountHead->delete();
        session()->flash('message', 'Deleted successfully.');
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->type_id = '';
        $this->selected_id = null;
        $this->is_customer_vendor = null;
    }

    public function confirmDeletionAccountGroup($id)
    {

        $this->dispatch('swal:confirm-deletion-group', voucherId: $id);

    }
}
