<?php

namespace App\Livewire\Purchase\Brokers;

use Livewire\Component;
use App\Models\CustomerDetail;
use App\Models\ChartOfAccount;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BrokerDetailManager extends Component
{
    use WithPagination, WithFileUploads ;

    public $customerId;
    public $account_name;
    public $group_id=11; // General Creditor
    public $balance=0;
    public $cnic;
    public $strn;
    public $ntn;
    public $discount=0;
    public $bonus=0;
    public $credit_limit=0;
    public $payment_terms=0;
    public $address;
    public $phone;
    public $email;
    public $avatar;
    public $broker_rate = 0; // Default value for broker rate


    public $isEditMode = false;
    public $itemsPerPage = 50;
    public $searchTerm = '';
    public $selectedCategory = null; // Show all accounts by default

    public $drcr='Cr.';

    public $isSubmitting = false;

    protected $rules = [
        'account_name' => 'required|string|max:255',
        'group_id' => 'required',
        'balance' => 'required|numeric|min:0', // Ensure balance is not negative
        'email' => 'nullable|email',
        'phone' => 'nullable|string',
        'address' => 'nullable|string',
        'cnic' => 'nullable|string',
        'strn' => 'nullable|string',
        'ntn' => 'nullable|string',
        'discount' => 'required|numeric|min:0',
        'bonus' => 'nullable|numeric|min:0',
        'credit_limit' => 'required|numeric|min:0',
        'payment_terms' => 'required|numeric|min:0',
        'avatar' => 'nullable|image|max:1024',
        'drcr' => 'nullable|string',
        'broker_rate' => 'required|numeric|min:0', // Add this line for broker_rate validation

    ];


    public function mount()
    {
        //$this->customers = CustomerDetail::all();
        abort_if(!auth()->user()->can('purchases brokers view'), 403);
    }

    public function create()
    {
        $this->reset();
        $this->isEditMode = false;
        $this->dispatch('showModal_customer');
    }

        public function edit($id)
        {

        $customer = CustomerDetail::with('coaTitle')->findOrFail($id);
        $this->customerId = $customer->id;
        $this->account_name = $customer->coaTitle->name ?? '';
        $this->group_id = $customer->coaTitle->group_id ?? '';
        $this->balance = $customer->coaTitle->balance ?? 0;
        $this->drcr = $customer->coaTitle->drcr ?? 'Dr.';
        $this->cnic = $customer->cnic;
        $this->strn = $customer->strn;
        $this->ntn = $customer->ntn;
        $this->discount = $customer->discount;
        $this->bonus = $customer->bonus;
        $this->credit_limit = $customer->credit_limit;
        $this->payment_terms = $customer->payment_terms;
        $this->address = $customer->address;
        $this->phone = $customer->phone;
        $this->email = $customer->email;
        $this->broker_rate = $customer->broker_rate;

        // Do not assign avatar here, it will be handled if a new file is uploaded
        $this->avatar = null;

        $this->resetValidation();
        $this->isEditMode = true;
        $this->dispatch('showModal_customer');
    }




    public function store()
    {
        $this->isSubmitting = true;

        $this->validate();

         // Handle avatar upload if a new file is uploaded
         if ($this->avatar) {
            // Check if a customer ID exists, meaning it's an update
            if ($this->customerId) {
                $currentCustomer = CustomerDetail::find($this->customerId);
                if ($currentCustomer && $currentCustomer->avatar) {
                    // Delete the existing avatar from DigitalOcean Spaces
                    \Storage::disk('spaces')->delete($currentCustomer->avatar);
                }
            }

            // Upload new avatar to DigitalOcean Spaces in the sfpro folder
            $avatarPath = $this->avatar->store('sfpro', 'spaces');
        } else {
            // Retain old avatar if no new avatar is uploaded
            $avatarPath = $this->customerId
                ? CustomerDetail::find($this->customerId)->avatar
                : null;
        }

        // Find the ChartOfAccount if updating, otherwise null
        $existingAccountId = $this->customerId ? CustomerDetail::find($this->customerId)->account_id : null;

        // Create or update the ChartOfAccount record
        $chartOfAccount = ChartOfAccount::updateOrCreate(
            ['id' => $this->customerId ? CustomerDetail::find($this->customerId)->account_id : null],
            [
                'name' => $this->account_name,
                'group_id' => $this->group_id,
                'balance' => $this->balance,
                'is_customer_vendor' => 'purchase_broker',
                'company_id' => session('company_id'),
                'drcr' => $this->drcr,
                'created_by' => $existingAccountId ? ChartOfAccount::find($existingAccountId)->created_by : Auth::id(), // Keep created_by if updating
                'updated_by' => $existingAccountId ? Auth::id() : null, // Set updated_by if updating
            ]
        );

        // Create or update the CustomerDetail record
        CustomerDetail::updateOrCreate(
            ['id' => $this->customerId],
            [
                'account_id' => $chartOfAccount->id,
                'cnic' => $this->cnic,
                'strn' => $this->strn,
                'ntn' => $this->ntn,
                'discount' => $this->discount,
                'bonus' => $this->bonus,
                'credit_limit' => $this->credit_limit,
                'payment_terms' => $this->payment_terms,
                'address' => $this->address,
                'phone' => $this->phone,
                'email' => $this->email,
                'avatar' => $avatarPath, // Save new avatar or retain the old one
                'broker_rate' => $this->broker_rate,
                'created_by' => $existingAccountId ? ChartOfAccount::find($existingAccountId)->created_by : Auth::id(), // Keep created_by if updating
                'updated_by' => $existingAccountId ? Auth::id() : null, // Set updated_by if updating
            ]
        );

        $this->resetInputFields();
        $this->resetValidation();

        $this->isSubmitting = false;

        session()->flash('message', $this->customerId ? 'Broker updated successfully.' : 'Broker added successfully.');
        $this->dispatch('hideModal_customer');
    }



    public function confirmDeletion($id)
    {
        // This method can be used to confirm and trigger the deletion
        $this->deleteCustomer($id);
    }


    public function deleteCustomer($id)
    {
        DB::transaction(function () use ($id) {
            // Find the customer
            $customer = CustomerDetail::findOrFail($id);

            // Check if the customer has related voucher details by querying the `voucher_details` table
            $voucherExists = DB::table('voucher_details')
                ->where('account_id', $customer->account_id)
                ->exists();

            if ($voucherExists) {
                session()->flash('error', 'Broker cannot be deleted because they have associated vouchers.');
                return;
            }

            // Delete the customer
            $customer->delete();

            // Also delete the related ChartOfAccount record if needed
            ChartOfAccount::where('id', $customer->account_id)->delete();

            session()->flash('message', 'Broker deleted successfully.');
        }, 5); // Retry transaction 5 times in case of deadlock
    }


    public function render()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session
        $searchTerm = '%' . $this->searchTerm . '%';

        // Query brokers and their ChartOfAccount relationships only if the account is for a purchase broker
        $customers = CustomerDetail::whereHas('coaTitle', function($query) use ($companyId) {
                $query->where('is_customer_vendor', 'purchase_broker')
                      ->where('company_id', $companyId); // Filter by company_id
            })
            ->with(['coaTitle' => function($query) use ($companyId) {
                $query->where('is_customer_vendor', 'purchase_broker')
                      ->where('company_id', $companyId); // Ensure it only loads broker accounts for the current company
            }])
            ->where(function($query) use ($searchTerm, $companyId) {
                $query->whereHas('coaTitle', function($query) use ($searchTerm, $companyId) {
                    $query->where('name', 'like', $searchTerm)
                          ->where('company_id', $companyId); // Filter by company_id and search within ChartOfAccount's name
                })
                ->orWhere('strn', 'like', $searchTerm)
                ->orWhere('ntn', 'like', $searchTerm);
            })
            ->paginate($this->itemsPerPage);

        return view('livewire.purchase.brokers.broker-detail-manager', ['customers' => $customers]);
    }


    private function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->address = '';
        $this->cnic = '';
        $this->strn = '';
        $this->ntn = '';
        $this->discount = '';
        $this->bonus = '';
        $this->credit_limit = '';
        $this->payment_terms = '';
        $this->financial_year_id = null;
        $this->company_id = null;
        $this->account_id = '';
        $this->isEditing = false;
    }




}
