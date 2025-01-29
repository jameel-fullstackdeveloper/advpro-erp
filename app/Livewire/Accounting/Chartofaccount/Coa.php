<?php

namespace App\Livewire\Accounting\Chartofaccount;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\Costcenter;
use App\Models\VoucherDetail;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountGroup;
use App\Models\CustomerDetail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ChartOfAccountsExport;

use Illuminate\Support\Facades\DB;

class Coa extends Component
{
    use WithPagination, WithFileUploads; // Use the trait

    public $name;
    public $group_id;
    public $is_customer_vendor;
    public $balance=0;
    public $drcr;
    public $segment_id; // Added segment_id for compa
    public $cost_center_id;
    public $selected_id;
    public $isOpen = false;

    public $segments = [];
    public $costCenters = [];

    //filters
    public $searchTerm = '';
    public $itemsPerPage = 50;
    public $selectedCategory = null; // Show all accounts by default

    protected $paginationTheme = 'bootstrap'; // Use Bootstrap pagination

     // Export the data
     public function export()
     {
         return Excel::download(new ChartOfAccountsExport($this->selectedCategory, $this->searchTerm), 'chart_of_accounts.xlsx');
     }

    public function mount()
    {
        abort_if(!auth()->user()->can('accounting chart of account view'), 403);

         // Fetch all companies (segments) for the dropdown
         $this->segments = Company::all();

        // Fetch cost centers
         //  $this->costCenters = Costcenter::where('segment_id', session('company_id'))->get();
     }

     public function updatedSegmentId($value)
    {
        // Fetch cost centers for the selected company (segment)
        $this->costCenters = Costcenter::where('segment_id', $value)->get();
        $this->cost_center_id = null; // Reset cost center selection
    }


    public function render()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session
        $user = auth()->user(); // Get the authenticated user

        // Query logic for ChartOfAccount with filters
        //$query = ChartOfAccount::where('company_id', $companyId); // Filter by company_id

        $query = ChartOfAccount::query();

        // Exclude records where is_customer_vendor is 'customer', 'vendor', 'sales_broker', or 'purchase_broker'
        $query->where(function ($q) {
           // $q->whereNull('is_customer_vendor')  // Include records where is_customer_vendor is null
           // ->orWhereNotIn('is_customer_vendor', ['customer', 'vendor', 'sales_broker', 'purchase_broker']);  // Exclude 'customer', 'vendor', 'sales_broker', and 'purchase_broker'
        });

        // Filter by selected category if it's not null
        if ($this->selectedCategory) {
            $query->whereHas('chartOfAccountGroup', function ($q) {
                $q->whereHas('chartOfAccountsType', function ($q) {
                    $q->where('category', $this->selectedCategory);
                });
            });
        }

        // Search filter
        if ($this->searchTerm) {
            $query->where('name', 'like', '%' . $this->searchTerm . '%')
                ->orWhereHas('chartOfAccountGroup.chartOfAccountsType', function ($q) {
                    $q->where('category', 'like', '%' . $this->searchTerm . '%');
                });
        }

        // Fetch accounts and counts for tabs
        $accounts = $query->paginate($this->itemsPerPage);

        // Fetch account heads
        $accountHeads = ChartOfAccountGroup::all(); // Filter by company_id

        $counts = [
            'assets' => ChartOfAccount::whereHas('chartOfAccountGroup.chartOfAccountsType', function ($q) {
                $q->where('category', 'Assets');
            })->where(function ($q) {
                $q->whereNull('is_customer_vendor')
                    ->orWhereNotIn('is_customer_vendor', ['customer', 'vendor', 'sales_broker', 'purchase_broker']);
            })->count(),

            'liabilities' => ChartOfAccount::whereHas('chartOfAccountGroup.chartOfAccountsType', function ($q) {
                $q->where('category', 'Liabilities');
            })->where(function ($q) {
                $q->whereNull('is_customer_vendor')
                    ->orWhereNotIn('is_customer_vendor', ['customer', 'vendor', 'sales_broker', 'purchase_broker']);
            })->count(),

            'equity' => ChartOfAccount::whereHas('chartOfAccountGroup.chartOfAccountsType', function ($q) {
                $q->where('category', 'Equity');
            })->where(function ($q) {
                $q->whereNull('is_customer_vendor')
                    ->orWhereNotIn('is_customer_vendor', ['customer', 'vendor', 'sales_broker', 'purchase_broker']);
            })->count(),

            'revenue' => ChartOfAccount::whereHas('chartOfAccountGroup.chartOfAccountsType', function ($q) {
                $q->where('category', 'Revenue');
            })->where(function ($q) {
                $q->whereNull('is_customer_vendor')
                    ->orWhereNotIn('is_customer_vendor', ['customer', 'vendor', 'sales_broker', 'purchase_broker']);
            })->count(),

            'expenses' => ChartOfAccount::whereHas('chartOfAccountGroup.chartOfAccountsType', function ($q) {
                $q->where('category', 'Expenses');
            })->where(function ($q) {
                $q->whereNull('is_customer_vendor')
                    ->orWhereNotIn('is_customer_vendor', ['customer', 'vendor', 'sales_broker', 'purchase_broker']);
            })->count(),
        ];

        return view('livewire.accounting.chartofaccount.coa', [
            'accounts' => $accounts,
            'accountHeads' => $accountHeads,
            'counts' => $counts,
            'costCenters' => $this->costCenters,
        ]);
    }



    public function filterByCategory($category)
    {
        $this->selectedCategory = $category;
        $this->resetPage(); // Reset pagination to the first page when changing filters
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
        $this->dispatch('showModal_account');
    }

    public function closeModal()
    {
        $this->resetInputFields();
        $this->resetValidation(); // Clear validation error messages
        $this->isOpen = false;
        $this->dispatch('hideModal_account');
    }

    /*public function store()
    {
        $this->validate([
            'name' => 'required|string',
            'group_id' => 'required|integer',
            'segment_id' => 'required|integer',
            //'cost_center_id' => 'required|integer',
            'balance' => 'required|numeric',
            'drcr' => 'required|string',
        ]);

        $chartOfAccount = ChartOfAccount::create([
            'name' => $this->name,
            'group_id' => $this->group_id,
            'company_id' => $this->segment_id,
            'is_customer_vendor' => $this->is_customer_vendor,
            'created_by' => Auth::id(),
            'balance' => $this->balance,
            'drcr' => $this->drcr,

        ]);

        // If the account is a customer or vendor, create CustomerDetail
        if (in_array($this->is_customer_vendor, ['customer', 'vendor'])) {
            CustomerDetail::create([
                'account_id' => $chartOfAccount->id, // Link to the created ChartOfAccount
                'created_by' => Auth::id(),
                // Include other fields as required for the CustomerDetail
            ]);
        }

        session()->flash('message', 'Account created successfully.');
        $this->closeModal();
        $this->resetInputFields();
    }*/

    public function store()
    {
        $this->validate([
            'name' => 'required|string',
            'group_id' => 'required|integer',
            'segment_id' => 'required|integer',
            'balance' => 'required|numeric',
            'drcr' => 'required|string',
        ]);

        DB::beginTransaction(); // Start transaction

        try {

            $isCustomerVendor = in_array($this->is_customer_vendor, ['customer', 'vendor']) ? $this->is_customer_vendor : null;

            // Create ChartOfAccount
            $chartOfAccount = ChartOfAccount::create([
                'name' => $this->name,
                'group_id' => $this->group_id,
                'company_id' => $this->segment_id,
                'is_customer_vendor' => $isCustomerVendor,
                'created_by' => Auth::id(),
                'balance' => $this->balance,
                'drcr' => $this->drcr,
            ]);

            // If the account is a customer or vendor, create CustomerDetail
            if (in_array($this->is_customer_vendor, ['customer', 'vendor'])) {
                CustomerDetail::create([
                    'account_id' => $chartOfAccount->id, // Link to the created ChartOfAccount
                    'created_by' => Auth::id(),

                ]);
            }

            DB::commit(); // Commit transaction
            session()->flash('message', 'Account created successfully.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction if an error occurs
            session()->flash('error', 'Failed to create account: ' . $e->getMessage());
        }

        $this->closeModal();
        $this->resetInputFields();
    }


    public function edit($id)
    {
        $account = ChartOfAccount::findOrFail($id);
        $this->selected_id = $id;
        $this->name = $account->name;
        $this->group_id = $account->group_id;
        $this->segment_id = $account->costcenter->segment_id ?? null;
        $this->updatedSegmentId($this->segment_id); // Load cost centers

        $this->is_customer_vendor = $account->is_customer_vendor;
        $this->balance = $account->balance;
        $this->drcr = $account->drcr;


        $this->openModal();
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string',
            'group_id' => 'required|integer',
            'segment_id' => 'required|integer',
            //'cost_center_id' => 'required|integer',
            'balance' => 'required|numeric',
            'drcr' => 'required|string',

        ]);

        $isCustomerVendor = in_array($this->is_customer_vendor, ['customer', 'vendor']) ? $this->is_customer_vendor : null;


        if ($this->selected_id) {
            $account = ChartOfAccount::findOrFail($this->selected_id);
            $account->update([
                'name' => $this->name,
                'is_customer_vendor' => $isCustomerVendor,
                'group_id' => $this->group_id,
                'balance' => $this->balance,
                'company_id' =>  $this->segment_id,
                'drcr' => $this->drcr,
                'updated_by' => Auth::id(),
            ]);


            // Delete the account_id from CustomerDetail if it's a customer or vendor

                    $customerDetail = CustomerDetail::where('account_id', $account->id)->first();
                    if ($customerDetail) {
                        $customerDetail->delete();
                    }

             // If the account is a customer or vendor, create CustomerDetail
             if (in_array($this->is_customer_vendor, ['customer', 'vendor'])) {
                CustomerDetail::create([
                    'account_id' => $account->id, // Link to the created ChartOfAccount
                    'created_by' => Auth::id(),

                ]);
            }


            session()->flash('message', 'Account updated successfully.');
            $this->closeModal();
            $this->resetInputFields();
        }
    }

    public function delete($id)
    {
        // Find the ChartOfAccount by ID
        $chartOfAccount = ChartOfAccount::findOrFail($id);

        // Check if the ChartOfAccount has any associated VoucherDetails with the account_id
        if (VoucherDetail::where('account_id', $chartOfAccount->id)->exists()) {
            session()->flash('error', 'Cannot delete because this account has child records.');
            return;
        }

        ChartOfAccount::findOrFail($id)->delete();

        session()->flash('message', 'Account deleted successfully.');

    }

    private function resetInputFields()
    {
        $this->name = null;
        $this->group_id = null;
        $this->segment_id = null;
        $this->is_customer_vendor = null;
        $this->balance = 0;
        $this->drcr = null;
        $this->selected_id = null;
    }

    public function confirmDeletion($id)
    {

        $this->dispatch('swal:confirm-deletion', voucherId: $id);

    }
}

