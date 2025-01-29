<?php

namespace App\Livewire\Company;

use Livewire\Component;
use App\Models\Company;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\ChartOfAccount;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class CompanyManager extends Component
{
    use WithPagination, WithFileUploads;

    public $companyId;
    public $name;
    public $address;
    public $email;
    public $phone;
    public $abv;
    public $strn;
    public $ntn;
    public $type;
    public $avatar;
    public $created_by;
    public $updated_by;
    public $isEditMode = false;
    public $itemsPerPage = 10;
    public $searchTerm = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'abv' => 'required|string|max:3',
        'email' => 'required|email|unique:companies,email',
        'phone' => 'nullable|string',
        'address' => 'nullable|string',
        'type' => 'nullable|string',
        'avatar' => 'nullable|image|max:1024',
    ];

    public function mount()
    {
        // Initialize data if needed

        abort_if(!auth()->user()->hasRole('Administrator') && !auth()->user()->hasRole('Super Admin'), 403);


    }

    public function create()
    {
        $this->resetFields();
        $this->isEditMode = false;
        $this->dispatch('showModal');
    }

    public function edit($id)
    {
        $company = Company::findOrFail($id);
        $this->companyId = $company->id;
        $this->name = $company->name;
        $this->address = $company->address;
        $this->email = $company->email;
        $this->phone = $company->phone;
        $this->abv = $company->abv;
        $this->strn = $company->strn;
        $this->ntn = $company->ntn;
        $this->type = $company->type;
        $this->avatar = null; // Avatar will be handled if updated
        $this->isEditMode = true;
        $this->dispatch('showModal');
    }

    public function store()
    {
        // Validation
        $this->validate([
            'name' => 'required|string|max:255',
            'abv' => 'required|string|max:3',
            'email' => 'required|email|unique:companies,email,' . $this->companyId, // Exclude the current company's email from the unique check
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'type' => 'required|string',
            'avatar' => 'nullable|image|max:1024',
        ]);

        /* // Handle avatar upload
        if ($this->avatar) {
            $avatarPath = $this->avatar->store('avatars', 'public');
        } else {
            $avatarPath = $this->companyId
                ? Company::find($this->companyId)->avatar
                : null;
        }*/

        // Handle avatar upload if a new file is uploaded
        if ($this->avatar) {
            // Check if a customer ID exists, meaning it's an update
            if ($this->companyId) {
                $currentCustomer = Company::find($this->companyId);
                if ($currentCustomer && $currentCustomer->avatar) {
                    // Delete the existing avatar from DigitalOcean Spaces
                    \Storage::disk('spaces')->delete($currentCustomer->avatar);
                }
            }

            // Upload new avatar to DigitalOcean Spaces in the sfpro folder
            $avatarPath = $this->avatar->store('afzpro', 'spaces');
        } else {
            // Retain old avatar if no new avatar is uploaded
            $avatarPath = $this->companyId
                ? Company::find($this->companyId)->avatar
                : null;
        }


        // Create or update company
        Company::updateOrCreate(
            ['id' => $this->companyId],
            [
                'name' => $this->name,
                'address' => $this->address,
                'email' => $this->email,
                'phone' => $this->phone,
                'abv' => $this->abv,
                'strn' => $this->strn,
                'ntn' => $this->ntn,
                'type' => $this->type,
                'avatar' => $avatarPath,
                'created_by' => $this->isEditMode ? Company::find($this->companyId)->created_by : Auth::id(),
                'updated_by' => Auth::id(),
            ]
        );

        // Reset fields
        $this->resetFields();

        // Flash success message and close modal
        session()->flash('message', $this->companyId ? 'Company updated successfully.' : 'Company added successfully.');
        $this->dispatch('hideModal');
    }


    public function confirmDeletion($id)
    {
        $this->deleteCompany($id);
    }

    public function deleteCompany($id)
    {
         // Check if there are any associated vouchers with this cost center
         $voucherCount = Voucher::where('segment_id', $id)->count();

         // Check if there are any associated chart of accounts with this cost center
         $accountCount = ChartOfAccount::where('company_id', $id)->count();

         if ($voucherCount > 0 || $accountCount > 0) {
             // If there are associated records, prevent deletion and show an error message
             session()->flash('error', 'Cannot delete Segment , as it has associated vouchers or accounts.');
             return;
         }

        Company::findOrFail($id)->delete();
        session()->flash('message', 'Segment  deleted successfully.');
    }

    public function render()
    {
        $searchTerm = '%' . $this->searchTerm . '%';
        $companies = Company::where('name', 'like', $searchTerm)
            ->orWhere('email', 'like', $searchTerm)
            ->paginate($this->itemsPerPage);

        return view('livewire.company.company-manager', ['companies' => $companies]);
    }

    private function resetFields()
    {
        $this->companyId = null;
        $this->name = '';
        $this->address = '';
        $this->email = '';
        $this->phone = '';
        $this->abv = '';
        $this->strn = '';
        $this->ntn = '';
        $this->type = '';
        $this->avatar = null;
    }
}
