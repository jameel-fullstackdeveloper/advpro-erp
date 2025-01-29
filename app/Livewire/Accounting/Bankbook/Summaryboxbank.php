<?php

namespace App\Livewire\Accounting\Bankbook;

use Livewire\Component;
use App\Models\ChartOfAccount;
use App\Models\VoucherDetail;

class Summaryboxbank extends Component
{

    protected $listeners = ['filterUpdated' => 'updateSummaryData'];



    public $cashInHandBalanceBank = 0;
    public $debitTotalBank = 0;
    public $creditTotalBank = 0;
    public $balanceBank = 0;

    public function mount()
    {
        $this->updateSummaryData('CurrentMonth'); // Default to 'Today'
    }

    public function updateSummaryData($filter)
    {
        // Calculate cash in hand
        $cashAccountsBank = ChartOfAccount::where('company_id', session('company_id'))
            ->where('group_id', 2)
            ->get();


        $this->cashInHandBalanceBank = $cashAccountsBank->sum('balance');

         // Fetch debit total for cash receipts
         $this->debitTotalBank = VoucherDetail::join('vouchers as v1', 'v1.id', '=', 'voucher_details.voucher_id') // Use alias 'v1' for vouchers
         ->whereIn('voucher_details.account_id', $cashAccountsBank->pluck('id'))
         ->where('v1.voucher_type', 'bank-receipt')
         ->where('voucher_details.type', 'debit')
         ->when($filter, function ($query) use ($filter) {
             return $this->applyFilter($query, $filter);
         })
         ->sum('voucher_details.amount');

     // Fetch credit total for cash payments
     $this->creditTotalBank = VoucherDetail::join('vouchers as v2', 'v2.id', '=', 'voucher_details.voucher_id') // Use alias 'v2' for vouchers
         ->whereIn('voucher_details.account_id', $cashAccountsBank->pluck('id'))
         ->where('v2.voucher_type', 'bank-payment')
         ->where('voucher_details.type', 'credit')
         ->when($filter, function ($query) use ($filter) {
             return $this->applyFilter($query, $filter);
         })
         ->sum('voucher_details.amount');

        // Calculate balance
        $this->balanceBank = $this->cashInHandBalanceBank + $this->debitTotalBank - $this->creditTotalBank;
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
        return view('livewire.accounting.bankbook.summaryboxbank');
    }
}
