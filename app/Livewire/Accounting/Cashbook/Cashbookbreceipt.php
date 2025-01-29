<?php

namespace App\Livewire\Accounting\Cashbook;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\Costcenter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;

class Cashbookbreceipt extends Component
{

    use WithPagination,WithFileUploads;

    public $voucherDetails = [];
    public $voucherId, $voucher_date, $reference_number, $total_amount, $description, $status;
    public $payment_at;
    public $isEditing = false;
    public $isOpen = false;
    public $totalAmount = 0;
    public $totalAmountRV = 0;
    public $itemsPerPage = 50;
    public $searchTerm = '';
    public $imagecbrec;
    public $filter = 'CurrentMonth';
    public $totalSum;
    protected $paginationTheme = 'bootstrap';

    public $segments;
    public $costCenters = [];
    public $selectedSegment = 1;
    public $selectedCostCenter = 1;


    protected $rules = [
        'voucher_date' => 'required|date',
        'reference_number' => 'required|string|unique:vouchers,reference_number',
        'payment_at' => 'required',
        'voucherDetails.*.account_id' => 'required|exists:chart_of_accounts,id',
        'voucherDetails.*.amount' => 'required|numeric|min:0',
        'imagecbrec' => 'nullable|mimes:jpeg,jpg,png,bmp,gif,svg,webp|max:1024' // Keep nullable for optional upload
    ];

    protected $messages = [
        'imagecbrec.mimes' => 'The uploaded file must be an image of type: jpeg, jpg, png, bmp, gif, svg, or webp.',
        'imagecbrec.max' => 'The image size must not exceed 1MB.',
    ];

    public function mount()
    {
        abort_if(!auth()->user()->can('accounting cashbook view'), 403);

        $this->segments = Company::all();  // Fetch all segments (companies)
        $this->costCenters = Costcenter::all();

        // If there are bank/cash accounts, set the first one as default for payment_at
        $firstAccount = ChartOfAccount::whereIn('group_id', [1])->first();
        if ($firstAccount) {
            $this->payment_at = $firstAccount->id;
        }

        $this->voucher_date = date('Y-m-d');
        $this->reference_number = $this->generateVoucherNumber();
        $this->voucherDetails = [
            ['account_id' => '', 'amount' => 0]
        ];
        $this->calculateTotalAmount();
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
                        $query->where('type', 'debit');
                        $query->where('name', 'like', '%' . $this->searchTerm . '%');
                    });
            })
            ->where('voucher_type', 'cash-receipt')
            ->whereHas('voucherDetails', function ($query) {
                $query->where('type', 'debit');
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
            ->orderBy('created_at', 'desc')
            ->paginate($this->itemsPerPage);

            // Fetch bank and cash accounts without filtering by company_id
            $bankAndCashAccounts = ChartOfAccount::whereIn('group_id', [1]) // Filter by group_id for bank and cash accounts
            ->get();

            $this->dispatch('filterUpdated', filter: $this->filter);

            return view('livewire.accounting.cashbook.cashbookbreceipt', [
            'accounts' => ChartOfAccount::whereNotIn('group_id', [1])
            ->orderBy('name')
            ->get(),
            'vouchers' => $vouchers,
            'bankAndCashAccounts' => $bankAndCashAccounts,
            ]);
    }


    public function updatedVoucherDetails()
    {
        $this->calculateTotalAmount();
    }

    public function calculateTotalAmount()
    {
        // Convert all amounts to float, defaulting to 0 if empty
        $this->totalAmountRV = array_sum(
            array_map(
                function ($detail) {
                    return (float)($detail['amount'] ?? 0);
                },
                $this->voucherDetails
            )
        );
    }

    private function generateVoucherNumber()
{
    $company = Company::where('id', session('company_id'))->first(); // Fetch the company record
    $companyAbbreviation = $company ? $company->abv : 'CR'; // Default to 'CR' if not found

    $currentDate = now();

    // Determine the start of the fiscal year (starting from July)
    $financialYearStart = $currentDate->month >= 7
        ? $currentDate->copy()->month(7)->startOfMonth()
        : $currentDate->copy()->subYear()->month(7)->startOfMonth();

    // Get the last two digits of the current year for use in the voucher number
    $currentYear = substr($currentDate->format('Y'), -2); // Last two digits of the year

    // Count the number of receipt vouchers in the current fiscal year
    $voucherCount = Voucher::where('voucher_type', 'cash-receipt')
        ->where('date', '>=', $financialYearStart)
        ->where('date', '<', $financialYearStart->copy()->addYear()) // Ensure it’s within the fiscal year
        ->count();

    // Start numbering from 1 for the new fiscal year
    $nextVoucherNumber = $voucherCount + 1;

    do {
        // Format the next voucher number with leading zeros
        $formattedNumber = str_pad($nextVoucherNumber, 3, '0', STR_PAD_LEFT);
        // Generate the voucher number in the format: [CompanyAbbreviation]-[VoucherType]-[FiscalYearLast2Digits]-[FormattedNumber]
        $voucherNumber =  'CR'  . $currentYear . '-' . $formattedNumber;

        // Check if the voucher number already exists
        $voucherExists = Voucher::where('reference_number', $voucherNumber)->exists();

        if ($voucherExists) {
            // If the number exists, increment and retry
            $nextVoucherNumber++;
        }
    } while ($voucherExists);

    return $voucherNumber;
}


    public function createVoucherRV()
    {
        $this->resetInputFields();
        $this->openModalRV();
    }

    public function openModalRV()
    {
        if (!$this->isEditing) {
            $this->resetInputFields();
            $this->reference_number = $this->generateVoucherNumber();
        }
        $this->isOpen = true;
        $this->dispatch('showModal_receipt');
    }

    public function closeModalRV()
    {
        $this->resetInputFields();
        $this->resetValidation();
        $this->isEditing = false;
        $this->isOpen = false;
        $this->dispatch('hideModal_receipt');
    }

    public function storeVoucher()
    {

         // Get current year
         $currentYear = date('Y');

         // Define your maximum allowed back year (e.g., 2 years ago)
         $allowedYear = $currentYear - 2;

       // Extended validation
        $this->validate([
            'voucher_date' => 'required|date|date_format:Y-m-d|after_or_equal:' . $allowedYear . '-01-01|before_or_equal:' . $currentYear . '-12-31',
            'reference_number' => 'required|string|unique:vouchers,reference_number,' . $this->voucherId,
			'description' => 'required|string|max:255',
            'voucherDetails.*.account_id' => 'required|exists:chart_of_accounts,id',
            'voucherDetails.*.amount' => 'required|numeric|min:0',
            //'voucherDetails.*.narration' => 'nullable|string|max:255',
            'imagecbrec' => 'nullable|image|max:1024', // Image is optional
         //   'selectedSegment' => 'required|exists:companies,id',  // Ensure segment is selected
            'selectedCostCenter' => 'required|exists:cost_centers,id',  // Ensure cost center is selected

        ]);

        $imagePath='';

        DB::beginTransaction();

        try {

            $imagePath = null;

              // Handle Image Upload
            if ($this->imagecbrec) {
                $imagePath = $this->imagecbrec->store('sfpro', 'spaces');
            }

            $voucher = Voucher::create([
                'voucher_type' => 'cash-receipt',
                'date' => $this->voucher_date,
                'reference_number' => $this->reference_number,
                'total_amount' => $this->totalAmountRV,
                'description' => $this->description,
                'image_path' => $imagePath,
                'status' => 1,
                'segment_id' => $this->selectedSegment,
                'cost_center_id' => $this->selectedCostCenter,
                'company_id' =>  session('company_id'),
                'created_by' => Auth::id(),
            ]);

            VoucherDetail::create([
                'voucher_id' => $voucher->id,
                'account_id' => $this->payment_at,
                'amount' => $this->totalAmountRV,
                'type' => 'debit',
                'narration' => $this->description,
                'segment_id' => $this->selectedSegment,
                'cost_center_id' => $this->selectedCostCenter,
                'created_by' => Auth::id(),
            ]);

            foreach ($this->voucherDetails as $detail) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $detail['account_id'],
                    'amount' => $detail['amount'],
                    'type' => 'credit',
                    'narration' => $detail['narration'] ?? '', // Use an empty string if 'narration' is not set
                    'segment_id' => $this->selectedSegment,
                    'cost_center_id' => $this->selectedCostCenter,
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();

            session()->flash('message', 'Receipt Voucher created successfully.');
            $this->closeModalRV();
        } catch (\Exception $e) {
            DB::rollBack();

            // Display the actual error message
            session()->flash('error', 'An error occurred while creating the voucher: ' . $e->getMessage());

        }
    }

    public function editVoucherRV($id)
    {
        $voucher = Voucher::with('voucherDetails')->findOrFail($id);

        // Reset selectedCostCenter and costCenters before setting new values
        $this->selectedCostCenter = null;
        $this->costCenters = []; // Reset cost centers to an empty array

        $this->voucherId = $voucher->id;
        $this->voucher_date = $voucher->date;
        $this->reference_number = $voucher->reference_number;
        $this->totalAmountRV = $voucher->total_amount;
        $this->description = $voucher->description;
        $this->status = $voucher->status;

        // Set selected segment
        $this->selectedSegment = $voucher->segment_id;

        // Fetch related cost centers for the selected segment
        $this->costCenters = Costcenter::all();

        // Set selected cost center after loading cost centers
        $this->selectedCostCenter = $voucher->cost_center_id;

        // Handle payment account
        $this->payment_at = $voucher->voucherDetails->where('type', 'debit')->first()->account_id ?? null;

        // Prepare voucher details, filtering out debit accounts
        $this->voucherDetails = $voucher->voucherDetails->where('type', 'credit')->map(function ($detail) {
            return [
                'id' => $detail->id,
                'account_id' => $detail->account_id,
                'amount' => $detail->amount,
                'narration' => $detail->narration,
            ];
        })->toArray();

        $this->isEditing = true;
        $this->openModalRV();  // Open modal after setting all values
    }


    public function updateVoucher()
    {

        // Get current year
        $currentYear = date('Y');

        // Define your maximum allowed back year (e.g., 2 years ago)
        $allowedYear = $currentYear - 2;

        // Extended validation
        $this->validate([
            'voucher_date' => 'required|date|date_format:Y-m-d|after_or_equal:' . $allowedYear . '-01-01|before_or_equal:' . $currentYear . '-12-31',
            'reference_number' => 'required|string|unique:vouchers,reference_number,' . $this->voucherId,
			'description' => 'required|string|max:255',
            'voucherDetails.*.account_id' => 'required|exists:chart_of_accounts,id',
            'voucherDetails.*.amount' => 'required|numeric|min:0',
            //'voucherDetails.*.narration' => 'nullable|string|max:255',
            'imagecbrec' => 'nullable|image|max:1024', // Image is optional
            'selectedSegment' => 'required|exists:companies,id',  // Ensure segment is selected
            'selectedCostCenter' => 'required|exists:cost_centers,id',  // Ensure cost center is selected
        ]);


        DB::beginTransaction();



        try {
            $voucher = Voucher::findOrFail($this->voucherId);

            // If a new image is uploaded, handle the image upload and delete the old one
            if ($this->imagecbrec) {
                if ($voucher->image_path) {
                    $oldImagePath = parse_url($voucher->image_path, PHP_URL_PATH);
                    $oldImagePath = ltrim($oldImagePath, '/');
                    \Storage::disk('spaces')->delete($oldImagePath); // Delete old image
                }

                $imagePath = $this->imagecbrec->store('sfpro', 'spaces'); // Store new image
            } else {
                // Keep the existing image path if no new image is uploaded
                $imagePath = $voucher->image_path;
            }

            // Update the voucher with the new or existing image path
            $voucher->update([
                'voucher_type' => 'cash-receipt',
                'date' => $this->voucher_date,
                'reference_number' => $this->reference_number,
                'total_amount' => $this->totalAmountRV,
                'description' => $this->description,
                'status' => $this->status,
                'image_path' => $imagePath,  // Save the new or existing image path
                'company_id' => session('company_id'),
                'segment_id' =>  $this->selectedSegment,
                'cost_center_id' =>  $this->selectedCostCenter,
                'updated_by' => Auth::id(),
            ]);

            // Delete all existing voucher details for this voucher
            $voucher->voucherDetails()->delete();

            // Add debit voucher detail
            VoucherDetail::create([
                'voucher_id' => $voucher->id,
                'account_id' => $this->payment_at,
                'amount' => $this->totalAmountRV,
                'type' => 'debit',
                'narration' => $this->description,
                'segment_id' =>  $this->selectedSegment,
                'cost_center_id' =>  $this->selectedCostCenter,
                'created_by' => Auth::id(),
            ]);

            // Add credit voucher details
            foreach ($this->voucherDetails as $detail) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $detail['account_id'],
                    'amount' => $detail['amount'],
                    'type' => 'credit',
                     'narration' => $detail['narration'] ?? '', // Use an empty string if 'narration' is not set
                    'segment_id' =>  $this->selectedSegment,
                    'cost_center_id' =>  $this->selectedCostCenter,
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();

            session()->flash('message', 'Receipt Voucher updated successfully.');
            $this->closeModalRV();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred while updating the voucher. Please try again.');
        }
    }


    private function resetInputFields()
    {
        $this->voucher_date = date('Y-m-d');
        $this->description = '';
        $this->status = '';
        $this->totalAmount = 0;
        $this->totalAmountRV=0;
        $this->selectedSegment=1;
        $this->selectedCostCenter=1;
        $this->voucherDetails = [
            ['account_id' => '', 'amount' => 0]
        ];
        $this->imagecbrec = null; // Reset Imag
    }

    public function addVoucherDetail()
    {
        $this->voucherDetails[] = [
            'account_id' => '',
            'amount' => 0,
        ];

        $this->calculateTotalAmount();
    }

    public function removeVoucherDetail($index)
    {
        unset($this->voucherDetails[$index]);
        $this->voucherDetails = array_values($this->voucherDetails);
        $this->calculateTotalAmount();
    }

    public function deleteVoucherRV($id)
    {
       // dd($id);
        $voucher = Voucher::findOrFail($id);


        // Set the session variable in MySQL for subsequent queries
        DB::statement("SET @current_user_id = ?", [auth()->user()->id]);

        // Delete associated voucher details
        $voucher->voucherDetails()->delete();

        // Delete the voucher itself
        $voucher->delete();

        session()->flash('message', 'Voucher and associated details deleted successfully.');

    }

    public function confirmDeletionRV($voucherId)
    {
        // This method can be used to confirm and trigger the deletion
        //$this->deleteVoucherRV($id);

        $this->dispatch('swal:confirm-deletion', voucherId: $voucherId);
    }

    public function calculateTotalSum()
    {
        $vouchers = Voucher::with('voucherDetails.account')
            ->where(function ($query) {
                $query->where('reference_number', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $this->searchTerm . '%')
                    ->orWhereHas('voucherDetails.account', function ($query) {
                        $query->where('type', 'debit');
                        $query->where('name', 'like', '%' . $this->searchTerm . '%');
                    });
            })
            ->where('voucher_type', 'cash-receipt')
            ->whereHas('voucherDetails', function ($query) {
                $query->where('type', 'debit');
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

        $this->totalSum = $vouchers->sum('total_amount');
    }

    public function updatedfilter()
    {
        $this->calculateTotalSum();
        $this->dispatch('filterUpdated', filter: $this->filter);

    }

    public function updatedSearchTerm()
    {
        $this->calculateTotalSum();
    }

    public function updatedItemsPerPage()
    {
        $this->calculateTotalSum();
    }




}
