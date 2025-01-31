<?php

namespace App\Livewire\Farms\Stock;

use Livewire\Component;
use App\Models\MaterialTransfer;
use App\Models\MaterialTransferItem;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\Company;
use App\Models\ChartOfAccount; // Import ChartOfAccount for customers
use Livewire\WithPagination;
use Carbon\Carbon;
use App\Models\Items;
use Illuminate\Support\Facades\DB;

class FarmStockManager extends Component
{
    use WithPagination;

    public $sales_order_id, $invoice_number, $transfer_date, $status, $company_id, $financial_year_id, $created_by, $updated_by;
    public $customer_id;
    public $items = [];
    public $isEditMode = false;
    public $invoiceId;
    public $isLinkedToOrder = false; // To distinguish between linked and unlinked invoices
    public $invoice_due_days = 0;

    public $itemsPerPage = 100;
    public $searchTerm;
    public $startDate, $endDate;

    public $discount_per_bag_rate=0;
    public $discount_per_bag_amount=0;
    public $totalDiscountPerBag = 0;
    public $totalDiscount = 0;
    public $totalSalesTax = 0;
    public $totalFurtherTax = 0;
    public $totalQuantity = 0;
    public $totalExclTax = 0;
    public $totalInclTax = 0;
    public $totalNetAmount = 0;
    public $vehicle_fare_adj = 0;

    //dev challan
    public $farm_name;
    public $farm_address;
    public $vehicle_no;
    public $vehicle_fare=0;
    public $farm_supervisor_mobile;

    public $selectedCustomer = null;
    public $brokers= [];
    public $broker_id, $broker_rate = 0, $broker_amount = 0;

    public $selectedItem, $selectedBroker, $selectedGroup;
    public $selectedFarm = null;
    public $selectedProduct = null;


    public $totalInvoicesu = 0;
    public $totalBagsu = 0;
    public $totalGrossAmountu = 0;
    public $totalDiscountu = 0;
    public $totalBonustu = 0;
    public $totalSalesTaxu = 0;
    public $totalFurtherSalesTaxu=0;
    public $totalWhtu=0;
    public $totalNetAmountu = 0;
    public $totalFreightu = 0;
    public $totalBrokerageu = 0;

    protected $rules = [
        'transfer_date' => 'required|date',
        'customer_id' => 'required', // Add validation for customer_id
        'vehicle_number' => 'required|string',
        'challan_number' => 'required|string',
        'items.*.product_id' => 'required',
        'items.*.quantity' => 'required|numeric|min:1',
        'items.*.unit_price' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
        'items.*.discount_rate' => 'numeric|min:0',
        'items.*.discount_amount' => 'numeric|min:0',
        'items.*.amount_excl_tax' => 'numeric|min:0',
        'items.*.sales_tax_rate' => 'numeric|min:0',
        'items.*.sales_tax_amount' => 'numeric|min:0',
        'items.*.further_tax_rate' => 'numeric|min:0',
        'items.*.further_tax_amount' => 'numeric|min:0',
        'items.*.amount_incl_tax' => 'numeric|min:0',
        'items.*.amount_incl_tax' => 'numeric|min:0',
    ];

    public function mount()
    {
        abort_if(!auth()->user()->can('sales invocies view'), 403);

        $this->startDate = Carbon::create(2024, 7, 1)->startOfDay()->format('Y-m-d'); // Start of the financial year
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');


        $this->transfer_date = Carbon::now()->format('Y-m-d');

        // Ensure at least one row is always present
        if (empty($this->items)) {
            $this->addItemRow();
        } else {
            // Calculate totals if items already exist
            $this->calculateTotals();
        }

    }

    public function render()
    {


            $invoices = MaterialTransfer::with(['items'])
                ->when($this->startDate, function ($query) {
                    $query->where('transfer_date', '>=', $this->startDate);
                })

                ->when($this->endDate, function ($query) {
                    $query->where('transfer_date', '<=', $this->endDate);
                })

                ->when($this->selectedFarm, function ($query) {
                    // Filter invoices by the farm account (associated with the farm_id)
                    $query->where('farm_account', $this->selectedFarm);
                })
                ->orderBy('id', 'desc') // Order by the most recent order_date
                ->paginate($this->itemsPerPage);

            $products = Items::where('item_type', 'purchase')
                ->orderBy('name')
                ->get();

            // Fetch customers filtered by company_id
            $farms = ChartOfAccount::where(function($query) {
                    $query->where('is_farm', 1);
                })
                ->orderBy('name')
                ->get();

        // Fetch customer groups filtered by company_id
        $filter_groups = DB::table('chart_of_accounts')
            ->join('chart_of_accounts_groups', 'chart_of_accounts.group_id', '=', 'chart_of_accounts_groups.id')
            ->where('chart_of_accounts_groups.is_customer_vendor', 'customer')
            ->where('chart_of_accounts_groups.is_customer_vendor', 'vendor')
            ->select('chart_of_accounts_groups.id', 'chart_of_accounts_groups.name')
            ->distinct()
            ->get();



        return view('livewire.farms.stock.index', compact('invoices', 'products', 'filter_groups' ,'farms'));
    }







    public function addItemRow()
    {
        $this->items[] = [
            'product_id' => null,
            'quantity' => 0,
            'unit_price' => 0,
            'discount_rate' => 0,
            'discount_amount' => 0,
            'discount_per_bag_rate' => 0,
            'discount_per_bag_amount' => 0,
            'amount_excl_tax' => 0,
            'sales_tax_rate' => 0,
            'sales_tax_amount' => 0,
            'further_sales_tax_rate' => 0,
            'further_sales_tax_amount' => 0,
            'amount_incl_tax' => 0,
            'net_amount' => 0
        ];

        // Recalculate totals after adding a new row
        $this->calculateTotals();
    }

    public function removeItemRow($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items); // Reset array keys after removing an item

        // Recalculate totals after removing a row
        $this->calculateTotals();
    }

    public function calculateAmounts($index)
    {
        // Access the item by reference
        $item = &$this->items[$index];

        // Convert string inputs to floats and round to two decimal places
        $quantity = round(floatval($item['quantity'] ?? 0), 2);
        $unit_price = round(floatval($item['unit_price'] ?? 0), 2); // Ensure unit price is rounded to two decimal places
        $discount_rate = round(floatval($item['discount_rate'] ?? 0), 2);
        $discount_rate_per_bag = round(floatval($item['discount_per_bag_rate'] ?? 0), 2);
        $sales_tax_rate = round(floatval($item['sales_tax_rate'] ?? 0), 2);
        $further_tax_rate = round(floatval($item['further_sales_tax_rate'] ?? 0), 2);

        // Calculate net amount and round
        $net_amount = round($unit_price * $quantity, 2);
        $item['net_amount'] = $net_amount;

        // Calculate discount amount using the rounded discount_rate and round the result
        $discount_amount = round(($unit_price * $discount_rate / 100) * $quantity, 2);
        $item['discount_amount'] = $discount_amount;

        // Calculate discount per bag amount and round
        $discount_per_bag_amount = round($discount_rate_per_bag * $quantity, 2);
        $item['discount_per_bag_amount'] = $discount_per_bag_amount;

        // Calculate total discount and round
        $total_discount = round($discount_amount + $discount_per_bag_amount, 2);

        // Calculate amount excluding tax and round
        $amount_excl_tax = round(($quantity * $unit_price) - $total_discount, 2);
        $item['amount_excl_tax'] = $amount_excl_tax;

        // Calculate sales tax amount for feed mill and round
        $sales_tax_amount = round($sales_tax_rate * $quantity, 2);
        $item['sales_tax_amount'] = $sales_tax_amount;

        // Calculate further tax amount and round
        $further_tax_amount = round(($amount_excl_tax * $further_tax_rate / 100), 2);
        $item['further_sales_tax_amount'] = $further_tax_amount;

        // Calculate total amount including tax and round
        $item['amount_incl_tax'] = round($amount_excl_tax + $sales_tax_amount, 2);

        // Calculate the totals after every change
        $this->calculateTotals();
    }






    public function calculateTotals()
    {
        $this->totalQuantity = 0;
        $this->totalNetAmount = 0;
        $this->totalDiscount = 0;
        $this->totalDiscountPerBag = 0;
        $this->totalSalesTax = 0;
        $this->totalFurtherTax = 0;
        $this->totalExclTax = 0;
        $this->totalInclTax = 0;

        foreach ($this->items as $item) {
            // Ensure all values are cast to numbers before performing operations
            $this->totalQuantity += round(floatval($item['quantity'] ?? 0), 2);
            $this->totalNetAmount += round(floatval($item['net_amount'] ?? 0), 2);
            $this->totalDiscount += round(floatval($item['discount_amount'] ?? 0), 2);
            $this->totalDiscountPerBag += round(floatval($item['discount_per_bag_amount'] ?? 0), 2);
            $this->totalSalesTax += round(floatval($item['sales_tax_amount'] ?? 0), 2);
            $this->totalFurtherTax += round(floatval($item['further_sales_tax_amount'] ?? 0), 2);
            $this->totalExclTax += round(floatval($item['amount_excl_tax'] ?? 0), 2);
            $this->totalInclTax += round(floatval($item['amount_incl_tax'] ?? 0), 2);
        }
    }


    public function calculateTotalsTop()
    {


        $companyId = session('company_id'); // Retrieve the company_id from the session

        $this->invoices = SalesInvoiceFarm::query()
        ->where('company_id', $companyId)
        ->when($this->startDate, fn($query) => $query->where('transfer_date', '>=', $this->startDate))
        ->when($this->endDate, fn($query) => $query->where('transfer_date', '<=', $this->endDate))
        ->when($this->selectedCustomer, fn($query) => $query->where('customer_id', $this->selectedCustomer))
        ->when($this->selectedFarm, fn($query) => $query->whereHas('items', fn($q) => $q->where('farm_account', $this->selectedFarm)))
        ->get();

        $this->totalInvoicesu = $this->invoices->count();
        $this->totalBagsu = $this->invoices->sum(fn($invoice) => $invoice->items->sum('quantity'));
        $this->totalGrossAmountu = $this->invoices->sum(fn($invoice) => $invoice->items->sum('amount_excl_tax'));
        $this->totalDiscountu = $this->invoices->sum(fn($invoice) => $invoice->items->sum('discount_amount'));
        $this->totalBonustu = $this->invoices->sum(fn($invoice) => $invoice->items->sum('discount_per_bag_amount'));
        $this->totalSalesTaxu = $this->invoices->sum(fn($invoice) => $invoice->items->sum('sales_tax_amount'));
        $this->totalFurtherSalesTaxu = $this->invoices->sum(fn($invoice) => $invoice->items->sum('further_sales_tax_amount'));
        $this->totalWhtu = $this->invoices->sum(fn($invoice) => $invoice->items->sum('advance_wht_amount'));
        $this->totalNetAmountu = $this->invoices->sum(fn($invoice) => $invoice->items->sum('amount_incl_tax'));
        $this->totalFreightu = $this->invoices->sum('freight');
        $this->totalBrokerageu = $this->invoices->sum('broker_amount');




    }


    public function updated($propertyName)
    {

        if ($propertyName === 'customer_id') {
            // Fetch payment_term when the customer_id changes
            $customerDetail = \App\Models\CustomerDetail::where('account_id', $this->customer_id)->first();
            if ($customerDetail) {
                $this->invoice_due_days = $customerDetail->payment_terms;
            }
        }

        // This will validate the specific property being updated (real-time validation)
        $this->validateOnly($propertyName, [
            'customer_id' => 'required',
            'transfer_date' => 'required|date',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'items.*.discount_rate' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'required|numeric|min:0',
            'items.*.discount_rate_bag' => 'required|numeric|min:0',
            'items.*.discount_per_bag_amount' => 'required|numeric|min:0',
            'items.*.amount_excl_tax' => 'required|numeric|min:0',
            'items.*.sales_tax_rate' => 'required|numeric|min:0',
            'items.*.sales_tax_amount' => 'required|numeric|min:0',
            'items.*.further_sales_tax_rate' => 'required|numeric|min:0',
            'items.*.further_sales_tax_amount' => 'required|numeric|min:0',
            'items.*.amount_incl_tax' => 'required|numeric|min:0',
        ], $this->customMessages());
    }
    protected function customMessages()
    {
        return [
            'items.*.product_id.required' => 'Product is required.',
            'items.*.quantity.required' => 'Quantity is required.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.unit_price.required' => 'Unit price is required.',
            'items.*.unit_price.min' => 'The field must be at least 1.',
            'items.*.discount_rate.required' => 'Discount rate is required.',
            'items.*.discount_rate.min' => 'Discount rate must be at least 0.',
            'items.*.amount_excl_tax.required' => 'Amount excluding tax is required.',
            'items.*.sales_tax_rate.required' => 'Sales tax rate is required.',
            'items.*.sales_tax_amount.required' => 'Sales tax amount is required.',
            'items.*.further_sales_tax_rate.required' => 'Further sales tax rate is required.',
            'items.*.further_sales_tax_amount.required' => 'Further sales tax amount is required.',
            'items.*.amount_incl_tax.required' => 'Amount including tax is required.',
        ];


    }

    private function generateVoucherNumber()
    {
        $currentDate = now();

        // Determine the start of the financial year based on your July start
        $financialYearStart = $currentDate->month >= 7
            ? $currentDate->copy()->month(7)->startOfMonth()
            : $currentDate->copy()->subYear()->month(7)->startOfMonth();

        // Format the current month and year
        $monthYear = $currentDate->format('m') . substr($currentDate->format('Y'), -2);

        // Count the number of vouchers in the current financial year
        $voucherCount = Voucher::where('date', '>=', $financialYearStart)->count();
        $nextVoucherNumber = $voucherCount + 1;

        do {
            // Format the next voucher number with leading zeros
            $formattedNumber = str_pad($nextVoucherNumber, 3, '0', STR_PAD_LEFT);
            $voucherNumber = 'JV' . $monthYear . '-' . $formattedNumber;

            // Check if the voucher number already exists
            $voucherExists = Voucher::where('reference_number', $voucherNumber)->exists();

            if ($voucherExists) {
                $nextVoucherNumber++;
            }
        } while ($voucherExists);

        return $voucherNumber;
    }

    public function confirmDeletion($id)
    {

        $this->dispatch('swal:confirm-deletion', voucherId: $id);

    }

    public function delete($id)
    {
        // Find the SalesInvoice by ID
        $invoice = MaterialTransfer::findOrFail($id);

        // Check if the invoice has associated voucher details
        $voucher = Voucher::where('reference_number', $invoice->reference_number )->first(); // Assuming the invoice is linked with a voucher by invoice_id

        if ($voucher) {
            // Delete all VoucherDetails associated with the voucher
            VoucherDetail::where('voucher_id', $voucher->id)->delete();

            // Delete the Voucher itself
            $voucher->delete();
        }

        // Delete all SalesInvoiceItems associated with the invoice
        MaterialTransferItem::where('material_transfer_id', $id)->delete();

        // Delete the invoice itself
        $invoice->delete();

        // Set a session flash message for success
        session()->flash('message', 'Material Transfer Record and associated records deleted successfully.');
    }

}
