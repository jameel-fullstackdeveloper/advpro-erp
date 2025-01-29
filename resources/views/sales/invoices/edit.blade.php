@extends('layouts.master')

@section('title', 'Edit Sale (Mill)')

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

       /* Section Heading */
       .section-heading {
            font-size: 14px;
            font-weight: 500;
            color: #FF9F43;
            position: relative;
            margin-bottom: 20px;
            padding-bottom: 5px;
            /*text-transform: uppercase;
            text-decoration:underline;*/
            letter-spacing: 1px; /* Adds some spacing between the letters for better readability */

        }

        .section-heading::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px; /* Thickness of the underline */
            background-color: #28a745; /* Green color to match the text */
            border-radius: 5px; /* Rounded corners for a softer look */
            transform: scaleX(0); /* Initially scales the underline to 0 for animation */
            transition: transform 0.3s ease; /* Smooth animation for the underline */
        }



        /* Section Blocks */
        .invoice-section,
        .delivery-challan-section,
        .brokerage-section {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
        }

        .invoice-section .row,
        .delivery-challan-section .row,
        .brokerage-section .row {
            margin-bottom: 5px;
        }

        /* Buttons */
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


    </style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 card-title">Edit Sale (Mill)</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Sales</a></li>
                    <li class="breadcrumb-item active"><a href="{{ url('sales/invoices') }}"> Sale (Mill) </a> > Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        @if (session()->has('formerrors'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri-notification-off-line label-icon"></i> {{ session('formerrors') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

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

        <form action="{{ route('invoices.update', $invoice->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="invoice-section">
        <h5 class="section-heading tabheading mb-2">Invoice Details</h5>

        <!-- Invoice Details -->
        <div class="row">
            <div class="col-md-2">
                <label class="form-label">Invoice Date</label>
                <input type="date" name="invoice_date" class="form-control shadow-sm"
                    value="{{ old('invoice_date', $invoice->invoice_date ?? now()->format('Y-m-d')) }}" required>
                @error('invoice_date') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-2">
                <label class="form-label">Invoice Number</label>
                <input type="text" name="invoice_number" class="form-control shadow-sm" placeholder="Invoice Number"
                    value="{{ old('invoice_number', $invoice->invoice_number) }}" required  readonly />
                @error('invoice_number') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-8">
                <label class="form-label">Select Customer</label>
                <select name="customer_id" class="form-control form-select" required>
                    <option value="">-- Select Customer --</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }}  [ {{ $customer->customerGroup ? $customer->customerGroup->name : 'No Group' }} ]
                        </option>
                    @endforeach
                </select>
                @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Delivery Challan Section -->
        <div class="row">
            <div class="col-md-2">
                <label class="form-label">Due Days</label>
                <input type="number" name="invoice_due_days" class="form-control shadow-sm" placeholder="Due Days"
                    value="{{ old('invoice_due_days', $invoice->invoice_due_days) }}" required />
                @error('invoice_due_days') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-2">
                <label for="vehicle_no">Vehicle Number</label>
                <input type="text" name="vehicle_no" class="form-control table-input" value="{{ old('vehicle_no', $salesOrders->vehicle_no) }}">
                @error('vehicle_no') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-2">
                <label for="farm_name">Farm Name</label>
                <input type="text" name="farm_name" class="form-control table-input" value="{{ old('farm_name', $salesOrders->farm_name) }}">
                @error('farm_name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-4">
                <label for="farm_address">Farm Address</label>
                <input type="text" name="farm_address" class="form-control table-input" value="{{ old('farm_address', $salesOrders->farm_address) }}">
                @error('farm_address') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-2">
                <label for="farm_supervisor_mobile">Supervisor Mobile</label>
                <input type="text" name="farm_supervisor_mobile" class="form-control table-input" value="{{ old('farm_supervisor_mobile', $salesOrders->farm_supervisor_mobile) }}">
                @error('farm_supervisor_mobile') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-section mt-2">
        <table class="table table-bordered table-striped" id="invoice-items-table">
        <thead class="table-info"  style="vertical-align: middle;">
                    <tr>
                    <th class="text-center text-info" style="width: 30%;">Product</th>
                    <th class="text-center text-info">Quantity<br><span class="text-dark" style="font-size:10px">Bags </span</th>
                    <th class="text-center text-info">Price<br><span class="text-dark" style="font-size:10px">Per Bag </span></th>
                    <th class="text-center text-info">Amount<br><span class="text-dark" style="font-size:10px">Qty x Price </span></th>
                    <th class="text-center text-info">Discount <br><span class="text-dark" style="font-size:10px">Percentage  </span></th>
                    <th class="text-center text-info">Bonus<br><span class="text-dark" style="font-size:10px">Per Bag </span></th>
                    <th class="text-center text-info d-none">Sales Tax<br><span class="text-dark" style="font-size:10px">Rate & Amount </span></th>
                    <th class="text-center text-info d-none">Further Tax<br><span class="text-dark" style="font-size:10px">Rate & Amount </span></th>
                    <th class="text-center text-info d-none">WHT<br><span class="text-dark" style="font-size:10px">With Holding Tax</span></th>
                    <th class="text-center text-info">Amount <br><span class="text-dark" style="font-size:10px">Including Taxes </span></th>
                    <th class="text-center text-info">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $index => $item)
                        <tr class="invoice-item">
                            <td style="width:30%">
                                <select name="items[{{ $index }}][product_id]" class="form-control form-select table-input" required>
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('items.' . $index . '.product_id', $item->product_id) == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("items.$index.product_id") <span class="text-danger">{{ $message }}</span> @enderror
                            </td>

                            <td>
                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control table-input quantity-input" step="0.0001" style="width:60px;" value="{{ old('items.' . $index . '.quantity', $item->quantity) }}" oninput="calculateAmounts(this)" required>
                                @error("items.$index.quantity") <span class="text-danger">{{ $message }}</span> @enderror
                            </td>

                            <td>
                                <input type="number" name="items[{{ $index }}][unit_price]" class="form-control table-input unit-price-input" step="0.0001" value="{{ old('items.' . $index . '.unit_price', $item->unit_price) }}" oninput="calculateAmounts(this)" required>
                                @error("items.$index.unit_price") <span class="text-danger">{{ $message }}</span> @enderror
                            </td>

                            <td>
                                <input type="number" name="items[{{ $index }}][net_amount]" class="form-control table-input gross-amount-input"
                                 value="{{ old('items.' . $index . '.net_amount', $item->net_amount) }}" readonly>
                                @error("items.$index.net_amount") <span class="text-danger">{{ $message }}</span> @enderror
                            </td>

                            <td>
                                <div class="d-flex">
                                    <input type="number" name="items[{{ $index }}][discount_rate]" class="form-control table-input discount-value-input" step="0.01" value="{{ old('items.' . $index . '.discount_rate', $item->discount_rate) }}" oninput="calculateAmounts(this)" style="width:50px;">

                                    <select style="width:40px;" name="items[{{ $index }}][discount_type]"
                                            class="d-none form-select-sm table-input discount-type-input" oninput="calculateAmounts(this)">
                                                <option value="percent">%</option>
                                                <option value="per_bag">B</option>
                                            </select>


                                    <input type="number" name="items[{{ $index }}][discount_amount]" class="form-control table-input discount-amount-input" value="{{ old('items.' . $index . '.discount_amount', $item->discount_amount) }}" readonly>
                                    @error("items.$index.discount_rate") <span class="text-danger">{{ $message }}</span> @enderror
                                    @error("items.$index.discount_amount") <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </td>

                            <td>
                            <div class="d-flex">
                                    <input type="number" name="items[{{ $index }}][discount_per_bag_rate]"
                                    class="form-control table-input discount-per-bag-rate-input" step="0.01" value="{{ old('items.' . $index . '.discount_per_bag_rate', $item->discount_per_bag_rate) }}" oninput="calculateAmounts(this)" style="width:50px;">
                                    <input type="number" name="items[{{ $index }}][discount_per_bag_amount]"
                                    class="form-control table-input discount-per-bag-amount-input" value="{{ old('items.' . $index . '.discount_per_bag_amount', $item->discount_per_bag_amount) }}" readonly>
                                    @error("items.$index.discount_per_bag_rate") <span class="text-danger">{{ $message }}</span> @enderror
                                    @error("items.$index.discount_per_bag_amount") <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </td>

                            <td class="d-none">
                                <div class="d-flex">
                                <input type="number" name="items[{{ $index }}][sales_tax_rate]" class="form-control table-input sales-tax-value-input" step="0.01" value="{{ old('items.' . $index . '.sales_tax_rate', $item->sales_tax_rate) }}" oninput="calculateAmounts(this)" style="width:50px;">
                                <select style="width:40px;" name="items[{{ $index }}][sales_tax_type]" class="d-none form-select-sm table-input sales-tax-type-input" oninput="calculateAmounts(this)">
                                    <option value="percent" {{ old('items.' . $index . '.sales_tax_type', $item->sales_tax_type) == 'percent' ? 'selected' : '' }}>%</option>
                                    <option value="per_bag" {{ old('items.' . $index . '.sales_tax_type', $item->sales_tax_type) == 'per_bag' ? 'selected' : '' }}>B</option>
                                </select>
                                <input type="number" name="items[{{ $index }}][sales_tax_amount]" class="form-control table-input sales-tax-amount-input" value="{{ old('items.' . $index . '.sales_tax_amount', $item->sales_tax_amount) }}" readonly>
                                @error("items.$index.sales_tax_rate") <span class="text-danger">{{ $message }}</span> @enderror
                                @error("items.$index.sales_tax_amount") <span class="text-danger">{{ $message }}</span> @enderror
                             </div>
                            </td>

                            <td class="d-none">
                                <div class="d-flex">
                                    <input type="number" name="items[{{ $index }}][further_sales_tax_rate]" class="form-control table-input further-sales-tax-rate-input" value="{{ old('items.' . $index . '.further_sales_tax_rate', $item->further_sales_tax_rate) }}" step="0.01" oninput="calculateAmounts(this)"  style="width:50px;">
                                    <input type="number" name="items[{{ $index }}][further_sales_tax_amount]" class="form-control table-input further-sales-tax-input" value="{{ old('items.' . $index . '.further_sales_tax_amount', $item->further_sales_tax_amount) }}" readonly>
                                </div>
                            </td>

                            <td class="d-none">
                                <div class="d-flex">
                                    <input type="number" name="items[{{ $index }}][advance_wht_rate]" class="form-control table-input advance-wht-rate-input" value="{{ old('items.' . $index . '.advance_wht_rate', $item->advance_wht_rate) }}" step="0.01" oninput="calculateAmounts(this)" style="width:50px;">
                                    <input type="number" name="items[{{ $index }}][advance_wht_amount]" class="form-control table-input advance-wht-input" value="{{ old('items.' . $index . '.advance_wht_amount', $item->advance_wht_amount) }}" readonly>
                                </div>
                            </td>

                            <td class="d-none">
                                        <input type="number" name="items[{{ $index }}][amount_excl_tax]" class="form-control table-input amount-excl-tax-input" value="0.00" readonly>
                                        @error("items.$index.amount_excl_tax") <span class="text-danger">{{ $message }}</span> @enderror
                                    </td>

                            <td>
                                <input type="number" name="items[{{ $index }}][amount_incl_tax]" class="form-control table-input amount-incl-tax-input" value="{{ old('items.' . $index . '.amount_incl_tax', $item->amount_incl_tax) }}" readonly>
                                @error("items.$index.amount_incl_tax") <span class="text-danger">{{ $message }}</span> @enderror
                            </td>

                            <td style="text-align:center;">
                                    <i class="ri-add-circle-line text-success add-row" style="cursor:pointer; font-size:18px;" title="Add New Row" onclick="addRow()"></i>
                                     <i class="ri-delete-bin-5-line text-danger delete-row" style="cursor:pointer; font-size:18px; display:none;" title="Delete Row" onclick="removeRow(this)"></i>
                                    </td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot class="table-light">
                    <tr>
                        <th class="text-end">Totals:</th>
                        <th class="text-center"><span class="total_label" id="total-quantity">0</span></th>
                        <th></th>
                        <th class="text-center"><span class="total_label" id="total-amount">0</span></th>
                        <th class="text-center"><span class="total_label" id="total-discount">0</span></th>
                        <th class="text-center"><span class="total_label" id="total-amount_excl_tax">0</span></th>
                        <th class="text-center d-none"><span class="total_label" id="total-sales-tax">0</span></th>
                        <th class="text-center  d-none"><span class="total_label" id="total-further-sales-tax">0</span></th>
                        <th class="text-center  d-none"><span class="total_label" id="total-advance-wht">0</span></th>
                        <th class="text-center"><span class="total_label" id="total-net-amount">0</span></th>
                        <th class="text-center"></th>
                    </tr>
                </tfoot>
            </table>

            <script>

function formatNumberWithCommas(number) {
    return number.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}






</script>

<div class="row">

    <div class="col-2">
        <label for="vehicle_fare">Freight</label>
        <input type="number" name="vehicle_fare" class="form-control table-input"  oninput="toggleVehicleFareAdjRequired()" value="{{ old('vehicle_fare', $salesOrders->vehicle_fare) }}">
        @error('vehicle_fare') <span class="text-danger">{{ $message }}</span> @enderror
    </div>

    <div class="col-2 ">
        <label for="vehicle_fare_adj">Freight Credit to</label>
        <select name="vehicle_fare_adj" class="form-control form-select table-input">
            <option value="">---Select---</option>
            <option value="1" {{ old('vehicle_fare_adj', $invoice->freight_credit_to) == 1 ? 'selected' : '' }}>Customer</option>
            <option value="2" {{ old('vehicle_fare_adj', $invoice->freight_credit_to) == 2 ? 'selected' : '' }}>Freight-Out Payable</option>
        </select>
    </div>

    <div class="col-8">
        <label for="comments">Invoice Comments</label>
        <input type="text" name="comments" class="form-control table-input" value="{{ old('comments', $invoice->comments) }}">
        @error('comments') <span class="text-danger">{{ $message }}</span> @enderror
    </div>

</div>

</div>

</div>

    <!-- Brokerage Section Block -->
 <div class="brokerage-section">
<div class="row">
<h5 class="section-heading tabheading mb-2">Commission Details</h5>



<div class="col-6">
    <label for="broker_id">Sales Commission Agent</label>
    <select name="broker_id" class="form-control form-select shadow-sm" id="broker_id">
        <option value="">-- Select --</option>
        @foreach($brokers as $broker)
            <option value="{{ $broker->id }}"  {{ old('broker_id', $invoice->broker_id) == $broker->id ? 'selected' : '' }}>{{ $broker->name }}</option>
        @endforeach
    </select>
    @error('broker_id') <span class="text-danger">{{ $message }}</span> @enderror
</div>

<div class="col-1">
    <label for="broker_rate">Rate</label>
    <input type="number" name="broker_rate" class="form-control table-input" id="broker_rate" step="0.001" value="{{ old('broker_rate', $invoice->broker_rate) }}">
    @error('broker_rate') <span class="text-danger">{{ $message }}</span> @enderror
</div>

<div class="col-2">
    <label for="calculation_method">Calculation Method</label>
    <select name="calculation_method" class="form-control form-select shadow-sm" id="calculation_method">
        <option value="">-- Select Method --</option>
        <option value="percentage" {{ old('calculation_method', $invoice->calculation_method) == 'percentage' ? 'selected' : '' }}>Percentage on Amount</option>
        <option value="quantity" {{ old('calculation_method', $invoice->calculation_method) == 'quantity' ? 'selected' : '' }}>Multiply by Quantity</option>
    </select>
</div>

<div class="col-2">
    <label for="broker_amount">Brokerage Amount</label>
    <input type="number" name="broker_amount" class="form-control table-input" id="broker_amount" step="0.001" value="{{ old('broker_amount', $invoice->broker_amount) }}">
    @error('broker_amount') <span class="text-danger">{{ $message }}</span> @enderror
</div>

</div>




                        </div>
                    </div>


                    <div class="row">

<div class="col-12 text-end mb-3">
    <button type="submit" class="btn btn-primary shadow-sm mt-2">Update Invoice</button>
</div>

            </div>

</form>

    </div>
</div>
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/sweetalerts.init.js') }}"></script>
  <!--jquery cdn-->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<!--select2 cdn-->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="{{ URL::asset('build/js/pages/select2.init.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
<script>
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelector('select[name="customer_id"]').addEventListener('change', function () {
                let customerId = this.value;
                if (customerId) {
                    fetchInvoiceDetails(customerId);
                } else {
                    document.querySelector('input[name="invoice_due_days"]').value = '';
                    document.querySelector('input[name="items[0][discount_rate]"]').value = '';
                }
            });

            document.querySelector('select[name="broker_id"]').addEventListener('change', function () {
                let brokerId = this.value;
                if (brokerId) {
                    fetchBrokerDetails(brokerId);
                } else {
                    document.querySelector('input[name="broker_rate"]').value = '';
                }
            });

            document.querySelector('#invoice-items-table tbody').addEventListener('change', function (e) {
                if (e.target && e.target.name.includes('product_id')) {
                    let productId = e.target.value;
                    let row = e.target.closest('tr');
                    if (productId) {
                        fetchProductDetails(productId, row);
                    }
                }
            });
        });


        function fetchSegmentDetails(segmentId) {
        fetch(`/get-segment-details/${segmentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let costCenterDropdown = document.querySelector('select[name="cost_center_id"]');
                        data.costcenters.forEach(costcenter => {
                            let option = document.createElement('option');
                            option.value = costcenter.id;
                            option.textContent = costcenter.name;
                            costCenterDropdown.appendChild(option);
                        });
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching cost centers:', error);
                });
        }

        function fetchInvoiceDetails(customerId) {
            fetch(`/get-customer-details/${customerId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('input[name="invoice_due_days"]').value = data.invoice_due_days;
                        document.querySelector('input[name="items[0][discount_rate]"]').value = data.discount_rate;
                    }
                })
                .catch(error => console.error('Error:', error));
        }

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

        function fetchProductDetails(productId, row) {
            fetch(`/get-product-details/${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        row.querySelector('input[name^="items"][name$="[unit_price]"]').value = data.price;
                        calculateAmounts(row.querySelector('input[name^="items"][name$="[unit_price]"]'));
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function calculateAmounts(inputElement) {
            let row = inputElement.closest('tr');
            let quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            let unitPrice = parseFloat(row.querySelector('.unit-price-input').value) || 0;
            let discountValue = parseFloat(row.querySelector('.discount-value-input').value) || 0;
            let discountType = row.querySelector('.discount-type-input').value;
            let discountPerBagRate = parseFloat(row.querySelector('.discount-per-bag-rate-input').value) || 0;

            let grossAmount = quantity * unitPrice;
            row.querySelector('.gross-amount-input').value = grossAmount.toFixed(2);

            let discountAmount = discountType === 'percent' ? (grossAmount * discountValue / 100).toFixed(2) : (quantity * discountValue).toFixed(2);
            row.querySelector('.discount-amount-input').value = discountAmount;

            let discountPerBagAmount = discountPerBagRate * quantity;
            row.querySelector('.discount-per-bag-amount-input').value = discountPerBagAmount.toFixed(2);

            let amountExclTax = grossAmount - discountAmount - discountPerBagAmount;
            row.querySelector('.amount-excl-tax-input').value = amountExclTax.toFixed(2);

            let netAmount = amountExclTax;
            row.querySelector('.amount-incl-tax-input').value = netAmount.toFixed(2);

            updateTotals();
        }

        function updateTotals() {
            let totalQuantity = 0;
            let totalAmount = 0;
            let totalDiscount = 0;
            let totalExlTaxAmount = 0;
            let totalNetAmount = 0;

            document.querySelectorAll('#invoice-items-table tbody .invoice-item').forEach(row => {
                totalQuantity += parseFloat(row.querySelector('.quantity-input').value) || 0;
                totalAmount += parseFloat(row.querySelector('.gross-amount-input').value) || 0;
                totalDiscount += parseFloat(row.querySelector('.discount-amount-input').value) || 0;
                totalExlTaxAmount += parseFloat(row.querySelector('.amount-excl-tax-input').value) || 0;
                totalNetAmount += parseFloat(row.querySelector('.amount-incl-tax-input').value) || 0;
            });

            document.getElementById('total-quantity').textContent = formatNumberWithCommas(totalQuantity);
            document.getElementById('total-amount').textContent = formatNumberWithCommas(totalAmount);
            document.getElementById('total-discount').textContent = formatNumberWithCommas(totalDiscount);
            document.getElementById('total-amount_excl_tax').textContent = formatNumberWithCommas(totalExlTaxAmount);
            document.getElementById('total-net-amount').textContent = formatNumberWithCommas(totalNetAmount);
        }

        function formatNumberWithCommas(number) {
            return number.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        function toggleVehicleFareAdjRequired() {
            const vehicleFareInput = document.querySelector('input[name="vehicle_fare"]');
            const vehicleFareAdjSelect = document.querySelector('select[name="vehicle_fare_adj"]');

            if (vehicleFareInput.value.trim() !== '' && vehicleFareInput.value.trim() !== '0') {
                vehicleFareAdjSelect.setAttribute('required', 'true');
            } else {
                vehicleFareAdjSelect.removeAttribute('required');
            }
        }

        function addRow() {
    let tableBody = document.querySelector('#invoice-items-table tbody');
    let row = tableBody.querySelector('tr').cloneNode(true); // Clone the first row
    let newIndex = tableBody.querySelectorAll('tr').length; // Get the new index for the row

    let inputs = row.querySelectorAll('input, select');
    inputs.forEach(input => {
        // Update the input names for the new row based on the new index
        let name = input.name.replace(/\[\d+\]/, `[${newIndex}]`);
        input.name = name;
        input.value = ''; // Reset values for all fields in the new row
    });

    // Get the previous row to copy values from
    let previousRow = tableBody.querySelectorAll('tr')[newIndex - 1];

    // Copy the required values from the previous row
    let previousDiscount = previousRow.querySelector('.discount-value-input').value || 0;
   // let previousSalesTax = previousRow.querySelector('.sales-tax-value-input').value || 0;
   // let previousFurtherSalesTax = previousRow.querySelector('.further-sales-tax-rate-input').value || 0;
   // let previousAdvanceWHT = previousRow.querySelector('.advance-wht-rate-input').value || 0;

    // Set the copied values in the new row
    row.querySelector('.discount-value-input').value = 0;
    row.querySelector('.discount-per-bag-rate-input').value = 0;
    //row.querySelector('.sales-tax-value-input').value = previousSalesTax;
    //row.querySelector('.further-sales-tax-rate-input').value = previousFurtherSalesTax;
    //row.querySelector('.advance-wht-rate-input').value = previousAdvanceWHT;

    // Trigger the calculation for the new row to update the amounts
    calculateAmounts(row.querySelector('.discount-value-input'));

    // Hide the "Add" option for rows other than the first one
    if (newIndex > 0) {
        row.querySelector('.add-row').style.display = 'none';
    }

    // Show the "Delete" button for all rows except the first one
    if (newIndex > 0) {
        row.querySelector('.delete-row').style.display = 'inline';
    }

    // Add the new row to the table body
    tableBody.appendChild(row);
}

function removeRow(buttonElement) {
    let row = buttonElement.closest('tr');
    row.remove();
}

    </script>

@endsection
