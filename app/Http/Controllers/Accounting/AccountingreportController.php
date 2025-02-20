<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseItem;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\CustomerDetail;
use App\Models\Company;
use App\Models\ChartOfAccount; // Import ChartOfAccount for customers
use App\Models\ChartOfAccountGroup; // Import ChartOfAccount for customers
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class AccountingreportController extends Controller
{

 /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {

        abort_if(!auth()->user()->can('accounting chart of account view'), 403);

        // Get the current month
        $firstDayOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $lastDayOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

        return view('accounting.reports.index', [
            'firstDate' => $firstDayOfMonth,
            'lastDate' => $lastDayOfMonth,
        ]);
    }

    public function cashbank_report(Request $request){

         // Get the current month
         $firstDate = $request->firstdate;
         $lastDate= $request->lastdate;


         // Fetch Cash and Bank accounts with their group names
        $cashAccounts = ChartOfAccount::with('chartOfAccountGroup')
            ->where('group_id', 1) // Replace with your actual group_id for Cash
            ->get();

        $bankAccounts = ChartOfAccount::with('chartOfAccountGroup')
            ->where('group_id', 2) // Replace with your actual group_id for Bank
            ->get();

        // Calculate balances for each account
        foreach ($cashAccounts as $account) {
            $account->opening_balance = $this->calculateOpeningBalance($account->id, $firstDate);
            $account->debit = $this->calculateDebits($account->id, $firstDate, $lastDate);
            $account->credit = $this->calculateCredits($account->id, $firstDate, $lastDate);
            $account->closing_balance = $account->opening_balance + ($account->debit - $account->credit);
        }

        // Calculate balances for each account
        foreach ($bankAccounts as $account) {
            $account->opening_balance = $this->calculateOpeningBalance($account->id, $firstDate);
            $account->debit = $this->calculateDebits($account->id, $firstDate, $lastDate);
            $account->credit = $this->calculateCredits($account->id, $firstDate, $lastDate);
            $account->closing_balance = $account->opening_balance + ($account->debit - $account->credit);
        }


        return view('accounting.reports.cash_bank_report', [
            'firstDate' => $firstDate,
            'lastDate' => $lastDate,
            'cashAccounts' => $cashAccounts,
            'bankAccounts' => $bankAccounts,
        ]);

    }

    // Calculate Opening Balance
    private function calculateOpeningBalance($accountId, $firstDate)
    {

        $account_info= ChartOfAccount::where('id', $accountId)->first();

        $opening_balance  =  $account_info->balance;

        $debit = VoucherDetail::where('account_id', $accountId)
        ->whereHas('voucher', function ($query) use ($firstDate) {
            $query->whereDate('date', '<', $firstDate);
        })
        ->where('type', 'debit')
        ->sum('amount');

        $credit = VoucherDetail::where('account_id', $accountId)
        ->whereHas('voucher', function ($query) use ($firstDate) {
            $query->whereDate('date', '<', $firstDate);
        })
        ->where('type', 'credit')
        ->sum('amount');

        return $opening_balance + $debit - $credit; // Net balance before the start date
    }

    // Calculate Debits in Date Range
    private function calculateDebits($accountId, $firstDate, $lastDate)
    {
        return VoucherDetail::where('account_id', $accountId)
        ->whereHas('voucher', function ($query) use ($firstDate, $lastDate) {
            $query->whereBetween('date', [$firstDate, $lastDate]);
        })
        ->where('type', 'debit')
        ->sum('amount');

    }

    // Calculate Credits in Date Range
    private function calculateCredits($accountId, $firstDate, $lastDate)
    {
        return VoucherDetail::where('account_id', $accountId)
        ->whereHas('voucher', function ($query) use ($firstDate, $lastDate) {
            $query->whereBetween('date', [$firstDate, $lastDate]);
        })
        ->where('type', 'credit')
        ->sum('amount');

    }

}
