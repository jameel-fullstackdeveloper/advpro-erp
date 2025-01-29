<?php

namespace App\Livewire\Accounting\Voucher;

use Livewire\Component;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceiptVoucher extends Component
{
    public $voucherDetails = [];
    public $voucherId, $voucher_date, $reference_number, $total_amount, $description, $status;
    public $payment_at;
    public $isEditing = false;
    public $isOpen = false;
    public $totalAmount = 0;
    public $itemsPerPage = 50;
    public $searchTerm = '';
    public $filter = 'Today';
    public $totalSum;
    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'voucher_date' => 'required|date',
        'reference_number' => 'required|string|unique:vouchers,reference_number',
        'payment_at' => 'required|exists:chart_of_accounts,id',
        'voucherDetails.*.account_id' => 'required|exists:chart_of_accounts,id',
        'voucherDetails.*.amount' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        abort_if(!auth()->user()->can('accounting journalvoucher view'), 403);

        $this->voucher_date = date('Y-m-d');
        $this->reference_number = $this->generateVoucherNumber();
        $this->voucherDetails = [
            ['account_id' => '', 'amount' => 0]
        ];
        $this->calculateTotalAmount();
    }

    public function render()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session

        // Query for vouchers filtered by company_id
        $vouchers = Voucher::with('voucherDetails.account')
            ->where('company_id', $companyId) // Filter by company_id
            ->where(function ($query) {
                $query->where('reference_number', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $this->searchTerm . '%')
                    ->orWhereHas('voucherDetails.account', function ($query) {
                        $query->where('type', 'debit');
                        $query->where('name', 'like', '%' . $this->searchTerm . '%');
                    });
            })
            ->where('voucher_type', 'receipt')
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
            ->orderBy('date', 'desc')
            ->paginate($this->itemsPerPage);

        // Fetch bank and cash accounts filtered by company_id
        $bankAndCashAccounts = ChartOfAccount::where('company_id', $companyId) // Filter by company_id
            ->whereIn('group_id', [1, 2]) // Filter for bank and cash accounts
            ->get();

        // Fetch all accounts filtered by company_id
        $accounts = ChartOfAccount::where('company_id', $companyId)->get(); // Filter by company_id

        return view('livewire.accounting.voucher.receipt-voucher', [
            'accounts' => $accounts,
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
        $this->totalAmount = array_sum(
            array_map(
                function($detail) {
                    return (float) ($detail['amount'] ?? 0);
                },
                $this->voucherDetails
            )
        );
    }

    private function generateVoucherNumber()
    {
        $currentDate = now();

        $company = Company::where('id', session('company_id'))->first(); // Fetch the company record
        $companyAbbreviation = $company ? $company->abv : 'JV'; // Default to 'ORD' if not found


        // Determine the start of the financial year (assuming it starts in July)
        $financialYearStart = $currentDate->month >= 7
            ? $currentDate->copy()->month(7)->startOfMonth()
            : $currentDate->copy()->subYear()->month(7)->startOfMonth();

        // Format the current month and year
        $monthYear = $currentDate->format('m') . substr($currentDate->format('Y'), -2);

        // Count the number of receipt vouchers in the current financial year
        $voucherCount = Voucher::where('voucher_type', 'receipt')
            ->where('date', '>=', $financialYearStart)
            ->count();

        $nextVoucherNumber = $voucherCount + 1;

        do {
            $formattedNumber = str_pad($nextVoucherNumber, 3, '0', STR_PAD_LEFT);
            $voucherNumber = $companyAbbreviation . 'RV'  . '-' . $formattedNumber;

            // Check if the voucher number already exists
            $voucherExists = Voucher::where('reference_number', $voucherNumber)->exists();

            if ($voucherExists) {
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
        $this->validate();

        DB::beginTransaction();

        try {
            $voucher = Voucher::create([
                'voucher_type' => 'receipt',
                'date' => $this->voucher_date,
                'reference_number' => $this->reference_number,
                'total_amount' => $this->totalAmount,
                'description' => $this->description,
                'status' => 1,
                'company_id' => session('company_id'),
                'created_by' => Auth::id(),
            ]);

            VoucherDetail::create([
                'voucher_id' => $voucher->id,
                'account_id' => $this->payment_at,
                'amount' => $this->totalAmount,
                'type' => 'debit',
                'narration' => $this->description,
                'created_by' => Auth::id(),
            ]);

            foreach ($this->voucherDetails as $detail) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $detail['account_id'],
                    'amount' => $detail['amount'],
                    'type' => 'credit',
                    'narration' => $detail['narration'],
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
        $this->voucherId = $voucher->id;
        $this->voucher_date = $voucher->date;
        $this->reference_number = $voucher->reference_number;
        $this->totalAmount = $voucher->total_amount; // Ensure total amount is displayed
        $this->description = $voucher->description;
        $this->status = $voucher->status;
        $this->payment_at = $voucher->voucherDetails->where('type', 'debit')->first()->account_id ?? null;

        // Filter out the debit account and prepare the credit accounts for the voucherDetails array
        $this->voucherDetails = $voucher->voucherDetails->where('type', 'credit')->map(function ($detail) {
            return [
                'id' => $detail->id,
                'account_id' => $detail->account_id,
                'amount' => $detail->amount,
                'narration' => $detail->narration,
            ];
        })->toArray();

        $this->isEditing = true;
        $this->openModalRV();
    }

    public function updateVoucher()
    {
        $this->validate([
            'voucher_date' => 'required|date',
            'reference_number' => 'required|string|unique:vouchers,reference_number,' . $this->voucherId,
            'voucherDetails.*.account_id' => 'required|exists:chart_of_accounts,id',
            'voucherDetails.*.amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $voucher = Voucher::findOrFail($this->voucherId);

            $voucher->update([
                'voucher_type' => 'receipt',
                'date' => $this->voucher_date,
                'reference_number' => $this->reference_number,
                'total_amount' => $this->totalAmount,
                'description' => $this->description,
                'status' => $this->status,
                'company_id' => session('company_id'),
                'updated_by' => Auth::id(),
            ]);

            foreach ($this->voucherDetails as $detail) {
                if (isset($detail['id'])) {
                    VoucherDetail::where('id', $detail['id'])->update([
                        'account_id' => $detail['account_id'],
                        'amount' => $detail['amount'],
                        'type' => 'credit',
                        'narration' => $detail['narration'],
                        'updated_by' => Auth::id(),
                    ]);
                } else {
                    VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => $detail['account_id'],
                        'amount' => $detail['amount'],
                        'type' => 'credit',
                        'narration' => $detail['narration'],
                        'updated_by' => Auth::id(),
                    ]);
                }
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
        $this->voucherDetails = [
            ['account_id' => '', 'amount' => 0]
        ];
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
        $voucher = Voucher::findOrFail($id);

        // Delete associated voucher details
        $voucher->voucherDetails()->delete();

        // Delete the voucher itself
        $voucher->delete();

        session()->flash('message', 'Voucher and associated details deleted successfully.');

    }

    public function confirmDeletionRV($id)
    {
        // This method can be used to confirm and trigger the deletion
        $this->deleteVoucherRV($id);
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
            ->where('voucher_type', 'receipt')
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
