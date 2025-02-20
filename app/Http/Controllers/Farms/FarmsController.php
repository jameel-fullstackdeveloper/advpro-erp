<?php

namespace App\Http\Controllers\Farms;

use App\Http\Controllers\Controller;

use App\Models\SalesInvoiceFarm;
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
use App\Models\Items;
use Carbon\Carbon;


class FarmsController extends Controller
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

        return view('farms.index');
    }

    public function farm_reports(Request $request) {

         // Get the current month
         $firstDayOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
         $lastDayOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

         return view('farms.reports.index', [
             'firstDate' => $firstDayOfMonth,
             'lastDate' => $lastDayOfMonth,
         ]);


    }

    public function farm_sale_report(Request $request)
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session

        // Get the date range from the request
        $firstDate = $request->firstdate;
        $lastDate = $request->lastdate;

        // Fetch invoices within the date range for the given company
        $invoices = SalesInvoiceFarm::with(['items'])
        ->where('company_id', $companyId)
        ->whereBetween('invoice_date', [$firstDate, $lastDate])
        ->orderBy('sales_invoice_farms.invoice_date')
        ->get();


        // Fetch all products related to the company
        $products = Items::where('company_id', $companyId)->get()->keyBy('id'); // Key by 'id' for faster lookup


        return view('farms.reports.farm_sale_report', [
            'firstDate' => $firstDate,
            'lastDate' => $lastDate,
            'invoices' => $invoices,
            'products' => $products,
        ]);
    }

    public function farm_expense_report(Request $request)
    {
        $companyId = session('company_id'); // Retrieve the company_id from the session

        // Get the date range from the request
        $firstDate = $request->firstdate;
        $lastDate = $request->lastdate;

        $vouchers = Voucher::with('voucherDetails.account')
        ->where('company_id', $companyId)
        ->whereIn('voucher_type', ['cash-payment', 'bank-payment'])
        ->where('farm_account', 1)
        ->whereHas('voucherDetails', fn($q) => $q->where('type', 'credit'))
        ->whereBetween('date', [$firstDate, $lastDate])
        ->orderBy('date')
        ->get();


        return view('farms.reports.farm_expense_report', [
            'firstDate' => $firstDate,
            'lastDate' => $lastDate,
            'vouchers' => $vouchers,
        ]);
    }

}
