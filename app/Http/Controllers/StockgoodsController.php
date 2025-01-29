<?php

namespace App\Http\Controllers;

use App\Models\SalesProduct;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Production;
use App\Models\ProductionDetail;
use App\Model\ChartOfAccount;

use Illuminate\Http\Request;
use Carbon\Carbon;

class StockgoodsController extends Controller
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
        $salesItems = SalesProduct::where('company_id', $companyId)->get();

        // Sort items by name
        $items = $salesItems->sortBy('product_name');

        return view('stockgoods.index', [
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
        $salesItems = SalesProduct::findOrFail($validated['accountId']);

        // Initialize the ledger array
        $ledger = [];

         // If salesItems is found, get the item_name
        $itemName = $salesItems ? $salesItems->product_name : 'Item Not Found';


        // Get the balance up to the start date to calculate the opening balance
        $openingBalance = $this->calculateBalanceBeforeDate($salesItems->id, $salesItems->balance, $validated['startDate']);

        //dd($openingBalance);


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

        // Get all relevant purchases, returns, and consumptions before the start date
        $purchases = Production::where('product_id', $salesItems->id)
            ->whereBetween('production_date',  [$validated['startDate'], $validated['endDate']])
            ->get();

        foreach ($purchases as $purchase) {
            $currentBalance += $purchase->net_quantity; // Add purchase quantity to the balance
            $ledger[] = [
                'date' => $purchase->production_date,
                'description' => 'Production # ' . $purchase->id ,
                'stock_in' => $purchase->actual_produced,
                'stock_out' => 0,
                'balance' => $currentBalance,
            ];
        }

        $returns = SalesReturnItem::where('product_id', $salesItems->id)
        ->join('sales_returns', 'sales_returns.id', '=', 'sales_return_items.sales_return_id')
        ->whereBetween('sales_returns.return_date',  [$validated['startDate'], $validated['endDate']])
        ->select('sales_returns.return_date','sales_return_items.return_quantity')
        ->get(); // Sum up the return quantity before the start date


        foreach ($returns as $return) {
            $currentBalance += $return->return_quantity; // Add return quantity to the balance
            $ledger[] = [
                'date' => \Carbon\Carbon::parse($return->return_date)->format('d-m-Y') ,
                'description' => 'Return',
                'stock_in' => $return->return_quantity,
                'stock_out' => 0,
                'balance' => $currentBalance,
            ];
        }


        $consumption = SalesInvoiceItem::where('product_id', $salesItems->id)
        ->join('sales_invoices', 'sales_invoices.id', '=', 'sales_invoice_items.sales_invoice_id')
        ->whereBetween('sales_invoices.invoice_date', [$validated['startDate'], $validated['endDate']])
        ->select('sales_invoices.*','sales_invoice_items.quantity')
        ->get();


        foreach ($consumption as $consume) {
            $currentBalance -= $consume->quantity_used; // Subtract consumed quantity from the balance
            $ledger[] = [
                'date' => \Carbon\Carbon::parse($consume->invoice_date)->format('d-m-Y'),
                'description' => $consume->invoice_number,
                'stock_in' => 0,
                'stock_out' => $consume->quantity,
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
        $salesItems = SalesProduct::all();

        // Sort items by name
        $items = $salesItems->sortBy('name');

        return view('stockgoods.index', [
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
        $purchasesBefore = Production::where('product_id', $productId)
            ->where('production_date', '<', $startDate)
            ->sum('actual_produced');

        $returnsBefore = SalesReturnItem::where('product_id', $productId)
            ->join('sales_returns', 'sales_returns.id', '=', 'sales_return_items.sales_return_id')
            ->where('sales_returns.return_date', '<', $startDate)
            ->sum('sales_return_items.return_quantity'); // Sum up the return quantity before the start date

        $consumptionBefore = SalesInvoiceItem::where('product_id', $productId)
            ->join('sales_invoices', 'sales_invoices.id', '=', 'sales_invoice_items.sales_invoice_id')
            ->where('sales_invoices.invoice_date', '<', $startDate)
            ->sum('sales_invoice_items.quantity'); // Sum up the net quantity of purchases before the start date // Sum up the consumed quantity before the start date

        // Calculate the balance before the start date
        return $opbal + $purchasesBefore + $returnsBefore - $consumptionBefore;
    }




}
