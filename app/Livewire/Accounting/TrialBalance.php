<?php

namespace App\Livewire\Accounting;

use Livewire\Component;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\PurchaseBillItem;
use App\Models\PurchaseReturnItem;
use App\Models\ProductionDetail;
use App\Models\SalesInvoiceItem;
use App\Models\SalesReturnItem;
use App\Models\StockMaterialAdjustment;
use App\Models\Items;


class TrialBalance extends Component
{
    public $startDate;
    public $endDate;
    public $trialBalanceData = [];
    public $totalDebit = 0;
    public $totalCredit = 0;

    protected $rules = [
        'startDate' => 'required|date',
        'endDate' => 'required|date|after_or_equal:startDate',
    ];

    public function mount()
    {
        abort_if(!auth()->user()->can('accounting trialbalance view'), 403);

        // Set default values for startDate and endDate to the current month's start and end dates
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.accounting.trial-balance', [
            'trialBalanceData' => $this->trialBalanceData,
            'totalDebit' => $this->totalDebit,
            'totalCredit' => $this->totalCredit,
        ]);
    }

    public function generateTrialBalance()
    {
        $this->validate();

        $companyId = session('company_id'); // Retrieve the company_id from the session

        // Fetch accounts filtered by company_id
        $accounts = ChartOfAccount::with('chartOfAccountGroup.chartOfAccountsType')
            //->where('company_id', $companyId) // Filter by company_id
            ->get();

        $this->trialBalanceData = [];
        $this->totalDebit = 0;
        $this->totalCredit = 0;

        // Variable to store the grand total of the "Value of Stock (Closing)"
        $totalStockValueClosing = 0;

        foreach ($accounts as $account) {
            $openingBalance = $account->balance;
            $accountNature = $account->drcr;
            $accountCategory = $account->chartOfAccountGroup->chartOfAccountsType->name;

            // Correct period balance calculation
            $debitSum = DB::table('voucher_details')
                ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
                ->where('voucher_details.account_id', $account->id)
                //->where('vouchers.company_id', $companyId) // Filter by company_id
                ->whereBetween('vouchers.date', [$this->startDate, $this->endDate])
                ->where('voucher_details.type', 'debit')
                ->sum('voucher_details.amount');

            $creditSum = DB::table('voucher_details')
                ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
                ->where('voucher_details.account_id', $account->id)
                //->where('vouchers.company_id', $companyId) // Filter by company_id
                ->whereBetween('vouchers.date', [$this->startDate, $this->endDate])
                ->where('voucher_details.type', 'credit')
                ->sum('voucher_details.amount');

            $periodBalance = ($accountNature === 'Dr.') ? $debitSum - $creditSum : $creditSum - $debitSum;

            // Determine the final balance based on the opening balance and period transactions
            $finalBalance = ($openingBalance == 0) ? $periodBalance : $openingBalance + $periodBalance;

            if (in_array($accountCategory, ['Current Assets', 'Fixed Assets', 'Expenses'])) {
                if ($finalBalance >= 0) {
                    $this->totalDebit += $finalBalance;
                    $this->trialBalanceData[$accountCategory][$account->chartOfAccountGroup->name][] = [
                        'account_name' => $account->name,
                        'debit' => $finalBalance,
                        'credit' => null,
                    ];
                } else {
                    $this->totalCredit += abs($finalBalance);
                    $this->trialBalanceData[$accountCategory][$account->chartOfAccountGroup->name][] = [
                        'account_name' => $account->name,
                        'debit' => null,
                        'credit' => abs($finalBalance),
                    ];
                }




            } elseif (in_array($accountCategory, ['Liabilities', 'Equity', 'Revenue'])) {
                if ($finalBalance >= 0) {
                    $this->totalCredit += $finalBalance;
                    $this->trialBalanceData[$accountCategory][$account->chartOfAccountGroup->name][] = [
                        'account_name' => $account->name,
                        'debit' => null,
                        'credit' => $finalBalance,
                    ];
                } else {
                    $this->totalDebit += abs($finalBalance);
                    $this->trialBalanceData[$accountCategory][$account->chartOfAccountGroup->name][] = [
                        'account_name' => $account->name,
                        'debit' => abs($finalBalance),
                        'credit' => null,
                    ];
                }
            }

        }

        $totalStockValueClosing = $this->getStockValueClosing($this->startDate, $this->endDate); // Add this line



        // Add the total stock value closing to the trial balance data
            $this->trialBalanceData['Stock'] = [
                'Value of Stock (Closing)' => $totalStockValueClosing,
            ];


    }

     // Method to calculate balance before the start date
     private function calculateBalanceBeforeDate($startDate)
     {
        $companyId = session('company_id');

        $opbals = Items::all();

       // dd($opbals);

        $openbal =0;

        foreach($opbals as $opbal) {

            if($opbal->item_type == 'sale') {
                $openbal += $opbal->sale_price *  $opbal->balance;
            } else  {
                $openbal += $opbal->purchase_price *  $opbal->balance;
            }

        }

         // Get all relevant purchases, returns, and consumptions before the start date
         $purchasesBefore = PurchaseBillItem::join('purchase_bills', 'purchase_bills.id', '=', 'purchase_bill_items.purchase_bill_id')
             ->where('purchase_bills.bill_date', '<', $startDate)
             ->sum('purchase_bill_items.net_quantity');

         $returnsBefore = PurchaseReturnItem::join('pruchase_returns', 'pruchase_returns.id', '=', 'purchase_return_items.purchase_return_id')
             ->where('pruchase_returns.return_date', '<', $startDate)
             ->sum('purchase_return_items.return_quantity');

         $consumptionBefore = ProductionDetail::join('productions', 'productions.id', '=', 'production_details.production_id')
             ->where('productions.production_date', '<', $startDate)
             ->sum('production_details.quantity_used');


           // Shortage and Access
           $shortage = StockMaterialAdjustment::where('adj_date', '<', $startDate)
           ->sum('shortage'); // Sum up the consumed quantity before the start date

           // Shortage and Access
         $excess = StockMaterialAdjustment::where('adj_date', '<', $startDate)
         ->sum('exccess'); // Sum up the consumed quantity before the start date

         // Calculate the balance before the start date
         return $openbal + $purchasesBefore + $returnsBefore   + $excess - $consumptionBefore - $shortage;
     }



     private function getStockValueClosing($startDate,$endDate)
     {

         $companyId = session('company_id');

         // Initialize the ledger array
         $ledger = [];

         // Get the balance up to the start date to calculate the opening balance
         $openingBalance = $this->calculateBalanceBeforeDate($startDate);

         $items = Items::all();

         // Initialize the current balance with the calculated opening balance
         $currentBalance = $openingBalance;

         $currentStockValue = 0;

         foreach($items as $item) {

         // Fetch and process purchases (items with item_type = 'purchase')
         if ($item->item_type == 'purchase') {
             $purchases = PurchaseBillItem::join('purchase_bills', 'purchase_bills.id', '=', 'purchase_bill_items.purchase_bill_id')
                 ->whereBetween('purchase_bills.bill_date', [$startDate, $endDate])
                 ->select('purchase_bill_items.*', 'purchase_bills.bill_date', 'purchase_bills.bill_number') // Explicitly select the required fields
                 ->get();

             foreach ($purchases as $purchase) {
                 //$currentBalance += $purchase->net_quantity; // Add purchase quantity to the balance
                 $currentStockValue += $item->purchase_price * $purchase->net_quantity;
            }



             // Fetch and process purchase returns (items with item_type = 'purchase')
             $returns = PurchaseReturnItem::join('pruchase_returns', 'pruchase_returns.id', '=', 'purchase_return_items.purchase_return_id')
                 ->whereBetween('pruchase_returns.return_date', [$startDate, $endDate])
                 ->select('pruchase_returns.return_date', 'purchase_return_items.return_quantity')
                 ->get();

             foreach ($returns as $return) {
                 //$currentBalance += $return->return_quantity; // Add return quantity to the balance
                 $currentStockValue += $item->purchase_price * $purchase->net_quantity;
             }

             // Fetch and process consumption (items with item_type = 'purchase')
             $consumption = ProductionDetail::join('productions', 'productions.id', '=', 'production_details.production_id')
                 ->whereBetween('productions.production_date', [$startDate, $endDate])
                 ->select('productions.*', 'production_details.quantity_used')
                 ->get();

             foreach ($consumption as $consume) {
                 //$currentBalance -= $consume->quantity_used; // Subtract consumed quantity from the balance
                 $currentStockValue += $item->purchase_price * $purchase->net_quantity;

             }


             // Shortage and Access
             $shortages = StockMaterialAdjustment::whereBetween('adj_date', [$startDate, $endDate])
             ->where('shortage', '>', 0)
             ->get(); // Sum up the consumed quantity before the start date

             foreach ($shortages as $shortage) {
                 //$currentBalance -= $shortage->shortage; // Subtract consumed quantity from the balance
                 $currentStockValue += $item->purchase_price * $purchase->net_quantity;

             }

             // Shortage and Access
             $excesses = StockMaterialAdjustment::whereBetween('adj_date', [$startDate, $endDate])
             ->where('exccess', '>', 0)
             ->get(); // Sum up the consumed quantity before the start date

             foreach ($excesses as $excesse) {
                // $currentBalance += $excesse->exccess; // Subtract consumed quantity from the balance
                 $currentStockValue += $item->purchase_price * $purchase->net_quantity;
                }

         }

     }

        //dd($currentStockValue);

         $totalValueClosingStock = $currentBalance;
        // $totalValueClosingStock = 0;
         return $totalValueClosingStock; // Update this according to your logic
     }









}
