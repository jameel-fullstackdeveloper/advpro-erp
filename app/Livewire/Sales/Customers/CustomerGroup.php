<?php

namespace App\Livewire\Sales\Customers;

use Livewire\Component;
use App\Models\CustomerDetail;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountGroup;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class CustomerGroup extends Component
{
    use WithPagination ;

    public $account_id, $name;
    public $isEditMode = false;
    public $itemsPerPage = 50;
    public $searchTerm = '';


    protected $rules = [
        'name' => 'required|string|max:255',
    ];

    public function mount()
    {
        abort_if(!auth()->user()->can('customers view'), 403);

    }

    public function creategroup()
    {
        $this->reset();
        $this->isEditMode = false;
        $this->dispatch('showModal_customergroup');
    }

    public function editgroup($id)
    {

        $customer = ChartOfAccountGroup::findOrFail($id);

        $this->account_id = $customer->id;
        $this->name = $customer->name;

        $this->resetValidation();
        $this->isEditMode = true;
        $this->dispatch('showModal_customergroup');
    }

    public function store()
    {
        $this->validate();


        // Create or update the ChartOfAccount record
        $ChartOfAccountGroup = ChartOfAccountGroup::updateOrCreate(
            ['id' => $this->account_id],
            [
                'name' => $this->name,
                'type_id' => 1,
                'is_customer_vendor' => 'customer',
                'company_id' => session('company_id'),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]
        );

        $this->resetInputFields();
        $this->resetValidation();

        session()->flash('message', $this->account_id ? 'Customer updated successfully.' : 'Customer added successfully.');
        $this->dispatch('hideModal_customergroup');
    }



    public function confirmDeletiongroup($id)
    {
        // This method can be used to confirm and trigger the deletion
        $this->deleteCustomergroup($id);
    }


    public function deleteCustomergroup($id)
    {
       // Find the ChartOfAccountGroup
        $customerGroup = ChartOfAccountGroup::findOrFail($id);

        // Check if there are any records in `chart_of_accounts` using this `group_id`
        $accountExists = DB::table('chart_of_accounts')
            ->where('group_id', $customerGroup->id) // Checking for matching group_id
            ->exists();

        // If records exist in chart_of_accounts with this group_id, prevent deletion
        if ($accountExists) {
            session()->flash('error', 'Customer Group cannot be deleted because it is associated with Customers.');
            return;
        }

        // If no associated ChartOfAccounts exist, proceed with deletion
        $customerGroup->delete();

        session()->flash('message', 'Customer Group deleted successfully.');
    }

    public function render()
    {
        // Search term with wildcards
        $searchTerm = '%' . $this->searchTerm . '%';

        $companyId = session('company_id');

        // Get all groups where 'is_customer_vendor' is 'customer' with search and pagination
        $groups = ChartOfAccountGroup::where('is_customer_vendor', 'customer')
            ->where('name', 'like', $searchTerm) // Apply search filter
            ->where('company_id', $companyId) // Filter by company_id
            ->paginate($this->itemsPerPage);  // Apply pagination

        return view('livewire.sales.customers.customer-group', ['customers' => $groups]);
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->company_id = null;
        $this->account_id = '';
        $this->isEditing = false;
    }





}
