<?php

namespace App\Livewire\Accounting\Chartofaccount;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\ChartOfAccountsType;
use App\Models\User;


class Coatype extends Component
{
     use WithPagination;

    public $name;
    public $category;
    public $selected_id;
    public $isOpen_coa_head = false;
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

        $query = ChartOfAccountsType::where('company_id', $companyId); // Filter by company_id

        // Add search functionality
        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('category', 'like', '%' . $this->searchTerm . '%');
            });
        }

        return view('livewire.accounting.chartofaccount.coatype', [
            'accountTypes' => $query->paginate($this->itemsPerPage),
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
            'category' => 'required|string|in:Assets,Liabilities,Expenses,Revenue,Equity',

        ]);

        ChartOfAccountsType::create([
            'name' => $this->name,
            'category' => $this->category,
            'company_id' => session('company_id'),
            'created_by' => Auth::id(),
        ]);

        session()->flash('message', 'Created successfully.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $accountHead = ChartOfAccountsType::findOrFail($id);
        $this->selected_id = $id;
        $this->name = $accountHead->name;
        $this->category = $accountHead->category;

        $this->openModal();
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string',
            'category' => 'required|string|in:Assets,Liabilities,Expenses,Revenue,Equity',

        ]);

        if ($this->selected_id) {
            $accountHead = ChartOfAccountsType::findOrFail($this->selected_id);
            $accountHead->update([
                'name' => $this->name,
                'category' => $this->category,
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
        $this->dispatch('showModal_type');
    }

    public function closeModal()
    {
        $this->resetInputFields();
        $this->resetValidation(); // Clear validation error messages
        $this->isOpen_coa_head = false;
        $this->dispatch('hideModal_type');
    }

    public function delete($id)
    {
        $accountHead = ChartOfAccountsType::findOrFail($id);

         // Check if there are any associated ChartOfAccountGroup records
        if ($accountHead->chartOfAccountGroups()->exists()) {
            session()->flash('error', 'Cannot delete because it has child groups.');
            return;
        }

        $accountHead->delete();
        session()->flash('message', 'Deleted successfully.');
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->category = '';
        $this->selected_id = null;
    }

    public function confirmDeletion($id)
    {
        // This method can be used to confirm and trigger the deletion
        $this->delete($id);
    }
}
