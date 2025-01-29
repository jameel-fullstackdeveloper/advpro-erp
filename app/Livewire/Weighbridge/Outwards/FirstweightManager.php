<?php

namespace App\Livewire\Weighbridge\Outwards;

use Livewire\Component;
use App\Models\CustomerDetail;
use App\Models\ChartOfAccount;
use App\Models\WeighbridgeOutward;
use App\Models\SalesOrder;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FirstweightManager extends Component
{

    use WithPagination;

    public $outward_id, $first_weight_datetime, $truck_number, $first_weight, $driver_name, $driver_mobile,$driveroption;
    public $itemsPerPage = 50;
    public $searchTerm;
    public $isEditMode = false;


    public $isProcessing = false;

    protected $rules = [
        'first_weight_datetime' => 'required|date',
        'truck_number' => 'required|string',
        'first_weight' => 'required|numeric',
        'driver_name' => 'required|string',
        'driveroption' => 'required|string',
        'driver_mobile' => 'required|string',
    ];


    public function mount()
    {
        abort_if(!auth()->user()->can('weighbridge view'), 403);
        // Set the first_weight_datetime to the current date
        $this->first_weight_datetime = Carbon::now()->format('d-m-Y h:i');
    }

    public function updated($field)
    {
        // Validate only the field being updated
        $this->validateOnly($field);
    }

    public function create()
    {
        $this->resetFields();
        $this->isEditMode = false;
        $this->dispatch('showModal_firstweight');
        $this->dispatch('initializeSocketForWeight'); // This event triggers the socket initialization

    }

    public function edit($id)
    {
        $this->isEditMode = true;
        $outward = WeighbridgeOutward::findOrFail($id);
        $this->outward_id = $outward->id;
        $this->first_weight_datetime = Carbon::parse($outward->created_at)->format('Y-m-d H:i');
        $this->truck_number = $outward->truck_number;
        $this->first_weight = $outward->first_weight;
        $this->driveroption = $outward->driveroption; // Add the actual field when connected to a database
        $this->driver_name = $outward->driver_name; // Add the actual field when connected to a database
        $this->driver_mobile = $outward->driver_mobile; // Placeholder for now
        $this->dispatch('showModal_firstweight');
    }

    public function confirmDeletionFirstWeight($id)
    {
        // This method can be used to confirm and trigger the deletion
        $this->deleteFirstWeight($id);
    }


    public function deleteFirstWeight($id)
    {
        WeighbridgeOutward::findOrFail($id)->delete();
        session()->flash('message', 'First Weight record deleted successfully.');
    }



    public function store()
    {
        // Validate the inputs
        $this->validate([
            'truck_number' => 'required|string',
            'first_weight' => 'required|numeric',
            'driver_name' => 'required|string',
            'driver_mobile' => ['required', 'digits:11'],
            'driveroption' => 'required|string',
        ]);

         // Set the loading state to true to disable the button
        $this->isProcessing = true;

        if (!$this->isEditMode) {
            // If it's not edit mode, validate and require the first_weight
            $this->validate([
                'first_weight' => 'required|numeric',
            ]);
        }

        // Prepare the data for update or creation
        $data = [
            'first_weight_datetime' => Carbon::parse($this->first_weight_datetime)->format('Y-m-d H:i'),
            'truck_number' => $this->truck_number,
            'driver_name' => $this->driver_name,
            'driver_mobile' => $this->driver_mobile,
            'driveroption' => $this->driveroption,
            'company_id' => session('company_id'),
            'created_by' => Auth()->id(), // Set the user who created/updated the record
        ];

        // Only add the first_weight if it's a new entry (not in edit mode)
        if (!$this->isEditMode) {
            $data['first_weight'] = $this->first_weight;
        }


        // Update or create the record
        WeighbridgeOutward::updateOrCreate(
            [
                // Condition to match an existing record: only if outward_id exists (in edit mode)
                'id' => $this->isEditMode ? $this->outward_id : null,
            ],
            $data
        );

        // Set flash message and reset form
        session()->flash('message', $this->isEditMode ? 'First Weight updated successfully.' : 'First Weight created successfully.');
        $this->dispatch('hideModal_firstweight');
        $this->resetFields(); // Reset the form fields
        // After processing, reset the state
        $this->isProcessing = false;

    }


    public function resetFields()
    {
        $this->first_weight_datetime = Carbon::now()->format('Y-m-d h:i A');
        $this->truck_number = '';
        $this->first_weight = '';
        $this->driver_name = '';
        $this->driver_mobile = '';
    }

    public function render()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session

        // Fetch WeighbridgeOutward records filtered by company_id
        $outwardsfirstweight = WeighbridgeOutward::whereNull('second_weight') // Filter for records without second weight
            ->where('status', 0) // Filter for records with status == 0
            ->where('truck_number', 'like', '%'.$this->searchTerm.'%') // Apply search term for truck number
            ->where('company_id', $companyId) // Filter by company_id
            ->paginate($this->itemsPerPage);

        return view('livewire.weighbridge.outwards.firstweight-manager', compact('outwardsfirstweight'));
    }


}
