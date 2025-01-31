<?php

namespace App\Livewire\Accounting;

use Livewire\Component;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountGroup;
use App\Models\ChartOfAccountsType;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\SalesInvoiceFarm;
use App\Models\SalesInvoiceFarmItem;

use App\Models\PurchaseFarmBill;
use App\Models\PurchaseFarmBillItem;

use App\Models\MaterialTransfer;
use App\Models\MaterialTransferItem;





use PDF;

class Ledger extends Component
{
    public $startDate;
    public $endDate;
    public $accountId;
    public $accountTitle;
    public $ledgerEntries = [];

    public $accountDrCr;
    public $accountNature;
    public $accountCategory; // Add this property


    protected $rules = [
        'startDate' => 'required|date',
        'endDate' => 'required|date|after_or_equal:startDate',
        'accountId' => 'required',
    ];

    public function mount()
    {
        abort_if(!auth()->user()->can('accounting ledgers view'), 403);

        // Set default values for startDate and endDate to the current month's start and end dates
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function render()
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session

        // Fetch accounts filtered by company_id
        $accounts = ChartOfAccount::get(); // Filter by company_id

        // Fetch account types along with related account groups and accounts, filtered by company_id
        $accountTypes = ChartOfAccountsType::with(['chartOfAccountGroups.chartOfAccounts' => function($query) use ($companyId) {
            //$query->where('company_id', $companyId); // Filter related accounts by company_id
        }])->get();

        return view('livewire.accounting.ledger', [
            'accounts' => $accounts,
            'accountTypes' => $accountTypes,
        ]);
    }


    private function calculateRunningBalance(&$balance, $accountCategory)
    {
        foreach ($this->ledgerEntries as $entry) {
            $amount = (float) $entry->amount;

            switch ($accountCategory) {
                case 'Assets':
                case 'Expenses':
                    if ($entry->type == 'debit') {
                        $balance += $amount;
                    } elseif ($entry->type == 'credit') {
                        $balance -= $amount;
                    }
                    break;

                case 'Liabilities':
                case 'Revenue':
                case 'Equity':
                    if ($entry->type == 'debit') {
                        $balance -= $amount;
                    } elseif ($entry->type == 'credit') {
                        $balance += $amount;
                    }
                    break;

                default:
                    break;
            }

            // Ensure the balance is stored correctly for each entry
            //$entry->balance = abs($balance);
            //$entry->balance .= $balance >= 0 ? ' Dr.' : ' Cr.';

            // Pass the raw balance to the Blade template without formatting or suffix
            $entry->balance = $balance;
        }
    }


    public function generateLedger()
    {
        $this->validate();

        // Fetch the account and its nature
        $account = ChartOfAccount::where('chart_of_accounts.id', $this->accountId)
            ->join('chart_of_accounts_groups', 'chart_of_accounts.group_id', '=', 'chart_of_accounts_groups.id')
            ->join('chart_of_accounts_types', 'chart_of_accounts_groups.type_id', '=', 'chart_of_accounts_types.id')
            ->select('chart_of_accounts.balance', 'chart_of_accounts.drcr', 'chart_of_accounts_types.category')
            ->first();

        $this->accountNature = $account->drcr; // 'Dr.' or 'Cr.'


        $accountCategory = $account->category;
        $this->accountCategory = $account->category;

        // Adjust the balance based on the nature (Cr or Dr)
        $balance = $account->balance;

        // Calculate transactions before the start date
        $transactionBalance = DB::table('voucher_details')
            ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
            ->where('voucher_details.account_id', $this->accountId)
            ->where('vouchers.date', '<', $this->startDate)
            ->select(DB::raw("
                SUM(CASE WHEN voucher_details.type = 'debit' THEN voucher_details.amount ELSE 0 END) -
                SUM(CASE WHEN voucher_details.type = 'credit' THEN voucher_details.amount ELSE 0 END)
            AS balance"))
            ->value('balance');

            if($this->accountNature === 'Dr.' && in_array($accountCategory, ['Liabilities', 'Revenue', 'Equity'])) {
                // Adjust the opening balance with pre-start transaction balance
                $balance += (float)$transactionBalance;
                //dd($transactionBalance);
            }

        // Adjust balance based on account nature and category
        if ($this->accountNature === 'Cr.' && in_array($accountCategory, ['Liabilities', 'Revenue', 'Equity'])) {
            // Liabilities, Revenue, and Equity accounts should reduce balance with debits and increase balance with credits
            $balance -= (float)$transactionBalance; // Debits reduce the balance
        } elseif ($this->accountNature === 'Dr.' && in_array($accountCategory, ['Assets', 'Expenses'])) {
            // Assets and Expenses accounts should increase balance with debits and reduce with credits
            $balance += (float)$transactionBalance; // Credits reduce the balance
        } else {
            // Flip the balance if necessary (unusual cases like Liabilities with a Debit balance)
            $balance = -$balance;
        }


            // Fetch the ledger entries within the selected date range
            $this->ledgerEntries = DB::table('voucher_details')
            ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
            ->where('voucher_details.account_id', $this->accountId)
            ->whereBetween('vouchers.date', [$this->startDate, $this->endDate])
            ->orderBy('vouchers.date')
            ->select('voucher_details.*', 'vouchers.date as voucher_date','vouchers.voucher_type','vouchers.reference_number', 'vouchers.description as voucher_description', 'voucher_details.narration as voucher_narration')
            ->get();





                    // Add full_description dynamically to each entry in the collection
        $this->ledgerEntries = $this->ledgerEntries->map(function ($entry) {
            // Check if reference type is Sales Invoice (INV) or Purchase Bill (PB)


            if ($entry->voucher_type == 'sales-invoice' || $entry->voucher_type == 'purchase-bill' || $entry->voucher_type == 'sales-invoice-farm' || $entry->voucher_type ==  'purchase-bill-farm') {
                // If reference_type is INV (Sales Invoice)
                if ($entry->voucher_type == 'sales-invoice' ) {


                    // Fetch the sales invoice using the reference_id
                    $salesInvoice = SalesInvoice::where('invoice_number', $entry->reference_number)->first();


                    if ($salesInvoice) {
                        // Retrieve the sale items related to the invoice
                        $saleItems = SalesInvoiceItem::where('sales_invoice_id', $salesInvoice->id)->get();

                        $salesOrder = $salesInvoice->salesOrder;

                        // Build the description based on the sales invoice and sale items
                        $entry->full_description =  $salesInvoice->description;
                        // Optionally, you can add item details if needed

                        // Add some details from the SalesOrder
                        if ($salesOrder) {
                            if($salesOrder->farm_name) {
                                $entry->full_description .=  ' ' . $salesOrder->farm_name;
                                $entry->full_description .= ', Vehicle # ' . $salesOrder->vehicle_no;
                            } else  {
                                $entry->full_description .= 'Vehicle # ' . $salesOrder->vehicle_no;

                            }
                        }

                        foreach ($saleItems as $item) {
                            $productName = $item->product ? $item->product->name : 'Unknown Product';
                            $entry->full_description .= ', ' . $productName . ' (' . $item->quantity . ' Bags, ' . ' Rate: ' . number_format($item->unit_price, 2) . ')';
                            $entry->full_description .= !empty($salesInvoice->comments) ? ', ' . $salesInvoice->comments : '';


                        }


                    } else {
                        // Fallback if no sales invoice found
                        $entry->full_description = 'Invoice not found';
                    }
                }

                elseif ($entry->voucher_type == 'sales-invoice-farm') {

                     // Fetch the sales invoice using the reference_id
                     $salesInvoice = SalesInvoiceFarm::where('invoice_number', $entry->reference_number)->first();


                     if ($salesInvoice) {
                         // Retrieve the sale items related to the invoice
                         $saleItems = SalesInvoiceFarmItem::where('sales_invoice_farm_id', $salesInvoice->id)->get();

                         $entry->full_description = 'Vehicle # ' . $salesInvoice->vehicle_no;
                         $entry->full_description .= !empty($salesInvoice->comments) ? ', ' . $salesInvoice->comments : '';
                         $entry->full_description .= '<br/>';

                         }

                         foreach ($saleItems as $item) {
                             $productName = $item->product ? $item->product->name : 'Unknown Product';
                             $entry->full_description .= '<strong>' . $productName . '</strong> (' . $item->quantity . ' Kgs, ' . ' Rate: ' . number_format($item->unit_price, 2) .  ', Rs.' . number_format($item->net_amount) . ')';

                             $entry->full_description .= '<br>';
                         }




                }
                // If reference_type is PB (Purchase Bill)
                elseif ($entry->voucher_type == 'purchase-bill') {

                    // Fetch the purchase bill using the reference_id
                    $purchaseBill = PurchaseBill::where('bill_number', $entry->reference_number)->first();

                    if ($purchaseBill) {
                        // Retrieve the purchase bill items
                        $purchaseBillItems = PurchaseBillItem::where('purchase_bill_id', $purchaseBill->id)->get();

                         // Build the description based on the purchase bill and purchase items
                            $entry->full_description = 'Vehicle # ' . $purchaseBill->vehicle_no;
                            $entry->full_description .= !empty($purchaseBill->comments) ? ', ' . $purchaseBill->comments : '';
                            $entry->full_description .= '<br>';

                            // Optionally, you can add item details if needed
                        foreach ($purchaseBillItems as $item) {
                               // Fetch the item details from PurchaseItem
                                $purchaseItem = $item->product; // Assuming you have a relationship defined as 'purchaseItem' in the PurchaseBillItem model

                                if ($purchaseItem) {
                                    $itemName = $purchaseItem->name; // Item name from the PurchaseItem model
                                    $entry->full_description .= ' <strong>' . $itemName . '</strong>, Qty: ' . $item->quantity . ', Rate: ' . $item->price  . ' Rs.' . number_format($item->net_amount,2);
                                    $entry->full_description .= '<br>';

                                }
                        }
                    } else {
                        // Fallback if no purchase bill found
                        $entry->full_description = 'Purchase Bill not found';
                    }
                }

                elseif ($entry->voucher_type == 'purchase-bill-farm') {

                     // Fetch the purchase bill using the reference_id
                     $purchaseBill = PurchaseFarmBill::where('bill_number', $entry->reference_number)->first();

                     if ($purchaseBill) {
                         // Retrieve the purchase bill items
                         $purchaseBillItems = PurchaseFarmBillItem::where('purchase_farm_bill_id', $purchaseBill->id)->get();

                          // Build the description based on the purchase bill and purchase items
                             $entry->full_description = 'Vehicle # ' . $purchaseBill->vehicle_no;
                             $entry->full_description .= !empty($purchaseBill->comments) ? ', ' . $purchaseBill->comments : '';
                             $entry->full_description .= '<br>';


                             // Optionally, you can add item details if needed
                         foreach ($purchaseBillItems as $item) {
                                // Fetch the item details from PurchaseItem
                                 $purchaseItem = $item->product; // Assuming you have a relationship defined as 'purchaseItem' in the PurchaseBillItem model

                                 if ($purchaseItem) {
                                     $itemName = $purchaseItem->name; // Item name from the PurchaseItem model
                                     $entry->full_description .= ' <strong>' . $itemName . '</strong>, Qty: ' . $item->quantity . ', Rate: ' . $item->price . ', Rs.' . number_format($item->net_amount,2);
                                    $entry->full_description .= '<br>';
                                     $entry->full_description .= '<br>';

                                 }
                         }
                     } else {
                         // Fallback if no purchase bill found
                         $entry->full_description = 'Purchase Bill not found';
                     }



               }


            } elseif($entry->voucher_type == 'material-transfer') {

                $materialTransfer = MaterialTransfer::with('items.product', 'farm')
                ->where('reference_number', $entry->reference_number)
                ->first();

                $descriptionParts = [];

                foreach ($materialTransfer->items as $item) {
                    $productName = $item->product->name ?? 'Unknown Product';
                    $quantity = $item->quantity;
                    $unitPrice = number_format($item->unit_price, 2);

                    $descriptionParts[] = "{$productName} ({$quantity} x {$unitPrice})";
                }

                // Join all product details into a single string
                $entry->full_description = implode(', ', $descriptionParts);
            }

            else {
                // Existing logic for other reference types
                if (strpos($entry->voucher_description, 'Invoice') !== false || strpos($entry->voucher_description, 'Purchase Bill') !== false) {

                    // If narration contains "Invoice #", remove the "Invoice #" and use the rest of the narration
                    if (strpos($entry->narration, 'Invoice #') !== false) {
                        // Remove the part before and including "Invoice #" and the first space after the number
                        $entry->full_description = preg_replace('/^Invoice #[^ ]+ - /', '', $entry->narration);
                    }
                    // If narration contains "Purchases for Bill #", remove the "Purchases for Bill #" part
                    elseif (strpos($entry->narration, 'Purchases for Bill') !== false) {
                        $entry->full_description = preg_replace('/^Purchases for Bill #[^ ]+ /', '', $entry->narration);
                    } else {
                        $entry->full_description = $entry->narration;
                    }

                } else {



                    // If voucher_description and narration are the same, use the first one
                    if ($entry->voucher_description === $entry->narration) {

                        if($entry->voucher_description == "0"){
                            $entry->full_description= ' ';
                        } else {
                            $entry->full_description = ' ' . $entry->voucher_description;

                        }

                    } else {

                        // Otherwise, concatenate voucher description and narration
                        //$entry->full_description = $entry->voucher_description . ' ' . $entry->narration;
                        $entry->full_description = $entry->voucher_description . ' ' . $entry->narration;

                    }
                }

                // Fetch account names related to the reference_number
                $accountNames = $this->getAccountNameFromReferenceNumber($entry->reference_number);

                // Get the name of the account that corresponds to the selected account ID
                $selectedAccount = ChartOfAccount::find($this->accountId);
                $selectedAccountName = $selectedAccount ? $selectedAccount->name : '';

                // Now, check if the account_id is different from the current $this->accountId



            }
            return $entry;
        });


        // Include the opening balance as the first entry in the ledger
        $this->ledgerEntries->prepend((object)[
            'voucher_date' => $this->startDate,
            'voucher_description' => 'Opening Balance',
            'narration' => null,
            'full_description' => 'Opening Balance', // Add opening balance description
            'reference_number' => null, // Add opening balance description
            'amount' => null,
            'type' => null,
            'balance' => $balance, // Include the adjusted opening balance
        ]);

        //dd($this->ledgerEntries);

        // Calculate running balance
        $this->calculateRunningBalance($balance, $accountCategory);

        // Set the account title
        $this->accountTitle = ChartOfAccount::find($this->accountId)->name;

        // Calculate the ending balance to be shown at the top
        $this->endingBalance = abs($balance);
        $this->balanceSuffix = $this->accountNature; // Use the account's nature directly as the suffix
    }


    private function calculateInitialBalance($balance, $accountNature, $accountCategory)
    {
        switch ($accountCategory) {
            case 'Assets':
            case 'Expenses':
                return $accountNature === 'Dr.' ? $balance : -$balance;

            case 'Liabilities':
            case 'Revenue':
            case 'Equity':
                return $accountNature === 'Cr.' ? $balance : -$balance;

            default:
                return $balance;
        }
    }



    public function printLedger()
    {
        // Dispatch a browser event to trigger the print dialog
        $this->dispatch('printLedger');
    }

    public function downloadPdf()
    {
        $data = [
            'ledgerEntries' => $this->ledgerEntries,
            'totalDebit' => 0,
            'totalCredit' => 0,
            'endingBalance' => 0,
            'accountTitle' => $this->accountTitle,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ];

        $pdf = PDF::loadView('accounting.ledgers.pdf', $data);
        return response()->streamDownload(
            fn () => print($pdf->output()),
            "ledger_" . now()->format('Ymd_His') . ".pdf"
        );
    }

    public function getAccountNameFromReferenceNumber($referenceNumber)
    {
        // Fetch the voucher based on the reference number
        $voucher = Voucher::where('reference_number', $referenceNumber)->first();

        if ($voucher) {
            // Fetch the voucher details associated with this voucher
            $voucherDetails = $voucher->voucherDetails;

            // Initialize an empty array to hold the account names
            $accountNames = [];

            // Loop through the voucher details and get the account names
            foreach ($voucherDetails as $voucherDetail) {
                // Fetch the account name using the account_id
                $account = $voucherDetail->account;

                if ($account) {
                    // Add the account name to the list
                    $accountNames[] = $account->name;
                }
            }

            return $accountNames;
        } else {
            // Return a message if no voucher is found
            return ['Voucher not found for reference number: ' . $referenceNumber];
        }
    }


}
