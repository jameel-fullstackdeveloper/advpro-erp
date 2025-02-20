<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Sales\InvoiceController;
use App\Http\Controllers\Sales\InvoiceFarmController;
use App\Http\Controllers\Farms\FarmStockController;
use App\Http\Controllers\Sales\SalesreportController;
use App\Http\Controllers\Purchase\BillController;
use App\Http\Controllers\Purchase\BillFarmController;
use App\Http\Controllers\Configuration\RoleController;
use App\Http\Controllers\Configuration\PermissionController;
use App\Http\Controllers\Configuration\UserController;
use App\Http\Controllers\Accounting\VoucherController;
use App\Http\Controllers\Accounting\CashbookController;
use App\Http\Controllers\Accounting\BankbookController;
use App\Http\Controllers\WeighbridgeController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockgoodsController;
use App\Http\Controllers\Purchase\PurchasereportController;
use App\Http\Controllers\Accounting\AccountingreportController;
use App\Http\Controllers\Farms\FarmsController;
use App\Http\Middleware\RestrictIP;
use App\Http\Controllers\WhitelistAuthController;
use App\Http\Controllers\IpWhitelistController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Auth::routes();

Route::get('/register', function () {
    abort(404);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    RestrictIP::class,
])->group(function () {


    //Language Translation
    Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);

    Route::get('/dashboard', function () {
        // Redirect to the root ('/')
        return redirect('/');
    })->name('dashboard');

    Route::resource('permissions', PermissionController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);

    //companies
    Route::get('segments', function () {
        return view('companies/index');
    })->name('companies');

    Route::get('cost-centers', function () {
        return view('configuration/costcenters/index');
    })->name('costcenters');

    //farms cost ceneters
    Route::get('farmcenters', function () {
        return view('farmcenters/index');
    })->name('farmcenters');

    // *******ACCOUNTING MODULE ******* //

    //chart of accounts
    Route::get('accounting/chartofaccount', function () {
        return view('accounting/chartofaccount/index');
    })->name('chartofaccount');


    //Journal vouchers
    Route::get('accounting/vouchers', function () {
        return view('accounting/vouchers/index');
    })->name('vouchers');
    Route::get('/voucher/rvprint/{id}', [VoucherController::class, 'rvprint'])->name('voucher.rvprint');
    Route::get('/voucher/pvprint/{id}', [VoucherController::class, 'pvprint'])->name('voucher.pvprint');
    Route::get('/voucher/jvprint/{id}', [VoucherController::class, 'jvprint'])->name('voucher.jvprint');

    //Cash book
      Route::get('accounting/cashbook', function () {
        return view('accounting/cashbook/index');
    })->name('cashbook');
    Route::get('/cashbook/print/{id}', [CashbookController::class, 'rvprint'])->name('cashbook.print');

    //Bank book
    Route::get('accounting/bankbook', function () {
        return view('accounting/bankbook/index');
    })->name('bankbook');
    Route::get('/bankbook/print/{id}', [BankbookController::class, 'rvprint'])->name('bankbook.print');


    //ledgers
    Route::get('accounting/ledgers', function () {
        return view('accounting/ledgers/index');
    })->name('vouchers');

    //trail balance
    Route::get('accounting/trailbalance', function () {
        return view('accounting/reports/trailbalance');
    })->name('trailbalance');

    //----Sales----
    // Customers
    Route::get('sales/customers', function () {
        return view('sales/customers/index');
    })->name('customers');

    // Brokers
    Route::get('sales/brokers', function () {
        return view('sales/brokers/index');
    })->name('salesbrokers');

    // Sales Orders
    Route::get('sales/orders', function () {
        return view('sales/orders/index');
    })->name('orders');

    Route::post('print-challan/{orderId}', [HomeController::class, 'printChallan'])->name('print.challan');


    /*** INVOICE ROUTES */
    // Invoices
    Route::get('sales/invoices', function () {
        return view('sales/invoices/index');
    })->name('invoices');
    Route::post('invoices/print/{invoiceId}', [HomeController::class, 'printinvoice'])->name('invoices.print');

    //create and edit invoices
    Route::get('sales/invoices/create', [InvoiceController::class, 'index'])->name('invoices.create');
    Route::post('sales/invoices', [InvoiceController::class, 'store'])->name('invoices.store');

    Route::get('sales/invoices/{id}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('sales/invoices/{id}', [InvoiceController::class, 'update'])->name('invoices.update');

    /********    End of Sale Invoices     ********/

    // Invoices Farms


    // Customers
    Route::get('farms', function () {
        return view('sales/farms/index');
    })->name('farms');


      Route::get('farms/invoicesfarms', function () {
        return view('sales/invoicesfarms/index');
    })->name('invoicesfarms');
    Route::post('invoicesfarms/print/{invoiceId}', [HomeController::class, 'printinvoicefarm'])->name('invoicesfarms.print');

    //create and edit invoices
    Route::get('farms/invoices/farms/create', [InvoiceFarmController::class, 'index'])->name('invoicesfarms.create');
    Route::post('farms/invoices/farms', [InvoiceFarmController::class, 'store'])->name('invoicesfarms.store');

    Route::get('farms/invoices/farms/{id}/edit', [InvoiceFarmController::class, 'edit'])->name('invoicesfarms.edit');
    Route::put('farms/invoices/farms/{id}', [InvoiceFarmController::class, 'update'])->name('invoicesfarms.update');


     //create and stock
     Route::get('farms/stock/create', [FarmStockController::class, 'index'])->name('farmsstock.create');
     Route::post('farms/stock/farms', [FarmStockController::class, 'store'])->name('farmsstock.store');

   /********   End of Sale Invoices (Farms)   ********/


    // ajax call for sales invoice
    Route::get('/get-customer-details/{customerId}', [InvoiceController::class, 'getCustomerDetails']);
    Route::get('/get-product-details/{productId}', [InvoiceController::class, 'getProductDetails']);
    Route::get('/get-broker-details/{brokerId}', [InvoiceController::class, 'getBrokerDetails']);
    Route::get('/get-segment-details/{segmentId}', [InvoiceController::class, 'getSegmentDetails']);


    // Sales Return
    Route::get('sales/returns', function () {
        return view('sales/returns/index');
    })->name('salesreturns');

    Route::post('/returns/print/{returnId}', [HomeController::class, 'saleinvoiceprint'])->name('returns.saleinvoiceprint');

    //----Purchase----

    // Vendros
    Route::get('purchase/vendors', function () {
        return view('purchase/vendors/index');
    })->name('vendors');

    // Brokers
    Route::get('purchase/brokers', function () {
        return view('purchase/brokers/index');
    })->name('purchasebrokers');

    // purchase order
    Route::get('purchase/orders', function () {
        return view('purchase/orders/index');
    })->name('purchaseorders');

    // purchase bill
    Route::get('purchase/bills', function () {
        return view('purchase/bills/index');
    })->name('purchasebills');


    /*** Purchase Bills Routes **/

     //create and edit invoices
     Route::get('purchase/bills/create', [BillController::class, 'create'])->name('bills.create');
     Route::post('purchase/bills', [BillController::class, 'store'])->name('bills.store');

     Route::get('purchase/bills/{id}/edit', [BillController::class, 'edit'])->name('bills.edit');
     Route::put('purchase/bills/{id}', [BillController::class, 'update'])->name('bills.update');

    // ajax call for purchase bills
    Route::get('/get-vendor-orders/{vendorId}', [BillController::class, 'getVendorOrders']);
    Route::get('/get-order-items/{orderId}', [BillController::class, 'getOrderItems']);


    // purchase return
    Route::get('purchase/returns', function () {
        return view('purchase/returns/index');
    })->name('purchasereturns');



    /**** Purchase Bill Farms */

     // purchase bill
     Route::get('farms/bills/farms', function () {
        return view('purchase/billsfarms/index');
    })->name('purchasebillsfarms');

    //create and edit invoices
    Route::get('farms/bills/farms/create', [BillFarmController::class, 'create'])->name('billsfarms.create');
    Route::post('farms/bills/farms', [BillFarmController::class, 'store'])->name('billsfarms.store');

    Route::get('farms/bills/farms/{id}/edit', [BillFarmController::class, 'edit'])->name('billsfarms.edit');
    Route::put('farms/bills/farms/{id}', [BillFarmController::class, 'update'])->name('billsfarms.update');



    // Farms Expenses
    Route::get('farms/expenses', function () {
        return view('farms/expenses/index');
    })->name('expensesfarms');

    // Farm Stock
    Route::get('farms/stock', function () {
        return view('farms/stock/index');
    })->name('stockfarms');


    //---Inventory----
    //Sales Products
    Route::get('inventory/salesproducts', function () {
        return view('inventory/salesproducts/index');
    })->name('salesproducts');

    //Purchase Items
    Route::get('inventory/purchaseitems', function () {
        return view('inventory/purchaseitems/index');
    })->name('purchaseitems');

    Route::resource('inventory/productions', ProductionController::class);
    Route::post('inventory/productions/{production}/print', [ProductionController::class, 'print'])->name('consumption.print');

    // Material
    Route::resource('inventory/stock', StockController::class);
    Route::post('inventory/stock/filter', [StockController::class, 'filter'])->name('stock.filter');
    Route::post('inventory/stock/{production}/print', [StockController::class, 'print'])->name('stockledger.print');

    //Finished Goods
    Route::resource('inventory/stockgoods', StockgoodsController::class);
    Route::post('inventory/stockgoods/filter', [StockgoodsController::class, 'filter'])->name('stockgoods.filter');
    Route::post('inventory/stockgoods/{production}/print', [StockgoodsController::class, 'print'])->name('stockgoodsledger.print');


    //stock reports
    Route::get('inventory/stockreports', [StockController::class, 'stockreport']);
    Route::post('inventory/stockreports/stock-report-general', [StockController::class, 'stockreportgeneral'])->name('stockreportgeneral');
    Route::post('inventory/stockreports/stock-report-fg-general', [StockController::class, 'stockreportfggeneral'])->name('stockreportfggeneral');

    Route::get('inventory/stockadjustments', [StockController::class, 'stockadjustments']);

    //--- Weighbridge---

    //inwards
    Route::get('weighbridge/inwards', function () {
        return view('weighbridge/inwards/index');
    })->name('inwards');

    //outwards
    Route::get('weighbridge/outwards', function () {
        return view('weighbridge/outwards/index');
    })->name('outwards');



    // ************** Farms ***************** //
    Route::get('farms/farms', [FarmsController::class, 'index']);

    // ************** Reports *****************//
    // Sales Reports
    Route::get('sales/sales-reports', [SalesreportController::class, 'index']);
    Route::post('sales/sales-reports/debtor-report', [SalesreportController::class, 'debtorreport'])->name('debtorreport');
    Route::post('sales/sales-reports/debtorgroup-report', [SalesreportController::class, 'debtorgroupreport'])->name('debtorgroupreport');


    //purchases reports
    Route::get('purchases/purchase-reports', [PurchasereportController::class, 'index']);
    Route::post('sales/purchase-reports/creditor-report', [PurchasereportController::class, 'creditorreport'])->name('creditorreport');
    Route::post('sales/purchase-reports/creditorgroup-report', [PurchasereportController::class, 'creditorgroupreport'])->name('creditorgroupreport');

    //accounts reports
    Route::get('accounting/accounting-reports', [AccountingreportController::class, 'index']);
    Route::post('accounting/cash-bank-report', [AccountingreportController::class, 'cashbank_report'])->name('cashbank_report');

    //test
    Route::get('/opening-balance', [VoucherController::class, 'showOpeningBalanceForm'])->name('opening_balance.form');
    Route::post('/opening-balance', [VoucherController::class, 'storeOpeningBalance'])->name('opening_balance.store');
    Route::get('/ledger/{accountId}', [VoucherController::class, 'showLedger'])->name('ledger.show');

    Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])->name('root');
    Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');

});

// Show the login form for the whitelist management
Route::get('admin/whitelist/login', [WhitelistAuthController::class, 'showLoginForm'])->name('whitelist.login');

// Handle the login request
Route::post('admin/whitelist/login', [WhitelistAuthController::class, 'login']);

// Logout the user from the whitelist section
Route::post('admin/whitelist/logout', [WhitelistAuthController::class, 'logout'])->name('whitelist.logout');

// Protected routes for whitelist management
Route::prefix('admin')->middleware('auth:whitelist')->group(function () {
    Route::get('whitelist', [IpWhitelistController::class, 'index'])->name('admin.whitelist');
    Route::post('whitelist', [IpWhitelistController::class, 'store'])->name('admin.whitelist.store');
    Route::delete('/admin/whitelist/{id}', [IpWhitelistController::class, 'destroy'])->name('admin.whitelist.destroy');

});


Route::fallback(function () {
    return abort(404);  // This will show a 404 error for non-existent pages
});
