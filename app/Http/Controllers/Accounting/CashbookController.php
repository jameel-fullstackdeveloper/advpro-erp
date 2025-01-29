<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CashbookController extends Controller
{

    public function print($id)
    {
        $voucher = Voucher::with('voucherDetails.account')->findOrFail($id);

        return view('accounting.cashbook.print', compact('voucher'));
    }



    public function showOpeningBalanceForm() {
        $accounts = ChartOfAccount::all(); // Fetch all accounts from the database
        return view('accounting.test.opening_balance', compact('accounts'));
    }


    public function storeOpeningBalance(Request $request) {
        $accounts = ChartOfAccount::find(array_keys($request->balances));
        $balances = [];

        foreach ($accounts as $account) {
            $amount = $request->balances[$account->id]['amount'];
            $type = $request->balances[$account->id]['type'];

            // Determine the final amount based on the type (Debit/Credit)
            $finalAmount = ($type === 'Dr') ? $amount : -$amount;

            $balances[] = [
                'id' => $account->id,
                'name' => $account->name,
                'type' => $account->group->type->name,  // Assuming relations are properly defined
                'opening_balance' => $finalAmount,
            ];
        }

        addOpeningBalance($balances); // Call the previously defined function to save opening balances

        return redirect()->back()->with('success', 'Opening balances saved successfully.');
    }

    function addOpeningBalance($accounts) {
        $voucher = new Voucher();
        $voucher->voucher_type = 'Opening Balance';
        $voucher->date = now()->startOfYear(); // Assuming opening balance is set at the start of the year
        $voucher->reference_number = 'OB-' . uniqid();
        $voucher->total_amount = array_sum(array_map(fn($account) => abs($account->opening_balance), $accounts));
        $voucher->description = 'Opening balance for all accounts';
        $voucher->status = 'approved'; // Automatically approve the opening balance voucher
        $voucher->financial_year_id = 1; // Set this as per your financial year logic
        $voucher->company_id = 1; // Replace with the actual company ID
        $voucher->created_by = auth()->user()->id;
        $voucher->save();

        foreach ($accounts as $account) {
            $voucherDetail = new VoucherDetail();
            $voucherDetail->voucher_id = $voucher->id;
            $voucherDetail->account_id = $account->id;
            $voucherDetail->amount = abs($account->opening_balance);
            $voucherDetail->type = getDrCrType($account->type, $account->opening_balance);
            $voucherDetail->narration = 'Opening balance for ' . $account->name;
            $voucherDetail->created_by = auth()->user()->id;
            $voucherDetail->save();
        }
    }

    function getDrCrType($accountType, $balance) {
        if (in_array($accountType, ['Asset', 'Expense'])) {
            return $balance >= 0 ? 'Dr' : 'Cr';
        } elseif (in_array($accountType, ['Liability', 'Equity', 'Revenue'])) {
            return $balance >= 0 ? 'Cr' : 'Dr';
        }
        return 'Dr';
    }

    public function showLedger($accountId) {
        $account = ChartOfAccount::findOrFail($accountId);
        $ledger = $this->generateLedger($accountId);

        return view('accounting.test.ledger', compact('account', 'ledger'));
    }

    function generateLedger($accountId) {
        $ledger = [];

        // Step 1: Get the opening balance for the account
        $openingBalance = VoucherDetail::whereHas('voucher', function ($query) {
            $query->where('voucher_type', 'Opening Balance');
        })->where('account_id', $accountId)
          ->sum(DB::raw("CASE WHEN type = 'Dr' THEN amount ELSE -amount END"));

        // Initialize the ledger with the opening balance
        $currentBalance = $openingBalance;
        $ledger[] = [
            'date' => 'Opening Balance',
            'description' => 'Opening Balance',
            'debit' => $openingBalance > 0 ? $openingBalance : null,
            'credit' => $openingBalance < 0 ? abs($openingBalance) : null,
            'balance' => $currentBalance,
        ];

        // Step 2: Get all transactions for this account
        $transactions = VoucherDetail::where('account_id', $accountId)
            ->whereHas('voucher', function ($query) {
                $query->where('voucher_type', '!=', 'Opening Balance');
            })
            ->orderBy('date', 'asc')
            ->get();

        // Step 3: Process each transaction and update the ledger
        foreach ($transactions as $transaction) {
            $currentBalance += $transaction->type === 'Dr' ? $transaction->amount : -$transaction->amount;

            $ledger[] = [
                'date' => $transaction->voucher->date,
                'description' => $transaction->voucher->description ?: $transaction->narration,
                'debit' => $transaction->type === 'Dr' ? $transaction->amount : null,
                'credit' => $transaction->type === 'Cr' ? $transaction->amount : null,
                'balance' => $currentBalance,
            ];
        }

        return $ledger;
    }


}
