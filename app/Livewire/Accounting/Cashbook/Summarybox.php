<?php

namespace App\Livewire\Accounting\Cashbook;

use Livewire\Component;
use App\Models\ChartOfAccount;
use App\Models\VoucherDetail;

class Summarybox extends Component
{

    protected $listeners = ['filterUpdated' => 'updateSummaryData'];



    public $cashInHandBalance = 0;
    public $debitTotal = 0;
    public $creditTotal = 0;
    public $balance = 0;

    public function mount()
    {
        $this->updateSummaryData('CurrentMonth'); // Default to 'Today'
    }

    public function updateSummaryData($filter)
    {
        // Calculate cash in hand
        $cashAccounts = ChartOfAccount::where('company_id', session('company_id'))
            ->where('group_id', 1)
            ->get();

        $this->cashInHandBalance = $cashAccounts->sum('balance');

         // Fetch debit total for cash receipts
        $this->debitTotal = VoucherDetail::join('vouchers as v1', 'v1.id', '=', 'voucher_details.voucher_id') // Use alias 'v1' for vouchers
        ->whereIn('voucher_details.account_id', $cashAccounts->pluck('id'))
        ->where('v1.voucher_type', 'cash-receipt')
        ->where('voucher_details.type', 'debit')
        ->when($filter, function ($query) use ($filter) {
            return $this->applyFilter($query, $filter);
        })
        ->sum('voucher_details.amount');

    // Fetch credit total for cash payments
    $this->creditTotal = VoucherDetail::join('vouchers as v2', 'v2.id', '=', 'voucher_details.voucher_id') // Use alias 'v2' for vouchers
        ->whereIn('voucher_details.account_id', $cashAccounts->pluck('id'))
        ->where('v2.voucher_type', 'cash-payment')
        ->where('voucher_details.type', 'credit')
        ->when($filter, function ($query) use ($filter) {
            return $this->applyFilter($query, $filter);
        })
        ->sum('voucher_details.amount');

        // Calculate balance
        $this->balance = $this->cashInHandBalance + $this->debitTotal - $this->creditTotal;
    }

    private function applyFilter($query, $filter)
    {
        switch ($filter) {
            case 'Today':
                // Filter by today's date
                return $query->join('vouchers', 'vouchers.id', '=', 'voucher_details.voucher_id')
                            ->whereDate('vouchers.date', now()->toDateString());

            case 'CurrentMonth':
                // Filter by current month and year
                return $query->join('vouchers', 'vouchers.id', '=', 'voucher_details.voucher_id')
                            ->whereMonth('vouchers.date', now()->month)
                            ->whereYear('vouchers.date', now()->year);

            case 'CurrentYear':
                // Filter by current year
                return $query->join('vouchers', 'vouchers.id', '=', 'voucher_details.voucher_id')
                            ->whereYear('vouchers.date', now()->year);

            case 'LastMonth':
                // Filter by last month
                return $query->join('vouchers', 'vouchers.id', '=', 'voucher_details.voucher_id')
                            ->whereMonth('vouchers.date', now()->subMonth()->month)
                            ->whereYear('vouchers.date', now()->subMonth()->year);

            case 'LastQuarter':
                // Filter by last quarter
                return $query->join('vouchers', 'vouchers.id', '=', 'voucher_details.voucher_id')
                            ->whereBetween('vouchers.date', [
                                now()->subQuarter()->startOfQuarter(),
                                now()->subQuarter()->endOfQuarter()
                            ]);

            case 'LastYear':
                // Filter by last year
                return $query->join('vouchers', 'vouchers.id', '=', 'voucher_details.voucher_id')
                            ->whereYear('vouchers.date', now()->subYear()->year);

            case 'Last30Days':
                // Filter by the last 30 days
                return $query->join('vouchers', 'vouchers.id', '=', 'voucher_details.voucher_id')
                            ->whereBetween('vouchers.date', [now()->subDays(30), now()]);

            case 'Last60Days':
                // Filter by the last 60 days
                return $query->join('vouchers', 'vouchers.id', '=', 'voucher_details.voucher_id')
                            ->whereBetween('vouchers.date', [now()->subDays(60), now()]);

            case 'Last90Days':
                // Filter by the last 90 days
                return $query->join('vouchers', 'vouchers.id', '=', 'voucher_details.voucher_id')
                            ->whereBetween('vouchers.date', [now()->subDays(90), now()]);
        }
    }




    public function render()
    {
        return view('livewire.accounting.cashbook.summarybox');
    }
}
