<?php

namespace App\Http\Controllers;

use App\Models\Items;
use App\Models\PurchaseBillItem;
use App\Models\PurchaseReturnItem;
use App\Models\Production;
use App\Models\ProductionDetail;
use App\Models\SalesInvoiceItem;

use App\Models\SalesReturnItem;
use App\Models\StockMaterialAdjustment;
use App\Models\MaterialTransfer;
use App\Models\MaterialTransferItem;

use Carbon\Carbon;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $companyId = session('company_id');

        // Initialize an empty ledger when the page is first loaded
        $ledger = [];

        // Get the current month
        $firstDayOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $lastDayOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

        // Fetch all items, filtering by company_id
        $items = Items::where('company_id', $companyId)->get();

        // Sort items by name
        $items = $items->sortBy('name');

        return view('stock.index', [
            'startDate' => $firstDayOfMonth,
            'endDate' => $lastDayOfMonth,
            'items' => $items,
            'ledger' => $ledger,
            'itemName' => 'Nil',
        ]);
    }

    public function filter(Request $request)
    {

        // Validate inputs
        $validated = $request->validate([
            'startDate' => 'required|date|before_or_equal:endDate',
            'endDate' => 'required|date|after_or_equal:startDate',
            'accountId' => 'required', // Ensure the item exists
        ]);

        // Fetch the selected item
        $item = Items::findOrFail($validated['accountId']);

        // Initialize the ledger array
        $ledger = [];

        // If item is found, get the item_name
        $itemName = $item ? $item->name : 'Item Not Found';

        // Get the balance up to the start date to calculate the opening balance
        $openingBalance = $this->calculateBalanceBeforeDate($item->id, $item->balance, $validated['startDate']);

        // Add the opening balance entry to the ledger for the start date
        $ledger[] = [
            'date' => $validated['startDate'],
            'reference_number' => '',
            'description' => 'Opening Balance',
            'stock_in' => 0,
            'stock_out' => 0,
            'balance' => $openingBalance,
        ];

        // Initialize the current balance with the calculated opening balance
        $currentBalance = $openingBalance;

        // Fetch and process purchases (items with item_type = 'purchase')
        if ($item->item_type == 'purchase') {
            $purchases = PurchaseBillItem::where('product_id', $item->id)
                ->join('purchase_bills', 'purchase_bills.id', '=', 'purchase_bill_items.purchase_bill_id')
                ->whereBetween('purchase_bills.bill_date', [$validated['startDate'], $validated['endDate']])
                ->select('purchase_bill_items.*', 'purchase_bills.bill_date', 'purchase_bills.bill_number') // Explicitly select the required fields
                ->get();

            foreach ($purchases as $purchase) {
                $currentBalance += $purchase->net_quantity; // Add purchase quantity to the balance
                $ledger[] = [
                    'date' => $purchase->bill_date,
                    'reference_number' => 'Purchases',
                    'description' => $purchase->bill_number,
                    'stock_in' => $purchase->net_quantity,
                    'stock_out' => 0,
                    'balance' => $currentBalance,
                ];
            }

            // Fetch and process purchase returns (items with item_type = 'purchase')
            $returns = PurchaseReturnItem::where('product_id', $item->id)
                ->join('pruchase_returns', 'pruchase_returns.id', '=', 'purchase_return_items.purchase_return_id')
                ->whereBetween('pruchase_returns.return_date', [$validated['startDate'], $validated['endDate']])
                ->select('pruchase_returns.return_date', 'purchase_return_items.return_quantity')
                ->get();

            foreach ($returns as $return) {
                $currentBalance -= $return->return_quantity; // Add return quantity to the balance
                $ledger[] = [
                    'date' => \Carbon\Carbon::parse($return->return_date)->format('d-m-Y'),
                    'reference_number' => 'Purchase Return',
                    'description' => 'Purchase Return',
                    'stock_in' => 0,
                    'stock_out' => $return->return_quantity,
                    'balance' => $currentBalance,
                ];
            }

            // Fetch and process consumption (items with item_type = 'purchase')
            $consumption = ProductionDetail::where('raw_material_id', $item->id)
                ->join('productions', 'productions.id', '=', 'production_details.production_id')
                ->whereBetween('productions.production_date', [$validated['startDate'], $validated['endDate']])
                ->select('productions.*', 'production_details.quantity_used')
                ->get();

            foreach ($consumption as $consume) {
                $currentBalance -= $consume->quantity_used; // Subtract consumed quantity from the balance
                $ledger[] = [
                    'date' => \Carbon\Carbon::parse($consume->production_date)->format('d-m-Y'),
                    'reference_number' => 'Consumption',
                    'description' => 'Consumption #' . $consume->id,
                    'stock_in' => 0,
                    'stock_out' => $consume->quantity_used,
                    'balance' => $currentBalance,
                ];
            }


            // Shortage and Access
            $shortages = StockMaterialAdjustment::where('material_id',  $item->id)
            ->whereBetween('adj_date', [$validated['startDate'], $validated['endDate']])
            ->where('shortage', '>', 0)
            ->get(); // Sum up the consumed quantity before the start date

            foreach ($shortages as $shortage) {
                $currentBalance -= $shortage->shortage; // Subtract consumed quantity from the balance
                $ledger[] = [
                    'date' => \Carbon\Carbon::parse($shortage->adj_date)->format('d-m-Y'),
                    'reference_number' => 'Shortage',
                    'description' => 'Stock Shortage : Adjustment ID # ' . $shortage->id ,
                    'stock_in' => 0,
                    'stock_out' => $shortage->shortage,
                    'balance' => $currentBalance,
                ];
            }

            // Shortage and Access
            $excesses = StockMaterialAdjustment::where('material_id',  $item->id)
            ->whereBetween('adj_date', [$validated['startDate'], $validated['endDate']])
            ->where('exccess', '>', 0)
            ->get(); // Sum up the consumed quantity before the start date

            foreach ($excesses as $excesse) {
                $currentBalance += $excesse->exccess; // Subtract consumed quantity from the balance
                $ledger[] = [
                    'date' => \Carbon\Carbon::parse($excesse->adj_date)->format('d-m-Y'),
                    'reference_number' => 'Exccess',
                    'description' => 'Stock Exccess : Adjustment ID # ' . $excesse->id ,
                    'stock_in' => $excesse->exccess,
                    'stock_out' => 0,
                    'balance' => $currentBalance,
                ];
            }


            // Transfers
            $transfers = MaterialTransferItem::where('item_id', $item->id)
                ->join('material_transfer_farms', 'material_transfer_farms.id', '=', 'material_transfer_farms_detail.material_transfer_id')
                ->join('chart_of_accounts', 'chart_of_accounts.id', '=', 'material_transfer_farms.farm_account')
                ->whereBetween('material_transfer_farms.transfer_date', [$validated['startDate'], $validated['endDate']])
                ->select('material_transfer_farms.*', 'material_transfer_farms_detail.quantity',  'material_transfer_farms_detail.unit_price' , 'chart_of_accounts.name as farmname')
                ->get();


            foreach ($transfers as $transfer) {
                $currentBalance -= $transfer->quantity; // Subtract consumed quantity from the balance
                $ledger[] = [
                    'date' => \Carbon\Carbon::parse($transfer->transfer_date)->format('d-m-Y'),
                    'reference_number' => 'Material Transfer',
                    'description' => $transfer->reference_number  . ', '  . $transfer->farmname .    ',  Price '  . number_format($transfer->unit_price,2) . '' ,
                    'stock_in' => 0,
                    'stock_out' => $transfer->quantity,
                    'balance' => $currentBalance,
                ];
            }


        }

        // Fetch and process sales (items with item_type = 'sale')
        if ($item->item_type == 'sale') {
            $sales = SalesInvoiceItem::where('product_id', $item->id)
                ->join('sales_invoices', 'sales_invoices.id', '=', 'sales_invoice_items.sales_invoice_id')
                ->whereBetween('sales_invoices.invoice_date', [$validated['startDate'], $validated['endDate']])
                ->select('sales_invoices.*', 'sales_invoice_items.quantity')
                ->get();

            foreach ($sales as $sale) {
                $currentBalance -= $sale->quantity; // Subtract sale quantity from the balance
                $ledger[] = [
                    'date' => \Carbon\Carbon::parse($sale->invoice_date)->format('d-m-Y'),
                    'reference_number' => 'Sale',
                    'description' => $sale->invoice_number,
                    'stock_in' => 0,
                    'stock_out' => $sale->quantity,
                    'balance' => $currentBalance,
                ];
            }

            // Fetch and process sales returns (items with item_type = 'sale')
            $returns = SalesReturnItem::where('product_id', $item->id)
                ->join('sales_returns', 'sales_returns.id', '=', 'sales_return_items.sales_return_id')
                ->whereBetween('sales_returns.return_date', [$validated['startDate'], $validated['endDate']])
                ->select('sales_returns.return_date', 'sales_return_items.return_quantity')
                ->get();

            foreach ($returns as $return) {
                $currentBalance += $return->return_quantity; // Add return quantity to the balance
                $ledger[] = [
                    'date' => \Carbon\Carbon::parse($return->return_date)->format('d-m-Y'),
                    'reference_number' => 'Sale Return',
                    'description' => 'Sale Return',
                    'stock_in' => $return->return_quantity,
                    'stock_out' => 0,
                    'balance' => $currentBalance,
                ];
            }

            // Get all relevant purchases, returns, and consumptions before the start date
            $purchases = Production::where('product_id', $item->id)
            ->whereBetween('production_date',  [$validated['startDate'], $validated['endDate']])
            ->get();

        foreach ($purchases as $purchase) {
            $currentBalance += $purchase->net_quantity; // Add purchase quantity to the balance
            $ledger[] = [
                'date' => $purchase->production_date,
                'reference_number' => 'Production',
                'description' => 'Production # ' . $purchase->id ,
                'stock_in' => $purchase->actual_produced,
                'stock_out' => 0,
                'balance' => $currentBalance,
            ];
        }

         // Shortage and Access
         $shortages = StockMaterialAdjustment::where('material_id',  $item->id)
         ->whereBetween('adj_date', [$validated['startDate'], $validated['endDate']])
         ->where('shortage', '>', 0)
         ->get(); // Sum up the consumed quantity before the start date

         foreach ($shortages as $shortage) {
             $currentBalance -= $shortage->shortage; // Subtract consumed quantity from the balance
             $ledger[] = [
                 'date' => \Carbon\Carbon::parse($shortage->adj_date)->format('d-m-Y'),
                 'reference_number' => 'Shortage',
                 'description' => 'Stock Shortage : Adjustment ID # ' . $shortage->id ,
                 'stock_in' => 0,
                 'stock_out' => $shortage->shortage,
                 'balance' => $currentBalance,
             ];
         }

         // Shortage and Access
         $excesses = StockMaterialAdjustment::where('material_id',  $item->id)
         ->whereBetween('adj_date', [$validated['startDate'], $validated['endDate']])
         ->where('exccess', '>', 0)
         ->get(); // Sum up the consumed quantity before the start date

         foreach ($excesses as $excesse) {
             $currentBalance += $excesse->exccess; // Subtract consumed quantity from the balance
             $ledger[] = [
                 'date' => \Carbon\Carbon::parse($excesse->adj_date)->format('d-m-Y'),
                 'reference_number' => 'Exccess',
                 'description' => 'Stock Exccess : Adjustment ID # ' . $excesse->id ,
                 'stock_in' => $excesse->exccess,
                 'stock_out' => 0,
                 'balance' => $currentBalance,
             ];
         }


        }

        // Sort the ledger by date to ensure chronological order
        usort($ledger, function ($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        // Recalculate the balance for each entry after sorting
        $currentBalance = $openingBalance; // Start with the opening balance again
        foreach ($ledger as $index => $entry) {
            $currentBalance += $entry['stock_in'] - $entry['stock_out'];
            $ledger[$index]['balance'] = $currentBalance;
        }

        // Fetch the company_id from the session
        $companyId = session('company_id');

        // Fetch all items again after filter
        $items = Items::where('company_id', $companyId)->get();

        // Sort items by name
        $items = $items->sortBy('name');

       // dd($ledger);

        return view('stock.index', [
            'startDate' => $validated['startDate'],
            'endDate' => $validated['endDate'],
            'ledger' => $ledger,
            'items' => $items,
            'itemName' => $itemName,
        ]);
    }

    // Method to calculate balance before the start date
    private function calculateBalanceBeforeDate($itemId, $opbal, $startDate)
    {
        // Get all relevant purchases, returns, and consumptions before the start date
        $purchasesBefore = PurchaseBillItem::where('product_id', $itemId)
            ->join('purchase_bills', 'purchase_bills.id', '=', 'purchase_bill_items.purchase_bill_id')
            ->where('purchase_bills.bill_date', '<', $startDate)
            ->sum('purchase_bill_items.net_quantity');



        $returnsBefore = PurchaseReturnItem::where('product_id', $itemId)
            ->join('pruchase_returns', 'pruchase_returns.id', '=', 'purchase_return_items.purchase_return_id')
            ->where('pruchase_returns.return_date', '<', $startDate)
            ->sum('purchase_return_items.return_quantity');

        // Fetch and process sales returns (items with item_type = 'sale')
        /*$returns = PurchaseReturnItem::where('product_id', $itemId)
        ->join('pruchase_returns', 'pruchase_returns.id', '=', 'purchase_return_items.purchase_return_id')
        ->whereBetween('pruchase_returns.return_date', [$firstDate, $lastDate])
        ->select('pruchase_returns.return_date', 'purchase_return_items.return_quantity')
        ->get();*/

        $consumptionBefore = ProductionDetail::where('raw_material_id', $itemId)
            ->join('productions', 'productions.id', '=', 'production_details.production_id')
            ->where('productions.production_date', '<', $startDate)
            ->sum('production_details.quantity_used');


          // Shortage and Access
          $shortage = StockMaterialAdjustment::where('material_id', $itemId)
          ->where('adj_date', '<', $startDate)
          ->sum('shortage'); // Sum up the consumed quantity before the start date

          // Shortage and Access
        $excess = StockMaterialAdjustment::where('material_id', $itemId)
        ->where('adj_date', '<', $startDate)
        ->sum('exccess'); // Sum up the consumed quantity before the start date


        if( $itemId == 5) {
          //  dd($returnsBefore);
        }

        // Calculate the balance before the start date
        return $opbal + $purchasesBefore   + $excess - $consumptionBefore - $shortage - $returnsBefore;
    }

    // for sales
    private function calculateBalanceBeforeDateSale($itemId, $opbal, $startDate)
    {
         // Get all relevant purchases, returns, and consumptions before the start date
         $purchasesBefore = Production::where('product_id', $itemId)
         ->where('production_date', '<', $startDate)
         ->sum('actual_produced');

        $returnsBefore = SalesReturnItem::where('product_id', $itemId)
            ->join('sales_returns', 'sales_returns.id', '=', 'sales_return_items.sales_return_id')
            ->where('sales_returns.return_date', '<', $startDate)
            ->sum('sales_return_items.return_quantity'); // Sum up the return quantity before the start date

        $consumptionBefore = SalesInvoiceItem::where('product_id', $itemId)
            ->join('sales_invoices', 'sales_invoices.id', '=', 'sales_invoice_items.sales_invoice_id')
            ->where('sales_invoices.invoice_date', '<', $startDate)
            ->sum('sales_invoice_items.quantity'); // Sum up the net quantity of purchases before the start date // Sum up the consumed quantity before the start date

          // Shortage and Access
          $shortage = StockMaterialAdjustment::where('material_id', $itemId)
          ->where('adj_date', '<', $startDate)
          ->sum('shortage'); // Sum up the consumed quantity before the start date

          // Shortage and Access
            $excess = StockMaterialAdjustment::where('material_id', $itemId)
            ->where('adj_date', '<', $startDate)
            ->sum('exccess'); // Sum up the consumed quantity before the start date

        // Calculate the balance before the start date
        return $opbal + $purchasesBefore + $returnsBefore   + $excess - $consumptionBefore - $shortage;
    }

    public function stockadjustments(Request $request) {

        return view('inventory.adjustments.index');
    }

    public function stockreport() {

        //get the products raw material and so on

        $firstDate = Carbon::now()->startOfMonth()->format('Y-m-d');

        // Get today's date in Y-m-d format
        $lastDate = now()->format('Y-m-d');

        return view('stock.reports.index', compact('firstDate','lastDate'));

    }

    public function stockreportgeneral(Request $request)
    {

        // Validate the input date
        $validated = $request->validate([
            'firstdate' => 'required|date', // Ensures valid date format
            'lastdate' => 'required|date', // Ensures valid date format
        ]);

        $firstDate = Carbon::parse($request->firstdate)->format('Y-m-d'); // Start date
        $lastDate = Carbon::parse($request->lastdate)->format('Y-m-d'); // End date

       // Retrieve all items (purchase items), excluding groups with ID 9 and 10
        $purchaseItems = Items::with('itemGroup')
        ->whereHas('itemGroup', function ($query) {
            $query->whereNotIn('id', [9, 10]); // Exclude item groups with ID 9 and 10
        })
        ->get()
        ->sortBy(function ($item) {
            return $item->itemGroup->sort_id; // Sort by sort_id of the related itemGroup
        });

        // Initialize the ledger array
        $ledger = [];

        // Group items by their group name (e.g., Raw Material, Medicine, PP Bags)
        $groupedItems = $purchaseItems->groupBy(function ($item) {
            return $item->itemGroup ? $item->itemGroup->name : 'Uncategorized'; // Group by group name, default to 'Uncategorized'
        });

        // Iterate through each purchase item and calculate the balance
        foreach ($purchaseItems as $purchaseItem) {

            //for sale items
            if($purchaseItem->item_type == 'sale') {

            // Calculate the opening balance for each item before the firstDate
            $openingBalance = $this->calculateBalanceBeforeDateSale($purchaseItem->id, $purchaseItem->balance, $firstDate);

            // Get the total purchases and corresponding prices between firstDate and lastDate
            $totalPurchasesAndPrices = $this->getPurchasesAndPricesBetweenDatesSale($purchaseItem->id, $firstDate, $lastDate);

            // Calculate the average price based on the available stock
            $averagePrice = $purchaseItem->sale_price;

            // Fetch consumption details between firstDate and lastDate
            $totalConsumption = $this->getTotalConsumptionBetweenDatesSale($purchaseItem->id, $firstDate, $lastDate);

            $totalShortage = $this->getShortages($purchaseItem->id, $firstDate, $lastDate);
            $totalExccess= $this->getExcesses($purchaseItem->id, $firstDate, $lastDate);

            $totalReturn= $this->getSaleReturn($purchaseItem->id, $firstDate, $lastDate);

              // Closing balance is calculated as opening balance + purchases - consumption
            $closingBalance = $openingBalance + $totalPurchasesAndPrices['totalPurchases']  +  $totalReturn + $totalExccess - ($totalShortage + $totalConsumption);

            // Add the opening balance entry to the ledger for each item
            $ledger[] = [
                'item_name' => $purchaseItem->name,
                'category' => $purchaseItem->itemGroup ? $purchaseItem->itemGroup->name : 'Uncategorized',
                'opening_balance' => $openingBalance,
                'total_purchases' => $totalPurchasesAndPrices['totalPurchases'],
                'available_balance' => $openingBalance + $totalPurchasesAndPrices['totalPurchases'],
                'total_consumption' => $totalConsumption,
                'total_shortage' => $totalShortage,
                'total_exccess' => $totalExccess,
                'closing_balance' => $closingBalance,
                'average_price' => $averagePrice,
                'total_return' =>  $totalReturn,
                'value_of_consumption' => $totalConsumption * $averagePrice,
                'value_of_closing_stock' => $closingBalance * $averagePrice,
                'value_of_opening_stock' => $openingBalance * $averagePrice,
                'item_type' => 'sale',
            ];

            //for purchase items
            } else {


            // Calculate the opening balance for each item before the firstDate
            $openingBalance = $this->calculateBalanceBeforeDate($purchaseItem->id, $purchaseItem->balance, $firstDate);


            if($purchaseItem->id == 5 ) {
               // dd($openingBalance);
            }

            // Get the total purchases and corresponding prices between firstDate and lastDate
            $totalPurchasesAndPrices = $this->getPurchasesAndPricesBetweenDates($purchaseItem->id, $firstDate, $lastDate);

            // Calculate the average price based on the available stock
            $averagePrice = $purchaseItem->purchase_price;

            // Fetch consumption details between firstDate and lastDate
            $totalConsumption = $this->getTotalConsumptionBetweenDates($purchaseItem->id, $firstDate, $lastDate);

            $totalShortage = $this->getShortages($purchaseItem->id, $firstDate, $lastDate);
            $totalExccess= $this->getExcesses($purchaseItem->id, $firstDate, $lastDate);

            $totalTransfer= $this->getTransfer($purchaseItem->id, $firstDate, $lastDate);

            $totalPurchaseReturn= $this->getPurchaseReturn($purchaseItem->id, $firstDate, $lastDate);

            // Closing balance is calculated as opening balance + purchases - consumption
            $closingBalance = $openingBalance + $totalPurchasesAndPrices['totalPurchases']   + $totalExccess - ($totalShortage + $totalConsumption + $totalTransfer + $totalPurchaseReturn);

            // Add the opening balance entry to the ledger for each item
            $ledger[] = [
                'item_name' => $purchaseItem->name,
                'category' => $purchaseItem->itemGroup ? $purchaseItem->itemGroup->name : 'Uncategorized',
                'opening_balance' => $openingBalance,
                'total_purchases' => $totalPurchasesAndPrices['totalPurchases'],
                'available_balance' => $openingBalance + $totalPurchasesAndPrices['totalPurchases'],
                'total_consumption' => $totalConsumption,
                'total_shortage' => $totalShortage,
                'total_exccess' => $totalExccess,
                'total_return' =>  $totalPurchaseReturn,
                'closing_balance' => $closingBalance,
                'average_price' => $averagePrice,
                'value_of_consumption' => $totalConsumption * $averagePrice,
                'value_of_closing_stock' => $closingBalance * $averagePrice,
                'value_of_opening_stock' => $openingBalance * $averagePrice,
                'item_type' => 'purchase',
            ];

            }
        }


        return view('stock.reports.stock_report', compact('ledger', 'groupedItems', 'firstDate' , 'lastDate'));
    }

    private function getPurchasesAndPricesBetweenDates($itemId, $firstDate, $lastDate)
    {
        // Retrieve total purchases between the firstDate and lastDate and calculate the corresponding price
        $purchases = PurchaseBillItem::where('product_id', $itemId)
            ->whereHas('bill', function ($query) use ($firstDate, $lastDate) {
                $query->whereBetween('bill_date', [$firstDate, $lastDate]);
            })
            ->get();

        $totalPurchases = 0;
        foreach ($purchases as $purchase) {
            $totalPurchases += $purchase->quantity;
        }

        return [
            'totalPurchases' => $totalPurchases,
        ];
    }

    private function getPurchasesAndPricesBetweenDatesSale($itemId, $firstDate, $lastDate)
    {


        // Retrieve total purchases between the firstDate and lastDate and calculate the corresponding price
        $purchases = Production::where('product_id', $itemId)
            ->whereBetween('production_date',  [$firstDate,$lastDate])
            ->get();


        $totalPurchases = 0;
        foreach ($purchases as $purchase) {
            $totalPurchases += $purchase->actual_produced;
        }

        return [
            'totalPurchases' => $totalPurchases,
        ];
    }

    private function getTotalConsumptionBetweenDates($itemId, $firstDate, $lastDate)
    {
        // Get total consumption of an item between the firstDate and lastDate
        $consumptions = ProductionDetail::where('raw_material_id', $itemId)
            ->whereHas('production', function ($query) use ($firstDate, $lastDate) {
                $query->whereBetween('production_date', [$firstDate, $lastDate]);
            })
            ->get();

        $totalConsumption = 0;
        foreach ($consumptions as $consumption) {
            $totalConsumption += $consumption->quantity_used;
        }

        return $totalConsumption;
    }

    private function getTotalConsumptionBetweenDatesSale($itemId, $firstDate, $lastDate)
    {
        // Get total consumption of an item between the firstDate and lastDate
        $consumptions = SalesInvoiceItem::where('product_id', $itemId)
                    ->join('sales_invoices', 'sales_invoices.id', '=', 'sales_invoice_items.sales_invoice_id')
                    ->whereBetween('sales_invoices.invoice_date', [$firstDate, $lastDate])
                    ->select('sales_invoices.*','sales_invoice_items.quantity')
                    ->get();

        $totalConsumption = 0;
        foreach ($consumptions as $consumption) {
            $totalConsumption += $consumption->quantity;
        }

        return $totalConsumption;
    }

    private function getTransfer($itemId, $firstDate, $lastDate)
    {
        $excessesgets = MaterialTransferItem::where('item_id', $itemId)
            ->join('material_transfer_farms', 'material_transfer_farms.id', '=', 'material_transfer_farms_detail.material_transfer_id')
            ->whereBetween('material_transfer_farms.transfer_date', [$firstDate, $lastDate])
            ->where('material_transfer_farms_detail.quantity', '>', 0)
            ->get();

        $totalExccess= 0;
        foreach ($excessesgets as $excessesget) {
                $totalExccess += $excessesget->quantity;
            }

        return $totalExccess;
    }



    private function getShortages($itemId, $firstDate, $lastDate)
    {
        $shortagesgets = StockMaterialAdjustment::where('material_id', $itemId)
            ->whereBetween('adj_date', [$firstDate, $lastDate])
            ->where('shortage', '>', 0)
            ->get();

        $totalShortage = 0;
        foreach ($shortagesgets as $shortagesget) {
            $totalShortage += $shortagesget->shortage;
        }

        return $totalShortage;
    }

    private function getExcesses($itemId, $firstDate, $lastDate)
    {
        $excessesgets = StockMaterialAdjustment::where('material_id', $itemId)
            ->whereBetween('adj_date', [$firstDate, $lastDate])
            ->where('exccess', '>', 0)
            ->get();

        $totalExccess= 0;
        foreach ($excessesgets as $excessesget) {
                $totalExccess += $excessesget->exccess;
            }

        return $totalExccess;
    }


    private function getSaleReturn($itemId, $firstDate, $lastDate)
    {

        // Fetch and process sales returns (items with item_type = 'sale')
        $returns = SalesReturnItem::where('product_id', $itemId)
        ->join('sales_returns', 'sales_returns.id', '=', 'sales_return_items.sales_return_id')
        ->whereBetween('sales_returns.return_date', [$firstDate, $lastDate])
        ->select('sales_returns.return_date', 'sales_return_items.return_quantity')
        ->get();

        $totalExccess= 0;
        foreach ($returns as $return) {
                $totalExccess += $return->return_quantity;
            }

        return $totalExccess;
    }


    private function getPurchaseReturn($itemId, $firstDate, $lastDate)
    {

        // Fetch and process sales returns (items with item_type = 'sale')
        $returns = PurchaseReturnItem::where('product_id', $itemId)
                ->join('pruchase_returns', 'pruchase_returns.id', '=', 'purchase_return_items.purchase_return_id')
                ->whereBetween('pruchase_returns.return_date', [$firstDate, $lastDate])
                ->select('pruchase_returns.return_date', 'purchase_return_items.return_quantity')
                ->get();




        $totalExccess= 0;
        foreach ($returns as $return) {
                $totalExccess += $return->return_quantity;
            }

        return $totalExccess;
    }



}
