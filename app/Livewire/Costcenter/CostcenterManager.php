<?php

namespace App\Livewire\Costcenter;

use Livewire\Component;
use App\Models\Company;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\ChartOfAccount;
use App\Models\Costcenter;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class CostcenterManager extends Component
{
    use WithPagination, WithFileUploads;

    public $companyId;
    public $segment_id;
    public $name;
    public $abv;
    public $address;
    public $description;
    public $opening_date;
    public $closing_date;
    public $status;
    public $created_by;
    public $updated_by;
    public $isEditMode = false;
    public $itemsPerPage = 50;
    public $searchTerm = '';
    public $filtersegmentId = 0;

    protected $rules = [
        'segment_id' => 'required|string',
        'name' => 'required|string|max:255',
        'description' => 'required|string|max:250',
    ];

    public function mount()
    {
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
        $company = Costcenter::findOrFail($id);
        $this->companyId = $company->id;
        $this->name = $company->name;
        $this->abv = $company->abv;
        $this->address = $company->address;
        $this->opening_date = $company->opening_date;
        $this->closing_date = $company->closing_date;
        $this->status = $company->status;
        $this->description = $company->description;
        $this->segment_id = $company->segment_id;
        $this->isEditMode = true;
        $this->dispatch('showModal');
    }

    public function store()
    {
        // Validation
        $this->validate([
            'segment_id' => 'required',
            'name' => 'required|string|max:255',
            'abv' => 'required|string|max:5',
            'description' => 'string|max:200',
            'address' => 'string|max:200',
            'status' => 'required',
        ]);

        // Handle creating or updating the Costcenter record
        Costcenter::updateOrCreate(
            ['id' => $this->companyId],  // Ensure we are updating the record if $this->companyId is set
            [
                'segment_id' => $this->segment_id,
                'name' => $this->name,
                'description' => $this->description,
                'abv' => $this->abv,
                'address' => $this->address,
                'status' => $this->status,
                'created_by' => $this->isEditMode ? Costcenter::find($this->companyId)->created_by : Auth::id(),
                'updated_by' => Auth::id(),

            ]
        );

        // Reset fields after saving
        $this->resetFields();

        // Flash success message and close modal
        session()->flash('message', $this->companyId ? 'Cost Center updated successfully.' : 'Cost Center added successfully.');
        $this->dispatch('hideModal');
    }


    public function confirmDeletion($id)
    {
        $this->deleteCompany($id);
    }

    public function deleteCompany($id)
    {
        // Check if there are any associated vouchers with this cost center
        $voucherCount = Voucher::where('cost_center_id', $id)->count();

        // Check if there are any associated chart of accounts with this cost center
        $accountCount = ChartOfAccount::where('cost_center_id', $id)->count();

        if ($voucherCount > 0 || $accountCount > 0) {
            // If there are associated records, prevent deletion and show an error message
            session()->flash('error', 'Cannot delete Cost Center, as it has associated vouchers or accounts.');
            return;
        }

        // If no associated records, proceed with deletion
        Costcenter::findOrFail($id)->delete();
        session()->flash('message', 'Cost Center deleted successfully.');
    }

    public function render()
    {

        $searchTerm = '%' . $this->searchTerm . '%';
        /*$companies = Costcenter::where('name', 'like', $searchTerm)
            ->orWhere('description', 'like', $searchTerm)
            ->paginate($this->itemsPerPage);*/

        if($this->filtersegmentId == 0) {
            $companies= CostCenter::with('company') // Eager load the related company
            ->where('name', 'like', $searchTerm)
            ->orWhere('description', 'like', $searchTerm)
            ->paginate($this->itemsPerPage);
        }

        else {
            $companies = CostCenter::with('company') // Eager load the related company
            ->where('segment_id', '=', session('company_id'))
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            })
            ->paginate($this->itemsPerPage);

            $this->segment_id = session('company_id');
        }

        $segment = Company::where('id', '=', session('company_id'))->first();

        return view('livewire.costcenter.costcenter-manager ', ['companies' => $companies, 'segment' => $segment]);
    }

    private function resetFields()
    {
        $this->companyId = null;
        $this->name = '';
        $this->description = '';
        $this->abv = '';
        $this->segment_id = null;
        $this->opening_date = '';
        $this->closing_date = '';
        $this->status = '';
    }
}
