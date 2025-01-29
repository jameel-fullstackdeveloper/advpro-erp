@extends('layouts.master')
@section('title')
    Edit Purchase (Farm)
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" type="text/css" />

    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .form-control {
            border-radius: 0 !important;
            padding: 8px !important;
            font-size: 12px !important;
        }
        .form-select-sm {
            border-radius: 0 !important;
            padding: 0px !important;
            font-size: 10px !important;
            border-color:#ced4da !important;
        }
        #invoice-items-table .form-control {
            border-radius: 0 !important;
            padding: 6px !important;
            font-size: 12px !important;
        }
        #invoice-items-table th {
            font-size: 12px !important;
        }
        #invoice-items-table td {
            color:#000;
        }

       .section-heading {
            font-size: 14px;
            font-weight: 500;
            color: #FF9F43;
            position: relative;
            margin-bottom: 10px;
            padding-bottom: 5px;
            letter-spacing: 1px;
        }
        .section-heading::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #28a745;
            border-radius: 5px;
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        .invoice-section,
        .delivery-challan-section,
        .brokerage-section {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 5px;
        }
        .invoice-section .row,
        .delivery-challan-section .row,
        .brokerage-section .row {
            margin-bottom: 5px;
        }
        .btn-submit {
            background-color: #28a745;
            color: white;
            font-size: 14px;
            font-weight: 600;
            padding: 8px 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-submit:hover {
            background-color: #218838;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .col-md-2,
        .col-md-7,
        .col-2,
        .col-1,
        .col-4 {
            margin-bottom: 10px;
        }
        .total_label {
            font-weight:bold;
        }
    </style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h4 class="mb-sm-0 card-title">Edit Purchase (Farm)</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase</a></li>
                    <li class="breadcrumb-item active">Purchase (Farm) > Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Whoops! Something went wrong.</strong><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('billsfarms.update', $purchaseBill->id) }}">

        @csrf
        @method('PUT')
        <div class="invoice-section">
            <h5 class="section-heading tabheading mb-">Purchase Bill Details</h5>
            <div class="row mb-1">


            <div class="col-4">
                 <label for="cost_center" class="form-label">Purchase to Debit</label>
                    <select name="farm_account" class="form-select" id="farm_account" required>
                        <option value="">---Select Farm Account ---</option>
                            @foreach($farmaccounts as $farmaccount)
                               <option value="{{ $farmaccount->id }}" {{ old('farm_account', $purchaseBill->farm_account) == $farmaccount->id ? 'selected' : '' }}>{{ $farmaccount->name }}</option>
                            @endforeach
                        </select>
                    @error('farm_account') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-8">
                    <label class="form-label" for="vendor_id">Vendor</label>
                    <select name="vendor_id" class="form-select" required>
                        <option value="">-- Select Vendor --</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ old('vendor_id', $purchaseBill->vendor_id) == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                    @error('vendor_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>


                <div class="col-md-2">
                    <label class="form-label" for="bill_date">Bill Date</label>
                    <input type="date" name="bill_date" class="form-control" value="{{ old('bill_date', \Carbon\Carbon::parse($purchaseBill->bill_date)->format('Y-m-d')) }}" required>

                    @error('bill_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label">Bill Number</label>
                    <input type="text" name="bill_number" class="form-control shadow-sm" placeholder="Bill Number"
                        value="{{ old('bill_number', $purchaseBill->bill_number) }}" readonly required />
                    @error('bill_number') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label" for="vehicle_no">Vehicle Number </label>
                    <input type="text" name="vehicle_no" class="form-control" value="{{ old('vehicle_no', $purchaseBill->vehicle_no) }}" required>
                    @error('vehicle_no') <span class="text-danger">{{ $message }}</span> @enderror
                </div>




                <div class="col-md-2">
                    <label class="form-label" for="freight">Freight In</label>
                    <input type="number" name="freight" class="form-control" id="freight" value="{{ old('freight', $purchaseBill->freight) }}" >
                    @error('freight') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label" for="status">Delivery Mode</label>
                    <select name="delivery_mode" class="form-select" required>
                        <option value="ex-mill" {{ old('delivery_mode', $purchaseBill->delivery_mode) == 'ex-mill' ? 'selected' : '' }}>Ex-mill</option>
                        <option value="deliverd" {{ old('delivery_mode', $purchaseBill->delivery_mode) == 'deliverd' ? 'selected' : '' }}>Delivered</option>
                    </select>
                    @error('delivery_mode') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label" for="bill_due_days">Due Days</label>
                    <input type="number" name="bill_due_days" class="form-control" id="bill_due_days" value="{{ old('bill_due_days', $purchaseBill->bill_due_days) }}">
                    @error('bill_due_days') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="row mt-3 mb-0">
                <div class="col-12">

                <table class="table table-bordered table-striped mt-2" id="invoice-items-table">
                        <thead class="table-info" style="vertical-align: middle;">
                            <tr>
                                <th class="text-center text-info">Product</th>
                                <th class="text-center text-info">Quantity <br><span class="text-dark" style="font-size:10px">Kgs / Unit</span></th>
                                <th class="text-center text-info">Deduction <br><span class="text-dark"style="font-size:10px">Kgs / Unit</span> </th>
                                <th class="text-center text-info">Net Quantity <br><span class="text-dark"style="font-size:10px">Kgs / Unit</span> </th>
                                <th class="text-center text-info">Rate <br><span class="text-dark"style="font-size:10px">Per kgs / Per unit</span></th>
                                <th class="text-center text-info">Amount <br><span class="text-dark"style="font-size:10px">Excluding Taxes</span> </th>
                                <th class="text-center text-info">Sales Tax <br><span class="text-dark"style="font-size:10px">Rate & Amount</span></th>
                                <th class="text-center text-info">Amount <br><span class="text-dark"style="font-size:10px">Including Taxes</span></th>
                                <th class="text-center text-info">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseBill->items as $item)
                                <tr>
                                    <td style="width:200px;">
                                        <select name="items[{{ $loop->index }}][product_id]" class="form-control form-select">
                                            <option value="{{ $item->product_id }}">{{ $item->product->name }}</option>
                                        </select>
                                    </td>
                                    <td><input type="number" name="items[{{ $loop->index }}][quantity]" class="form-control quantity" value="{{ old('items.' . $loop->index . '.quantity', $item->quantity) }}" step="0.001"></td>
                                    <td><input type="number" name="items[{{ $loop->index }}][deduction]" class="form-control deduction" value="{{ old('items.' . $loop->index . '.deduction', $item->deduction) }}" step="0.001"></td>
                                    <td><input type="number" name="items[{{ $loop->index }}][net_quantity]" class="form-control net_quantity" value="{{ old('items.' . $loop->index . '.net_quantity', $item->net_quantity) }}" readonly></td>
                                    <td><input type="number" name="items[{{ $loop->index }}][price]" class="form-control price" value="{{ old('items.' . $loop->index . '.price', $item->price) }}" step="0.001"></td>
                                    <td><input type="number" name="items[{{ $loop->index }}][gross_amount]" class="form-control gross_amount" value="{{ old('items.' . $loop->index . '.gross_amount', $item->gross_amount) }}" readonly></td>

                                    <td>
                                        <div class="d-flex">
                                            <input type="number" name="items[{{ $loop->index }}][sales_tax_rate]" class="form-control table-input-small me-1 sales_tax_rate" placeholder="" min="0" style="width:50px;" value="{{ old('items.' . $loop->index . '.sales_tax_rate', $item->sales_tax_rate) }}">
                                            <input type="number" name="items[{{ $loop->index }}][sales_tax_amount]" class="form-control sales_tax" step="0.001" value="{{ old('items.' . $loop->index . '.sales_tax_amount', $item->sales_tax_amount) }}">
                                        </div>
                                    </td>


                                    <td><input type="number" name="items[{{ $loop->index }}][net_amount]" class="form-control net_amount" value="{{ old('items.' . $loop->index . '.net_amount', $item->net_amount) }}" readonly></td>
                                    <td class="text-center">
                                        <a href="#" class="link-danger remove-row d-none">
                                            <i class="ri-delete-bin-line" style="font-size:18px;"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td class="text-end"><strong>Total:</strong></td>
                                <td><span class="total_label" id="total-quantity">0</span></td>
                                <td><span class="total_label" id="total-deduction">0</span></td>
                                <td><span class="total_label" id="total-net-quantity">0</span></td>
                                <td></td>
                                <td><span class="total_label" id="total-gross">0</span></td>
                                <td><span class="total_label" id="total-salestax">0</span></td>
                                <td><span class="total_label" id="total-netamount">0</span></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>



                </div>
            </div>

            <div class="invoice-section">
                <div class="row mb-2">
                    <h5 class="section-heading tabheading mb-1">Brokery Details</h5>
                    <div class="col-md-5">
                        <label class="form-label" for="broker">Broker</label>
                        <select name="broker_id" class="form-select">
                            <option value=""  {{ old('broker_id', $purchaseBill->broker_id) == 'NULL' ? 'selected' : '' }}>-- Select Broker --</option>

                            <!-- If broker_id is null, select 'Select Broker' option -->
                            <option value="0" {{ old('broker_id', $purchaseBill->broker_id) == '0' ? 'selected' : '' }}>Self</option>

                            <!-- Loop through brokers and select the matching broker -->
                            @foreach($brokers as $broker)
                                <option value="{{ $broker->id }}" {{ old('broker_id', $purchaseBill->broker_id) == $broker->id ? 'selected' : '' }}>
                                    {{ $broker->name }}
                                </option>
                            @endforeach
                        </select>


                        @error('broker_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-1">
                        <label class="form-label" for="broker_rate">Rate</label>
                        <input type="number" name="broker_rate" class="form-control" value="{{ old('broker_rate', $purchaseBill->broker_rate) }}" step="0.001">
                        @error('broker_rate') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-2">
                        <label for="calculation_method">Method</label>
                        <select name="calculation_method" class="form-control form-select shadow-sm" id="calculation_method">
                            <option value="">-- Select Method --</option>
                            <option value="percentage"  {{ old('calculation_method', $purchaseBill->calculation_method) == 'percentage' ? 'selected' : '' }}>Percentage on Amount</option>
                            <option value="quantity"  {{ old('calculation_method', $purchaseBill->calculation_method) == 'quantity' ? 'selected' : '' }}>Percentage on Quantity</option>

                            <option value="mann"  {{ old('calculation_method', $purchaseBill->calculation_method) == 'mann' ? 'selected' : '' }}>Percentage on Mann</option>

                        </select>
                        @error('calculation_method') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label" for="broker_amount">Amount</label>
                        <input type="number" step="0.001" name="broker_amount" class="form-control" value="{{ old('broker_amount', $purchaseBill->broker_amount) }}">
                        @error('broker_amount') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-2">
                            <label class="form-label" for="broker_wht_rate">WHT <span class="text-warning" style="font-size:10px;">(With Holding Tax)</span></label>
                            <div class="d-flex">
                                <input type="number" name="broker_wht_rate" class="form-control table-input-small me-1" placeholder="" min="0" style="width:50px;"
                                value="{{ old('broker_wht_rate', $purchaseBill->broker_wht_rate) }}" step="0.001">
                                <input type="number" name="broker_wht_amount" class="form-control" step="0.001" value="{{ old('broker_wht_amount', $purchaseBill->broker_wht_amount) }}" required>
                            </div>
                    </div>

                    <div class="col-md-2 d-none">
                        <label class="form-label" for="broker_amount_with_wht">Amount <span class="text-warning" style="font-size:10px;">(Inculding WHT)</span></label>
                        <input type="number" step="0.001" name="broker_amount_with_wht" class="form-control" id="broker_amount_with_wht" value="{{ old('broker_amount_with_wht', 0) }}">
                        @error('broker_amount_with_wht') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-6 mt-2">
                        <label class="form-label" for="comments">Comments</label>
                        <input type="text" name="comments" class="form-control"  value="{{ old('comments', $purchaseBill->comments) }}">
                        @error('comments') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 text-end mb-3 mt-3">
                    <button type="submit" class="btn btn-success btn-label">
                        <i class="ri-add-circle-line label-icon align-middle fs-16"></i> Update Purchase Bill
                    </button>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/sweetalerts.init.js') }}"></script>
<script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
<script src="https://cdn.lordicon.com/libs/mssddfmo/lord-icon-2.1.0.js"></script>
<script src="{{ URL::asset('build/js/pages/modal.init.js') }}"></script>

<!--jquery cdn-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<!--select2 cdn-->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="{{ URL::asset('build/js/pages/select2.init.js') }}"></script>

<script src="{{ URL::asset('build/js/app.js') }}"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {

updateTotals();

// Event listener for dynamic rows
document.querySelector('#invoice-items-table').addEventListener('input', function (e) {
    if (e.target.classList.contains('quantity') || e.target.classList.contains('deduction') || e.target.classList.contains('price') || e.target.classList.contains('sales_tax_rate') || e.target.classList.contains('sales_tax')) {
        calculateAmounts(e.target.closest('tr'));
        updateTotals();
    }
});

// Listen for changes to the calculation method
document.querySelector('select[name="calculation_method"]').addEventListener('change', function () {
    calculateBrokerageAmount();
});

// Listen for changes to the broker rate and broker amount fields to recalculate
document.querySelector('input[name="broker_rate"]').addEventListener('input', function () {
    calculateBrokerageAmount();
});

document.querySelector('input[name="broker_amount"]').addEventListener('input', function () {
    calculateBrokerageAmount();
});

// Listen for changes to the WHT amount and update the final amount
document.querySelector('input[name="broker_wht_rate"]').addEventListener('input', function () {
    calculateWHTAmount();
});

function calculateBrokerageAmount() {
    const calculationMethod = document.querySelector('select[name="calculation_method"]').value;
    const brokerRate = parseFloat(document.querySelector('input[name="broker_rate"]').value) || 0;
    const brokerAmountField = document.querySelector('input[name="broker_amount"]');
    const brokerAmountFieldWithWHT = document.querySelector('input[name="broker_amount_with_wht"]');
    const totalAmount = parseFloat(document.getElementById('total-netamount').textContent) || 0;
    const totalQuantity = parseFloat(document.getElementById('total-net-quantity').textContent) || 0;

    let brokerageAmount = 0;

    if (calculationMethod === 'percentage') {
        // Percentage on total amount
        brokerageAmount = (brokerRate * totalAmount) / 100;
    } else if (calculationMethod === 'quantity') {
        // Percentage on total quantity
        brokerageAmount = (brokerRate * totalQuantity) / 100;
    } else if (calculationMethod === 'mann') {
        // Percentage on total quantity
        brokerageAmount = (brokerRate * (totalQuantity /40) );
    }

    // Set the brokerage amount value
    brokerAmountField.value = brokerageAmount.toFixed(2);
    brokerAmountFieldWithWHT.value = brokerageAmount.toFixed(2);
    calculateWHTAmount(); // Recalculate WHT based on the updated broker amount
}

function calculateWHTAmount() {
    const brokerAmount = parseFloat(document.querySelector('input[name="broker_amount"]').value) || 0;
    const whtRate = parseFloat(document.querySelector('input[name="broker_wht_rate"]').value) || 0;
    const whtRateAmount = parseFloat(document.querySelector('input[name="broker_wht_amount"]').value) || 0;

    // Check if WHT rate is greater than 0
    if (whtRate > 0) {
        // Calculate the WHT amount
        const whtAmount = (whtRate * brokerAmount) / 100;

        // Calculate the total broker amount including WHT
        const totalBrokerAmountWithWHT = brokerAmount + whtAmount;

        // Update the input field for total broker amount with WHT
        document.querySelector('input[name="broker_wht_amount"]').value = whtAmount.toFixed(2);
        document.querySelector('input[name="broker_amount_with_wht"]').value = totalBrokerAmountWithWHT.toFixed(2);
    } else {
        // If WHT rate is 0 or null, set the broker amount with WHT to just the broker amount
        document.querySelector('input[name="broker_wht_amount"]').value =0;
        document.querySelector('input[name="broker_amount_with_wht"]').value = brokerAmount.toFixed(2);
    }
}

// Function to update totals
function updateTotals() {
    let totalQuantity = 0;
    let totalDeduction = 0;
    let totalNetQuantity = 0;
    let totalGross = 0;
    let totalSalesTax = 0;
    let totalNetAmount = 0;

    // Loop through each row in the table and sum up the columns
    const rows = document.querySelectorAll('#invoice-items-table tbody tr');
    rows.forEach(row => {
        totalQuantity += parseFloat(row.querySelector('.quantity').value) || 0;
        totalDeduction += parseFloat(row.querySelector('.deduction').value) || 0;
        totalNetQuantity += parseFloat(row.querySelector('.net_quantity').value) || 0;
        totalGross += parseFloat(row.querySelector('.gross_amount').value) || 0;
        totalSalesTax += parseFloat(row.querySelector('.sales_tax').value) || 0;
        totalNetAmount += parseFloat(row.querySelector('.net_amount').value) || 0;
    });

    // Update the footer with the totals
    document.getElementById('total-quantity').textContent = totalQuantity.toFixed(2);
    document.getElementById('total-deduction').textContent = totalDeduction.toFixed(2);
    document.getElementById('total-net-quantity').textContent = totalNetQuantity.toFixed(2);
    document.getElementById('total-gross').textContent = totalGross.toFixed(2);
    document.getElementById('total-salestax').textContent = totalSalesTax.toFixed(2);
    document.getElementById('total-netamount').textContent = totalNetAmount.toFixed(2);
}

// Function to calculate amounts for each row
function calculateAmounts(row) {
    const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
    const deduction = parseFloat(row.querySelector('.deduction').value) || 0;
    const price = parseFloat(row.querySelector('.price').value) || 0;
    const salesTaxRate = parseFloat(row.querySelector('.sales_tax_rate').value) || 0;

    const netQuantity = quantity - deduction;
    const grossAmount = netQuantity * price;
    const salesTaxAmount = (grossAmount * salesTaxRate) / 100;
    const netAmount = grossAmount + salesTaxAmount;

    row.querySelector('.net_quantity').value = netQuantity.toFixed(2);
    row.querySelector('.gross_amount').value = grossAmount.toFixed(2);
    row.querySelector('.sales_tax').value = salesTaxAmount.toFixed(2);
    row.querySelector('.net_amount').value = netAmount.toFixed(2);
}

// Listen for the change event on the vendor select dropdown
document.querySelector('select[name="vendor_id"]').addEventListener('change', function () {
    let vendorId = this.value;

    // If a valid vendor is selected, make the AJAX call
    if (vendorId) {
        fetchOrderDetails(vendorId);
    } else {
        // Clear the purchase order dropdown if no vendor is selected
        clearOrderDropdown();
    }
});

const tableBody = document.querySelector('#invoice-items-table tbody');

document.querySelector('select[name="order_id"]').addEventListener('change', function () {
    const orderId = this.value;
    if (orderId) {
        fetchOrderItems(orderId);
    }
});

// Fetch purchase order items
function fetchOrderItems(orderId) {
    fetch(`/get-order-items/${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector('select[name="delivery_mode"]').value = data.order.delivery_mode;
                document.querySelector('input[name="bill_due_days"]').value = data.order.bill_due_days;

                // Clear any previous rows before adding new ones
                tableBody.innerHTML = '';
                data.items.forEach(item => {
                    addRow(item);
                });
                // After rows are added, update totals
                updateTotals();
            } else {
                console.error(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

let index = 0;

function addRow(item) {
    let index = tableBody.rows.length;
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td style="width:200px;">
            <select name="items[${index}][product_id]" class="form-control form-select">
                <option value="${item.product_id}">${item.product_name}</option>
            </select>
        </td>
        <td><input type="number" name="items[${index}][quantity]" class="form-control quantity" value="" step="0.001" required></td>
        <td><input type="number" name="items[${index}][deduction]" class="form-control deduction" value="" step="0.001" required></td>
        <td><input type="number" name="items[${index}][net_quantity]" class="form-control net_quantity" value="" readonly required></td>
        <td><input type="number" name="items[${index}][price]" class="form-control price" value="${item.price}" step="0.001" required></td>
        <td><input type="number" name="items[${index}][gross_amount]" class="form-control gross_amount" value="" readonly required></td>
        <td>
            <div class="d-flex">
                <input type="number" name="items[${index}][sales_tax_rate]" class="form-control sales_tax_rate" value="" step="0.001"  style="width:50px;" required>
                <input type="number" name="items[${index}][sales_tax_amount]" class="form-control sales_tax" value="" step="0.001" required>
            </div>
        </td>
        <td><input type="number" name="items[${index}][net_amount]" class="form-control net_amount" value="" readonly required></td>
        <td class="text-center"><a href="#" class="link-danger remove-row d-none"><i class="ri-delete-bin-line" style="font-size:18px;"></i></a></td>
    `;
    tableBody.appendChild(newRow);
    index++;

    newRow.querySelector('.quantity').addEventListener('input', calculateAmounts);
    newRow.querySelector('.deduction').addEventListener('input', calculateAmounts);
    newRow.querySelector('.price').addEventListener('input', calculateAmounts);
    newRow.querySelector('.sales_tax_rate').addEventListener('input', calculateAmounts);
    newRow.querySelector('.sales_tax').addEventListener('input', calculateAmounts);
    newRow.querySelector('.remove-row').addEventListener('click', function () {
        newRow.remove();
        updateTotals(); // Recalculate totals after removing a row
    });
}

// Fetch broker details
document.querySelector('select[name="broker_id"]').addEventListener('change', function () {
    let brokerId = this.value;
    const brokerRateField = document.querySelector('input[name="broker_rate"]');
    const calculationMethodField = document.querySelector('select[name="calculation_method"]');
    const brokerWHTRateField = document.querySelector('input[name="broker_wht_rate"]');
    const brokerWHTAmountField = document.querySelector('input[name="broker_wht_amount"]');
    const brokerAmountWithWHTField = document.querySelector('input[name="broker_amount_with_wht"]');
    const brokerAmountField = document.querySelector('input[name="broker_amount"]');

    // If a valid broker is selected, make the AJAX call
    if (brokerId) {
        brokerRateField.setAttribute('required', 'required');
        calculationMethodField.setAttribute('required', 'required');
        brokerWHTRateField.setAttribute('required', 'required');
        brokerWHTAmountField.setAttribute('required', 'required');
        brokerAmountWithWHTField.setAttribute('required', 'required');
        brokerAmountField.setAttribute('required', 'required');
        fetchBrokerDetails(brokerId);
    } else {
        brokerRateField.removeAttribute('required');
        calculationMethodField.removeAttribute('required');
        brokerWHTRateField.removeAttribute('required');
        brokerWHTAmountField.removeAttribute('required');
        brokerAmountWithWHTField.removeAttribute('required');
        brokerAmountField.removeAttribute('required');

        // Set values to 0 if no broker is selected
        brokerRateField.value = 0;
        brokerWHTRateField.value = 0;
        brokerWHTAmountField.value = 0;
        brokerAmountWithWHTField.value = 0;
        brokerAmountField.value = 0;
    }
});

// Function to make AJAX request to fetch broker details
function fetchBrokerDetails(brokerId) {
    fetch(`/get-broker-details/${brokerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector('input[name="broker_rate"]').value = data.broker_rate;
            }
        })
        .catch(error => console.error('Error:', error));
}

// Function to make AJAX request and populate fields for order details
function fetchOrderDetails(vendorId) {
    fetch(`/get-vendor-orders/${vendorId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateOrderDropdown(data.orders);
            } else {
                clearOrderDropdown();
                console.error(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

// Function to populate the Purchase Order dropdown
function populateOrderDropdown(orders) {
    let orderDropdown = document.querySelector('select[name="order_id"]');
    orderDropdown.innerHTML = '<option value="">-- Select Purchase Order --</option>';

    orders.forEach(order => {
        let option = document.createElement('option');
        option.value = order.id;
        option.textContent = `Order #${order.order_number} - Date: ${order.order_date}`;
        orderDropdown.appendChild(option);
    });
}

// Function to clear the Purchase Order dropdown
function clearOrderDropdown() {
    let orderDropdown = document.querySelector('select[name="order_id"]');
    orderDropdown.innerHTML = '<option value="">-- Select Purchase Order --</option>';
}

});


</script>

@endsection
