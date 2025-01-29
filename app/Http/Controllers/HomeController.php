<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesInvoice;
use App\Models\CustomerDetail;
use App\Models\PurchaseBill;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\Company;

class HomeController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if (view()->exists($request->path())) {
            return view($request->path());
        }
        return abort(404);
    }

    public function root()
    {



        // Fetch the sales and purchases data using the existing method
        $salesAndPurchasesData = $this->getSalesAndPurchasesData();

        // Extract sales and purchases data from the response
        $salesData = $salesAndPurchasesData['sales'];


        return view('index')->with([
            'salesData' => $salesData
        ]);
    }

    public function getSalesAndPurchasesData()
    {
        $year = Carbon::now()->year;

        $salesData = SalesInvoice::selectRaw('MONTH(sales_invoices.invoice_date) as month, SUM(sales_invoice_items.quantity) as total_bags_sold')
        ->join('sales_invoice_items', 'sales_invoices.id', '=', 'sales_invoice_items.sales_invoice_id')
        ->whereYear('sales_invoices.invoice_date', $year)
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('total_bags_sold', 'month')->toArray();

        // Fill missing months with 0 (if no data for a month)
        $salesData = array_replace(array_fill(1, 12, 0), $salesData);

        // Return data as an array (not a JSON response)
        return [
            'sales' => array_values($salesData)
        ];
    }



    /*Language Translation*/
    public function lang($locale)
    {
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }

    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);

        $user = User::find($id);
        $user->name = $request->get('name');
        $user->email = $request->get('email');

        if ($request->file('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatarPath = public_path('/images/');
            $avatar->move($avatarPath, $avatarName);
            $user->avatar =  $avatarName;
        }

        $user->update();
        if ($user) {
            Session::flash('message', 'User Details Updated successfully!');
            Session::flash('alert-class', 'alert-success');
            // return response()->json([
            //     'isSuccess' => true,
            //     'Message' => "User Details Updated successfully!"
            // ], 200); // Status code here
            return redirect()->back();
        } else {
            Session::flash('message', 'Something went wrong!');
            Session::flash('alert-class', 'alert-danger');
            // return response()->json([
            //     'isSuccess' => true,
            //     'Message' => "Something went wrong!"
            // ], 200); // Status code here
            return redirect()->back();

        }
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!(Hash::check($request->get('current_password'), Auth::user()->password))) {
            return response()->json([
                'isSuccess' => false,
                'Message' => "Your Current password does not matches with the password you provided. Please try again."
            ], 200); // Status code
        } else {
            $user = User::find($id);
            $user->password = Hash::make($request->get('password'));
            $user->update();
            if ($user) {
                Session::flash('message', 'Password updated successfully!');
                Session::flash('alert-class', 'alert-success');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Password updated successfully!"
                ], 200); // Status code here
            } else {
                Session::flash('message', 'Something went wrong!');
                Session::flash('alert-class', 'alert-danger');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Something went wrong!"
                ], 200); // Status code here
            }
        }
    }


    public function printChallan(Request $request,  $orderId)
    {
        // Fetch sales order with joined customer (from ChartOfAccount) and related products
        $salesOrder = SalesOrder::join('chart_of_accounts', 'sales_orders.customer_id', '=', 'chart_of_accounts.id')
            ->where('sales_orders.id', $orderId)
            ->select('sales_orders.*', 'chart_of_accounts.name as customer_name')
            ->firstOrFail();

        // Fetch sales order items and related product data
        $orderItems = SalesOrderItem::join('sales_products', 'sales_order_items.product_id', '=', 'sales_products.id')
            ->where('sales_order_items.sales_order_id', $orderId)
            ->select('sales_order_items.*', 'sales_products.product_name')
            ->get();

        // Fetch the company details (id = 1)
        $company = Company::find(1);

        // Load the print view and pass the sales order and order items to the view
        return view('sales.orders.print', compact('salesOrder', 'orderItems','company'));
    }

    public function printinvoice(Request $request,  $orderId) {

        // Fetch the invoice details, including its items and customer
        $invoice = SalesInvoice::with(['items', 'customer', 'salesOrder'])->findOrFail($orderId);

        // Fetch the company details (id = 1)
        $company = Company::find(1);

        // Manually fetch the customer details from the customer_details table
        $customerDetails = CustomerDetail::where('account_id', $invoice->customer_id)->first();



        return view('sales.invoices.print', compact('invoice','company','customerDetails'));
    }




}
