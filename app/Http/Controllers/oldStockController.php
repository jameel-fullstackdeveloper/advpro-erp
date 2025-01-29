<?php

namespace App\Http\Controllers;

use App\Models\SalesProduct;
use App\Models\PurchaseItem;
use App\Models\PurchaseBillItem;
use App\Models\PurchaseReturnItem;
use App\Models\ProductionDetail;
use App\Models\ChartOfAccount;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Items;

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

        // Fetch all sales and purchase items
        //$salesItems = SalesProduct::all();
        $purchaseItems = Items::where('company_id', $companyId)->get();

       /* $items = $salesItems->merge($purchaseItems)->map(function ($item) {
            // You can add a prefix to distinguish between sales and purchase items
            $item->name = isset($item->product_name) ? $item->product_name : $item->item_name;
            return $item;
        });*/

         // Sort items by name
         $items = $purchaseItems->sortBy('item_name');

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
            'accountId' => 'required', // Ensure the purchase item exists
        ]);

        // Fetch the selected purchase item
        $purchaseItem = Items::findOrFail($validated['accountId']);

        // Initialize the ledger array
        $ledger = [];

         // If purchaseItem is found, get the item_name
        $itemName = $purchaseItem ? $purchaseItem->item_name : 'Item Not Found';


        // Get the balance up to the start date to calculate the opening balance
        $openingBalance = $this->calculateBalanceBeforeDate($purchaseItem->id, $purchaseItem->balance, $validated['startDate']);

        // Add the opening balance entry to the ledger for the start date
        $ledger[] = [
            'date' => $validated['startDate'],
            'description' => 'Opening Balance',
            'stock_in' => 0,
            'stock_out' => 0,
            'balance' => $openingBalance,
        ];

        // Initialize the current balance with the calculated opening balance
        $currentBalance = $openingBalance;

        // Fetch and process purchases within the selected date range
        $purchases = PurchaseBillItem::where('product_id', $purchaseItem->id)
            ->join('purchase_bills', 'purchase_bills.id', '=', 'purchase_bill_items.purchase_bill_id')
            ->whereBetween('purchase_bills.bill_date', [$validated['startDate'], $validated['endDate']])
            ->select('purchase_bill_items.*', 'purchase_bills.bill_date', 'purchase_bills.bill_number') // Explicitly select the required fields
            ->get();

        foreach ($purchases as $purchase) {
            $currentBalance += $purchase->net_quantity; // Add purchase quantity to the balance
            $ledger[] = [
                'date' => $purchase->bill_date,
                'description' => $purchase->bill_number,
                'stock_in' => $purchase->net_quantity,
                'stock_out' => 0,
                'balance' => $currentBalance,
            ];
        }

        // Fetch and process purchase returns within the selected date range
        $returns = PurchaseReturnItem::where('product_id', $purchaseItem->id)
            ->join('pruchase_returns', 'pruchase_returns.id', '=', 'purchase_return_items.purchase_return_id')
            ->whereBetween('pruchase_returns.return_date', [$validated['startDate'], $validated['endDate']])
            ->select('pruchase_returns.*', 'purchase_return_items.return_quantity')
            ->get();

        foreach ($returns as $return) {
            $currentBalance += $return->return_quantity; // Add return quantity to the balance
            $ledger[] = [
                'date' =>  \Carbon\Carbon::parse($return->return_date)->format('d-m-Y'),
                'description' => 'Return',
                'stock_in' => $return->return_quantity,
                'stock_out' => 0,
                'balance' => $currentBalance,
            ];
        }

        // Fetch and process consumption data within the selected date range
        $consumption = ProductionDetail::where('raw_material_id', $purchaseItem->id)
            ->join('productions', 'productions.id', '=', 'production_details.production_id')
            ->whereBetween('productions.production_date', [$validated['startDate'], $validated['endDate']])
            ->select('productions.*', 'production_details.quantity_used')
            ->get();

        foreach ($consumption as $consume) {
            $currentBalance -= $consume->quantity_used; // Subtract consumed quantity from the balance
            $ledger[] = [
                'date' => \Carbon\Carbon::parse($consume->production_date)->format('d-m-Y'),
                'description' => 'Consumption #' . $consume->id ,
                'stock_in' => 0,
                'stock_out' => $consume->quantity_used,
                'balance' => $currentBalance,
            ];
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

        // Fetch all sales and purchase items
        $purchaseItems = Items::all();



        // Sort items by name
        $items = $purchaseItems->sortBy('name');

        return view('stock.index', [
            'startDate' => $validated['startDate'],
            'endDate' => $validated['endDate'],
            'ledger' => $ledger,
            'items' => $items,
            'itemName' => $itemName,
        ]);
    }

    // Method to calculate balance before the start date
    private function calculateBalanceBeforeDate($productId, $opbal,$startDate)
    {
        // Get all relevant purchases, returns, and consumptions before the start date
        $purchasesBefore = PurchaseBillItem::where('product_id', $productId)
            ->join('purchase_bills', 'purchase_bills.id', '=', 'purchase_bill_items.purchase_bill_id')
            ->where('purchase_bills.bill_date', '<', $startDate)
            ->sum('purchase_bill_items.net_quantity'); // Sum up the net quantity of purchases before the start date

        $returnsBefore = PurchaseReturnItem::where('product_id', $productId)
            ->join('pruchase_returns', 'pruchase_returns.id', '=', 'purchase_return_items.purchase_return_id')
            ->where('pruchase_returns.return_date', '<', $startDate)
            ->sum('purchase_return_items.return_quantity'); // Sum up the return quantity before the start date

        $consumptionBefore = ProductionDetail::where('raw_material_id', $productId)
            ->join('productions', 'productions.id', '=', 'production_details.production_id')
            ->where('productions.production_date', '<', $startDate)
            ->sum('production_details.quantity_used'); // Sum up the consumed quantity before the start date

        // Calculate the balance before the start date
        return $opbal + $purchasesBefore + $returnsBefore - $consumptionBefore;
    }




}
