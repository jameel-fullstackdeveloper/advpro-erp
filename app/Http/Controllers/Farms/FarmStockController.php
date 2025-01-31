<?php

namespace App\Http\Controllers\Farms;

use App\Http\Controllers\Controller;
use App\Models\MaterialTransfer;
use App\Models\MaterialTransferItem;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\CustomerDetail;
use App\Models\ChartOfAccount; // Import ChartOfAccount for customers
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Items;

class FarmStockController extends Controller
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

        abort_if(!auth()->user()->can('sales invocies create'), 403);

        $products = Items::where(function ($query) {
            $query->where('item_type', 'purchase') // for item_type 'sale'
                  ->orWhere(function ($query) {

                  });
        })
        ->orderBy('name')
        ->get();

        $farmaccounts = ChartOfAccount::where(function($query) {
            $query->where('is_farm', 1);
        })
        ->orderBy('name')
        ->get();

        $RefNumber = $this->generateRefNumber();

        return view('farms.stock.create',compact('products','farmaccounts','RefNumber'));
    }

    public function store(Request $request) {

        // Get current year
        $currentYear = date('Y');

        // Define your maximum allowed back year (e.g., 2 years ago)
        $allowedYear = $currentYear - 2;


            // Validate the invoice inputs
            $customMessages = [
                'items.*.product_id.required' => 'Product is required.',
                'items.*.quantity.required' => 'Quantity is required.',
                'items.*.quantity.min' => 'Quantity must be at least 1.',
                'items.*.unit_price.required' => 'Unit price is required.',
                'items.*.unit_price.min' => 'The field must be at least 1.',
                'items.*.net_amount.required' => 'Amount excluding tax is required.',
                'items.*.amount_incl_tax.required' => 'Amount including tax is required.',
            ];

            // Validate form data
            $validated = $request->validate([
                'reference_number' => 'required',
                'farm_account' => 'required',
                'invoice_date' => 'required|date|date_format:Y-m-d|after_or_equal:' . $allowedYear . '-01-01|before_or_equal:' . $currentYear . '-12-31',
                'items.*.product_id' => 'required',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.unit_price' => 'required|numeric|min:1',
                'items.*.net_amount' => 'required|numeric|min:0',
                'items.*.amount_incl_tax' => 'required|numeric|min:0',
            ], $customMessages);


            DB::beginTransaction();

            try {

                // Create the invoice
                $invoiceData = [
                    'reference_number' => $request->reference_number,
                    'farm_account' => $request->farm_account,
                    'transfer_date' => $request->invoice_date,
                    'vehicle_no'=> $request->vehicle_no,
                    'comments' => $request->comments,
                    'status' => 'posted',
                    'company_id' => 1,
                ];

                // Create the new invoice
                $invoiceData['created_by'] = Auth()->id();
                $invoice = MaterialTransfer::create($invoiceData);

                // Save invoice items
                foreach ($request->items as $item) {
                    MaterialTransferItem::create([
                        'material_transfer_id' => $invoice->id,
                        'item_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'gross_amount' => round($item['net_amount'] ?? 0, 2),
                        'discount' => $item['discount_rate'] ?? 0,
                        'net_amount' => round($item['amount_incl_tax'] ?? 0, 2),
                        'created_by' => Auth()->id(),
                    ]);
                }

                // Create a voucher
                $voucher = Voucher::create([
                    'voucher_type' => 'material-transfer',
                    'date' => $request->invoice_date,
                    'reference_number' => $request->reference_number,
                    'total_amount' => collect($request->items)->sum('amount_incl_tax'),
                    'description' => 'Material Transfer #' . $request->reference_number,
                    'status' => 1,
                    'company_id' => session('company_id'),
                    'segment_id' => 2,
                    'cost_center_id' => 2,
                    'created_by' => Auth()->id(),
                ]);

                $salesAccount = $request->farm_account;
                $totalSales = collect($request->items)->sum('amount_incl_tax');

                //Debit the Farm
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $salesAccount,
                    'amount' => $totalSales,
                    'type' => 'debit',
                    'narration' => 'Material Transfer:' . $request->reference_number,
                    'segment_id' => 1,
                    'cost_center_id' => 1,
                    'created_by' => Auth()->id(),
                ]);

                //Credit the Stock
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => 605,
                    'amount' => $totalSales,
                    'type' => 'credit',
                    'narration' => 'Material Transfer:' . $request->reference_number,
                    'segment_id' => 1,
                    'cost_center_id' => 1,
                    'created_by' => Auth()->id(),
                ]);


            // Commit the transaction if everything is successful
            DB::commit();

            session()->flash('message', 'Material Transfer (Farm) saved successfully!');
            return redirect('farms/stock');

            } catch (\Exception $e) {
             // Rollback the transaction in case of an error
                DB::rollBack();
                session()->flash('formerrors', 'An error occurred while saving the invoice. Please try again.');
                return redirect()->back()->withErrors($e->getMessage()); // Stay on the same page and show the error

            }

    }

    private function generateRefNumber()
    {

        $currentDate = now();

        // Determine the start of the fiscal year (1st July)
        $fiscalYearStart = $currentDate->month >= 7
            ? $currentDate->copy()->month(7)->startOfMonth()
            : $currentDate->copy()->subYear()->month(7)->startOfMonth();

        // Extract the fiscal year (start and end year)
        $fiscalYear = $fiscalYearStart->format('Y') . '-' . $fiscalYearStart->copy()->addYear()->format('Y');

        // Get the last two digits of the current year for use in the invoice number
        $currentYear = substr($currentDate->format('Y'), -2); // Last two digits of the year

        // Count the number of invoices in the current fiscal year
        $invoiceCount = MaterialTransfer::where('transfer_date', '>=', $fiscalYearStart)
            ->where('transfer_date', '<', $fiscalYearStart->copy()->addYear()) // Ensure it's within the fiscal year
            ->count();

        // Start numbering from 1 for the new fiscal year
        $nextInvoiceNumber = $invoiceCount + 1;

        do {
            // Format the next invoice number with leading zeros
            $formattedNumber = str_pad($nextInvoiceNumber, 3, '0', STR_PAD_LEFT);
            // Generate the invoice number in the format: [CompanyAbbreviation]-[FiscalYearLast2Digits]-[FormattedNumber]
            $invoiceNumber =  'MTF'  . $currentYear . '-' . $formattedNumber;

            // Check if the invoice number already exists in the database (including the fiscal year logic)
            $invoiceExists = MaterialTransfer::where('reference_number', $invoiceNumber)->exists();

            if ($invoiceExists) {
                // If the number exists, increment and retry
                $nextInvoiceNumber++;
            }
        } while ($invoiceExists);

        return $invoiceNumber;
    }



}
