<?php

namespace App\Livewire\Accounting\Voucher;

use Livewire\Component;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\ChartOfAccount;
use Livewire\WithPagination;
use App\Models\Company;
use App\Models\Costcenter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;

class JournalVoucher extends Component
{

    use WithPagination; // Use the trait
    use WithFileUploads;

    public $voucherDetails = []; // Initialize as an empty array
    public $voucherId, $voucher_date, $reference_number, $total_amount, $description, $status;
    public $account_id;
    public $isEditing = false;
    public $isDetailEditing = false;


    public $isOpen = false;
    public $selected_id;
    public $voucher_type='journal';
    public $amount=0;
    public $debit_amount=0;
    public $credit_amount=0;
    public $type;
    public $image;

    public $totalDebit = 0;
    public $totalCredit = 0;
    public $balance = 0;
    public $totalSumJV;
    public $itemsPerPage = 50;
    public $searchTerm = '';
    public $filter = 'CurrentMonth'; // New property to hold the selected filter

    public $segments;
    public $costCenters = [];
    public $selectedSegment = 1;
    public $selectedCostCenter = 1;

    protected $paginationTheme = 'bootstrap'; // Use Bootstrap pagination

    protected $rules = [
        'voucher_date' => 'required|date',
        'reference_number' => 'required|string|unique:vouchers,reference_number',
        'description' => 'required',
        'voucherDetails.*.account_id' => 'required|exists:chart_of_accounts,id',
        'voucherDetails.*.debit_amount' => 'required_without:voucherDetails.*.credit_amount|numeric|min:0',
        'voucherDetails.*.credit_amount' => 'required_without:voucherDetails.*.debit_amount|numeric|min:0',
        'voucherDetails.*.type' => 'required|string|in:debit,credit',
        'image' => 'nullable|image|max:1024', // 1MB Max
    ];

    protected $messages = [
        'description' => 'Description is required.',
        'voucherDetails.*.account_id.required' => 'Account Title is required.',
        'voucherDetails.*.debit_amount.required_without' => 'Debit amount is required.',
        'voucherDetails.*.credit_amount.required_without' => 'Credit amount is required.',
    ];


    public function updatedVoucherDetails()
    {
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->totalDebit = array_sum(array_map(function ($detail) {
            return (float) $detail['debit_amount'];
        }, $this->voucherDetails));

        $this->totalCredit = array_sum(array_map(function ($detail) {
            return (float) $detail['credit_amount'];
        }, $this->voucherDetails));

        $this->balance = $this->totalDebit - $this->totalCredit;
    }


     // Add a mount method to initialize the array
     public function mount()
     {
        abort_if(!auth()->user()->can('accounting journalvoucher view'), 403);

        $this->segments = Company::all();  // Fetch all segments (companies)
        $this->costCenters = Costcenter::all();

        $this->voucher_date = date('Y-m-d');

        // Generate voucher number
        $this->reference_number = $this->generateVoucherNumber();

        $this->voucherDetails = [
            ['account_id' => '', 'debit_amount' => 0, 'credit_amount' => 0, 'type' => 'debit', 'narration' => ''],
            ['account_id' => '', 'debit_amount' => 0, 'credit_amount' => 0, 'type' => 'credit', 'narration' => '']
        ]; // Initialize with at least one row


        $this->calculateTotals(); // Initialize totals

     }

     public function updatedselectedCostCenter($segmentId)
    {
       // Fetch only the segment_id for the selected segment
       $this->selectedSegment = Costcenter::where('id', $segmentId)->value('segment_id');
    }


    public function render()
    {
        // Query for vouchers without filtering by company_id
        $vouchers = Voucher::with('voucherDetails.account')
            ->where(function ($query) {
                $query->where('reference_number', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $this->searchTerm . '%')
                    ->orWhereHas('voucherDetails.account', function ($query) {
                        $query->where('name', 'like', '%' . $this->searchTerm . '%');
                    });
            })
            ->where('voucher_type', 'journal')
            ->where(function ($query) {
                switch ($this->filter) {
                    case 'Today':
                        $query->whereDate('date', now());
                        break;
                    case 'CurrentMonth':
                        $query->whereMonth('date', now()->month)
                            ->whereYear('date', now()->year);
                        break;
                    case 'CurrentYear':
                        $query->whereYear('date', now()->year);
                        break;
                    case 'LastMonth':
                        $query->whereMonth('date', now()->subMonth()->month)
                            ->whereYear('date', now()->subMonth()->year);
                        break;
                    case 'LastQuarter':
                        $query->whereBetween('date', [
                            now()->subQuarter()->startOfQuarter(),
                            now()->subQuarter()->endOfQuarter()
                        ]);
                        break;
                    case 'LastYear':
                        $query->whereYear('date', now()->subYear()->year);
                        break;
                    case 'Last30Days':
                        $query->whereBetween('date', [now()->subDays(30), now()]);
                        break;
                    case 'Last60Days':
                        $query->whereBetween('date', [now()->subDays(60), now()]);
                        break;
                    case 'Last90Days':
                        $query->whereBetween('date', [now()->subDays(90), now()]);
                        break;
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->itemsPerPage);

        // Fetch accounts without filtering by company_id
        $accounts = ChartOfAccount::orderBy('name', 'asc')->get(); // Fetch all accounts, no company_id filter

        return view('livewire.accounting.voucher.journalvoucher', [
            'accounts' => $accounts,
            'vouchers' => $vouchers,
        ]);
    }



    public function createVoucher()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        if (!$this->isEditing) {
            $this->resetInputFields();
            $this->reference_number = $this->generateVoucherNumber(); // Generate a new voucher number for new entry
        }
        $this->isOpen = true;
        $this->dispatch('showModal_journal');
    }

    public function closeModal()
    {
        $this->resetInputFields();
        $this->resetValidation();
        $this->isEditing = false; // Reset editing mode
        $this->isOpen = false;
        $this->dispatch('hideModal_journal');
    }

    private function generateVoucherNumber()
{
    $company = Company::where('id', session('company_id'))->first(); // Fetch the company record
    $companyAbbreviation = $company ? $company->abv : 'JV'; // Default to 'JV' if not found

    $currentDate = now();

    // Determine the start of the fiscal year (starting from July)
    $financialYearStart = $currentDate->month >= 7
        ? $currentDate->copy()->month(7)->startOfMonth()
        : $currentDate->copy()->subYear()->month(7)->startOfMonth();

    // Get the last two digits of the current year for use in the voucher number
    $currentYear = substr($currentDate->format('Y'), -2); // Last two digits of the year

    // Count the number of journal vouchers in the current fiscal year
    $voucherCount = Voucher::where('voucher_type', 'journal') // Only count journal vouchers
        ->where('date', '>=', $financialYearStart)
        ->where('date', '<', $financialYearStart->copy()->addYear()) // Ensure it’s within the fiscal year
        ->count();

    // Start numbering from 1 for the new fiscal year
    $nextVoucherNumber = $voucherCount + 1;

    do {
        // Format the next voucher number with leading zeros
        $formattedNumber = str_pad($nextVoucherNumber, 3, '0', STR_PAD_LEFT);
        // Generate the voucher number in the format: [CompanyAbbreviation]-[VoucherType]-[FiscalYearLast2Digits]-[FormattedNumber]
        $voucherNumber =  'JV'  . $currentYear . '-' . $formattedNumber;

        // Check if the voucher number already exists
        $voucherExists = Voucher::where('reference_number', $voucherNumber)->exists();

        if ($voucherExists) {
            // If the number exists, increment and retry
            $nextVoucherNumber++;
        }
    } while ($voucherExists);

    return $voucherNumber;
}



    public function storeVoucher()
    {

        // Get current year
        $currentYear = date('Y');

        // Define your maximum allowed back year (e.g., 2 years ago)
        $allowedYear = $currentYear - 2;

        $this->validate([
            'voucher_date' => 'required|date|date_format:Y-m-d|after_or_equal:' . $allowedYear . '-01-01|before_or_equal:' . $currentYear . '-12-31',
            'reference_number' => 'required|string|unique:vouchers,reference_number,' . $this->voucherId, // Allow the same reference number for the current voucher
            'description' => 'required|string|max:255',
			'voucherDetails.*.account_id' => 'required|exists:chart_of_accounts,id',
            'voucherDetails.*.debit_amount' => 'required|numeric|min:0',
            'voucherDetails.*.credit_amount' => 'required|numeric|min:0',
            //'selectedSegment' => 'required|exists:companies,id',  // Ensure segment is selected
           'selectedCostCenter' => 'required|exists:cost_centers,id',  // Ensure cost center is selected
        ]);

        DB::beginTransaction();

        try {


            // Create the voucher
            $voucher = Voucher::create([
                'voucher_type' => $this->voucher_type,
                'date' => $this->voucher_date,
                'reference_number' => $this->reference_number, // Use the generated voucher number
                'total_amount' => $this->totalDebit,
                'description' => $this->description,
                'status' => 1,
                'segment_id' => $this->selectedSegment,
                'cost_center_id' => $this->selectedCostCenter,
                'company_id' =>  session('company_id'),
                'created_by' => Auth::id(),
            ]);

            // Create the voucher details
            foreach ($this->voucherDetails as $detail) {
                $amount = $detail['type'] === 'debit' ? $detail['debit_amount'] : $detail['credit_amount'];

                // Determine the type based on the amount fields
                if ($detail['credit_amount'] > 0) {
                    $detail['type'] = 'credit';
                    $amount = $detail['credit_amount'];
                } elseif ($detail['debit_amount'] > 0) {
                    $detail['type'] = 'debit';
                    $amount = $detail['debit_amount'];
                } else {
                    // Handle the case where neither debit nor credit amount is greater than 0
                    $detail['type'] = 'debit'; // Default to 'debit'
                    $amount = $detail['debit_amount']; // Set to 0 or some default value
                }

                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $detail['account_id'],
                    'amount' => $amount,
                    'type' => $detail['type'],
                    'narration' => $detail['narration'],
                    'segment_id' => $this->selectedSegment,
                    'cost_center_id' => $this->selectedCostCenter,
                    'created_by' => Auth::id(),
                ]);
            }

            // Commit the transaction
            DB::commit();

            session()->flash('message', 'Journal Voucher created successfully.');
            $this->closeModal();
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Optionally, log the error or handle it as needed
            session()->flash('error', 'An error occurred while creating the voucher. Please try again.' . $e->getMessage());
            //$this->closeModal();
        }
    }

    public function editVoucher($id)
    {
        $voucher = Voucher::with('voucherDetails')->findOrFail($id);

          // Reset selectedCostCenterCP and costCenters before setting new values
          $this->selectedCostCenter = null;
          $this->costCenters = []; // Reset cost centers to an empty array


        $this->voucherId = $voucher->id;
        $this->voucher_type = $voucher->voucher_type;
        $this->voucher_date = $voucher->date;
        $this->reference_number = $voucher->reference_number;
        $this->total_amount = $voucher->total_amount;
        $this->description = $voucher->description;
        $this->status = $voucher->status;

        // Set selected segment
        $this->selectedSegment = $voucher->segment_id;

        // Fetch related cost centers for the selected segment
        $this->costCenters = Costcenter::all();

        // Set selected cost center after loading cost centers
        $this->selectedCostCenter = $voucher->cost_center_id;

        $this->voucherDetails = $voucher->voucherDetails->map(function ($detail) {
            return [
                'id' => $detail->id, // Include the id to update existing records
                'account_id' => $detail->account_id,
                'debit_amount' => $detail->type === 'debit' ? $detail->amount : 0,
                'credit_amount' => $detail->type === 'credit' ? $detail->amount : 0,
                'narration' => $detail->narration,
            ];
        })->toArray();

        $this->calculateTotals();

       $this->isEditing = true;
        $this->openModal();
    }

    public function updateVoucher()
    {
          // Get current year
          $currentYear = date('Y');

          // Define your maximum allowed back year (e.g., 2 years ago)
          $allowedYear = $currentYear - 2;

          $this->validate([
              'voucher_date' => 'required|date|date_format:Y-m-d|after_or_equal:' . $allowedYear . '-01-01|before_or_equal:' . $currentYear . '-12-31',
              'reference_number' => 'required|string|unique:vouchers,reference_number,' . $this->voucherId, // Allow the same reference number for the current voucher
              'description' => 'required|string|max:255',
			  'voucherDetails.*.account_id' => 'required|exists:chart_of_accounts,id',
              'voucherDetails.*.debit_amount' => 'required|numeric|min:0',
              'voucherDetails.*.credit_amount' => 'required|numeric|min:0',
              //'selectedSegment' => 'required|exists:companies,id',  // Ensure segment is selected
              'selectedCostCenter' => 'required|exists:cost_centers,id',  // Ensure cost center is selected
          ]);

        DB::beginTransaction();

        try {
            $voucher = Voucher::findOrFail($this->voucherId);

            $voucher->update([
                'voucher_type' => $this->voucher_type,
                'date' => $this->voucher_date,
                'reference_number' => $this->reference_number,
                'total_amount' => $this->totalDebit, // Use the calculated total debit
                'description' => $this->description,
                'status' => $this->status,
                'company_id' => session('company_id'),
                'segment_id' =>  $this->selectedSegment,
                'cost_center_id' =>  $this->selectedCostCenter,
                'updated_by' => Auth::id(),
            ]);

            // Update or create voucher details
            foreach ($this->voucherDetails as $index => $detail) {
                if (isset($detail['id'])) {
                    // Update the existing voucher detail
                    VoucherDetail::where('id', $detail['id'])->update([
                        'account_id' => $detail['account_id'],
                        'amount' => $detail['debit_amount'] > 0 ? $detail['debit_amount'] : $detail['credit_amount'],
                        'type' => $detail['debit_amount'] > 0 ? 'debit' : 'credit',
                        'narration' => $detail['narration'],
                        'segment_id' =>  $this->selectedSegment,
                        'cost_center_id' =>  $this->selectedCostCenter,
                        'updated_by' => Auth::id(),
                    ]);
                } else {
                    // Create a new voucher detail if it doesn't exist
                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => $detail['account_id'],
                        'amount' => $detail['debit_amount'] > 0 ? $detail['debit_amount'] : $detail['credit_amount'],
                        'type' => $detail['debit_amount'] > 0 ? 'debit' : 'credit',
                        'narration' => $detail['narration'],
                        'segment_id' =>  $this->selectedSegment,
                        'cost_center_id' =>  $this->selectedCostCenter,
                        'updated_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            session()->flash('message', 'Journal Voucher updated successfully.');
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred while updating the voucher. Please try again.' .  $e->getMessage());
        }

    }

    public function deleteVoucherJV($id)
    {
        $voucher = Voucher::findOrFail($id);

        // Set the session variable in MySQL for subsequent queries
        DB::statement("SET @current_user_id = ?", [auth()->user()->id]);

        // Delete associated voucher details
        $voucher->voucherDetails()->delete();

        // Delete the voucher itself
        $voucher->delete();

        session()->flash('message', 'Voucher and associated details deleted successfully.');

    }



    private function resetInputFields()
    {
        $this->voucher_type = 'journal';
        $this->voucher_date = date('Y-m-d');
        $this->description = '';
        $this->status = '';
        $this->totalDebit = 0;
        $this->totalCredit = 0;
        $this->balance = 0;
        $this->selectedSegment=1;
        $this->selectedCostCenter=1;
        $this->voucherDetails = [
            ['account_id' => '', 'debit_amount' => 0, 'credit_amount' => 0, 'type' => 'debit', 'narration' => ''],
            ['account_id' => '', 'debit_amount' => 0, 'credit_amount' => 0, 'type' => 'credit', 'narration' => '']
        ]; // Reset to initial state
    }

    public function addVoucherDetail()
    {


        $this->voucherDetails[] = [
            'account_id' => $this->account_id,
            'debit_amount' => $this->debit_amount,
            'credit_amount' => $this->credit_amount,
            'amount' => $this->amount,
            'type' => 'debit',
        ];

        $this->resetDetailInputFields();
    }

    private function resetDetailInputFields()
    {
        $this->account_id = '';
        $this->debit_amount = 0;
        $this->credit_amount = 0;
        $this->amount=0;

    }

    public function removeVoucherDetail($index)
    {
        unset($this->voucherDetails[$index]);
        $this->voucherDetails = array_values($this->voucherDetails);
    }

    public function updatedItemsPerPage()
    {

        $this->resetPage(); // Reset to the first page when items per page changes
        $this->calculateTotalSumJV();
    }

    public function updatedSearchTerm()
    {

        $this->resetPage(); // Reset to the first page when the search term changes
        $this->calculateTotalSumJV();
    }

    public function updatedFilter()
    {

        $this->resetPage();
        $this->calculateTotalSumJV();
    }

    public function confirmDeletionJV($voucherId)
    {
       // This method can be used to confirm and trigger the deletion
       $this->dispatch('swal:confirm-deletion-JV', voucherId: $voucherId);
    }



    public function printVoucher($id)
    {
       // You can directly generate the route URL for the print view
        return redirect()->route('accounting.vouchers.jvprint', ['id' => $id]);
    }

    public function calculateTotalSumJV()
    {
        $vouchers = Voucher::with('voucherDetails.account')
            ->where(function ($query) {
                $query->where('reference_number', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $this->searchTerm . '%')
                    ->orWhereHas('voucherDetails.account', function ($query) {
                       // $query->where('type', 'credit');
                        $query->where('name', 'like', '%' . $this->searchTerm . '%');
                    });
            })
            ->where('voucher_type', 'journal')
            ->whereHas('voucherDetails', function ($query) {
                //$query->where('type', 'credit');
            })
            ->where(function ($query) {
                switch ($this->filter) {
                    case 'Today':
                        $query->whereDate('date', now());
                        break;
                    case 'CurrentMonth':
                        $query->whereMonth('date', now()->month)
                            ->whereYear('date', now()->year);
                        break;
                    case 'CurrentYear':
                        $query->whereYear('date', now()->year);
                        break;
                    case 'LastMonth':
                        $query->whereMonth('date', now()->subMonth()->month)
                            ->whereYear('date', now()->subMonth()->year);
                        break;
                    case 'LastQuarter':
                        $query->whereBetween('date', [
                            now()->subQuarter()->startOfQuarter(),
                            now()->subQuarter()->endOfQuarter()
                        ]);
                        break;
                    case 'LastYear':
                        $query->whereYear('date', now()->subYear()->year);
                        break;
                    case 'Last30Days':
                        $query->whereBetween('date', [now()->subDays(30), now()]);
                        break;
                    case 'Last60Days':
                        $query->whereBetween('date', [now()->subDays(60), now()]);
                        break;
                    case 'Last90Days':
                        $query->whereBetween('date', [now()->subDays(90), now()]);
                        break;
                }
            })
            ->get();

        $this->totalSumJV = $vouchers->sum('total_amount');
    }



}
