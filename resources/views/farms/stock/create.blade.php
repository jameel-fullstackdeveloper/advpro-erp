@extends('layouts.master')
@section('title')
    Create Material Transfer (Farm)
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

        /* Section Heading */
        .section-heading {
            font-size: 14px;
            font-weight: 500;
            color: #FF9F43;
            position: relative;
            margin-bottom: 20px;
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
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h4 class="mb-sm-0 card-title"> Material Transfer (Farm) </h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Farms</a></li>
                    <li class="breadcrumb-item active"><a href="{{ url('sales/invoices') }}"> Material Transfer </a> > Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div>
    <!-- Invoice Form -->
    <div class="row">
        <div class="col">

            @if (session()->has('formerrors'))
                <div class="alert alert-danger alert-dismissible fade show material-shadow" role="alert">
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

            <form action="{{ route('farmsstock.store') }}" method="POST">
                @csrf
                <div class="invoice-section">
                    <!-- Invoice Details -->
                    <div class="row">

                    <div class="col-6">
                                            <label for="cost_center" class="form-label"> Material Transfer to</label>
                                            <select name="farm_account" class="form-select" id="farm_account" required>
                                                <option value="">---Select Farm ---</option>
                                                    @foreach($farmaccounts as $farmaccount)
                                                        <option value="{{ $farmaccount->id }}">{{ $farmaccount->name }}</option>
                                                    @endforeach
                                            </select>
                                            @error('farm_account') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>


                    <div class="col-md-2">
                            <label class="form-label">Date</label>
                            <input type="date" name="invoice_date" class="form-control shadow-sm" value="{{ old('invoice_date', now()->format('Y-m-d')) }}" required>
                            @error('invoice_date') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-2">
                            <label class="form-label">Reference No.</label>
                            <input type="text" name="reference_number" class="form-control shadow-sm" value="{{ old('reference_number',$RefNumber) }}" required readonly>
                            @error('reference_number') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                        <div class="col-2">
                            <label for="vehicle_no">Vehicle Number</label>
                            <input type="text" name="vehicle_no" class="form-control table-input" value="{{ old('vehicle_no') }}">
                            @error('vehicle_no') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-2 d-none">
                            <label for="vehicle_fare">Freight</label>
                            <input type="number" name="vehicle_fare" class="form-control table-input" oninput="toggleVehicleFareAdjRequired()">
                            @error('vehicle_fare') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-2 d-none">
                            <label for="vehicle_fare_adj">Freight to Credit</label>
                            <select name="vehicle_fare_adj" class="form-control form-select table-input">
                                <option value="">---Select---</option>
                                <option value="1">Farm</option>
                                <option value="2">Freight-Out Payable</option>
                            </select>
                        </div>


                    </div>

                    <!-- Invoice Item Table -->
                    <div class="form-section mt-2">
                        <table class="table table-bordered table-striped" id="invoice-items-table">
                            <thead class="table-info"  style="vertical-align: middle;">
                                <tr>
                                    <th class="text-center text-info" style="width: 15%;">Product</th>
                                    <th class="text-center text-info">Quantity<br><span class="text-dark" style="font-size:10px">Kgs</span></th>
                                    <th class="text-center text-info">Price<br><span class="text-dark" style="font-size:10px">Per Kg</span></th>
                                    <th class="text-center text-info">Amount<br><span class="text-dark" style="font-size:10px">Qty x Price</span></th>
                                    <th class="text-center text-info">Discount <br><span class="text-dark" style="font-size:10px">Percentage</span></th>
                                    <th class="text-center text-info d-none">Bonus <br><span class="text-dark" style="font-size:10px">Per Bag</span></th>
                                    <!--<th class="text-center text-info">Amount<br><span class="text-dark" style="font-size:10px">Excluding Taxes</span></th>-->
                                    <th class="text-center text-info">Net Amount<br><span class="text-dark" style="font-size:10px"></span></th>
                                    <th class="text-center text-info">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $index = 0; @endphp
                                <tr class="invoice-item">
                                    <td style="width:30%">
                                        <select name="items[{{ $index }}][product_id]" class="form-control form-select table-input product-select" required>
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                        @error("items.$index.product_id") <span class="text-danger">{{ $message }}</span> @enderror
                                    </td>

                                    <td>
                                        <input type="number" name="items[{{ $index }}][quantity]" class="form-control table-input quantity-input" step="0.0001" style="width:60px;" oninput="calculateAmounts(this)" required>
                                        @error("items.$index.quantity") <span class="text-danger">{{ $message }}</span> @enderror
                                    </td>

                                    <td>
                                        <input type="number" name="items[{{ $index }}][unit_price]" class="form-control table-input unit-price-input" step="0.0001" oninput="calculateAmounts(this)" required>
                                        @error("items.$index.unit_price") <span class="text-danger">{{ $message }}</span> @enderror
                                    </td>

                                    <td>
                                        <input type="number" name="items[{{ $index }}][net_amount]" class="form-control table-input gross-amount-input" value="0.00" readonly>
                                        @error("items.$index.net_amount") <span class="text-danger">{{ $message }}</span> @enderror
                                    </td>

                                    <td>
                                        <div class="d-flex">
                                            <input type="number" name="items[{{ $index }}][discount_rate]" class="form-control table-input discount-value-input" step="0.01" value="0" oninput="calculateAmounts(this)" placeholder="" style="width:50px;">
                                            <select style="width:40px;" name="items[{{ $index }}][discount_type]"
                                            class="d-none form-select-sm table-input discount-type-input" oninput="calculateAmounts(this)">
                                                <option value="percent">%</option>
                                                <option value="per_bag">B</option>
                                            </select>
                                            <input type="number" name="items[{{ $index }}][discount_amount]" class="form-control table-input discount-amount-input" value="0.00" readonly>
                                            @error("items.$index.discount_rate") <span class="text-danger">{{ $message }}</span> @enderror
                                            @error("items.$index.discount_amount") <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </td>

                                    <td class="d-none">
                                        <div class="d-flex">
                                            <input type="number" name="items[{{ $index }}][discount_per_bag_rate]" class="form-control table-input discount-per-bag-rate-input" value="0.00" oninput="calculateAmounts(this)" style="width:50px;">

                                            <input type="number" name="items[{{ $index }}][discount_per_bag_amount]" class="form-control table-input discount-per-bag-amount-input" value="0.00" readonly>

                                            @error("items.$index.discount_per_bag_rate") <span class="text-danger">{{ $message }}</span> @enderror
                                            @error("items.$index.discount_per_bag_amount") <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </td>

                                    <td class="d-none">
                                        <input type="number" name="items[{{ $index }}][amount_excl_tax]" class="form-control table-input amount-excl-tax-input" value="0.00" readonly>
                                        @error("items.$index.amount_excl_tax") <span class="text-danger">{{ $message }}</span> @enderror
                                    </td>

                                    <td>
                                        <input type="number" name="items[{{ $index }}][amount_incl_tax]" class="form-control table-input amount-incl-tax-input" value="0.00" readonly>
                                        @error("items.$index.amount_incl_tax") <span class="text-danger">{{ $message }}</span> @enderror
                                    </td>

                                    <td style="text-align:center;">
                                        <i class="ri-add-circle-line text-success add-row" style="cursor:pointer;font-size:18px" title="Add New Row" onclick="addRow()"></i>
                                        <i class="ri-delete-bin-5-line text-danger delete-row" style="cursor:pointer;font-size:18px; display:none;" title="Delete Row" onclick="removeRow(this)"></i>
                                    </td>
                                </tr>
                            </tbody>

                            <tfoot class="table-light">
                                <tr>
                                    <th class="text-end">Total:</th>
                                    <th class="text-center"><span class="total_label" id="total-quantity">0</span></th>
                                    <th></th>
                                    <th class="text-center"><span class="total_label" id="total-amount">0</span></th>
                                    <th class="text-center"><span class="total_label" id="total-discount">0</span></th>
                                    <th class="text-center d-none"><span class="total_label" id="total-bonus">0</span></th>
                                    <th class="text-center d-none"><span class="total_label" id="total-amount_excl_tax">0</span></th>
                                    <th class="text-center"><span class="total_label" id="total-net-amount">0</span></th>
                                    <th class="text-center"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Freight Section -->
                    <div class="row">
                            <div class="col-12">
                                <label for="comments">Transfer Comments</label>
                                <input type="text" name="comments" class="form-control table-input">
                                @error('comments') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                    </div>



                    </div>


                    <div class="row">
                        <div class="col-12 text-end mt-3 mb-3">
                            <button type="submit" class="btn btn-success btn-label">
                                <i class="ri-add-circle-line label-icon align-middle fs-16"></i> Create Transfer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
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

            document.querySelector('select[name="segment_id"]').addEventListener('change', function () {
                let segmentId = this.value;

                // Clear the cost center dropdown
                let costCenterDropdown = document.querySelector('select[name="cost_center_id"]');
                costCenterDropdown.innerHTML = '<option value="">---Select Cost Center---</option>';

                if (segmentId) {
                    fetchSegmentDetails(segmentId);
                }
            });


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
    let discountRate = parseFloat(row.querySelector('.discount-value-input').value) || 0;
    let discountPerBagRate = parseFloat(row.querySelector('.discount-per-bag-rate-input').value) || 0;

    // Calculate gross amount
    let grossAmount = quantity * unitPrice;
    row.querySelector('.gross-amount-input').value = grossAmount.toFixed(2);

    // Calculate percentage discount
    let discountAmount = (grossAmount * discountRate) / 100;
    row.querySelector('.discount-amount-input').value = discountAmount.toFixed(2);

    // Calculate per-bag discount
    let discountPerBagAmount = discountPerBagRate * quantity;
    row.querySelector('.discount-per-bag-amount-input').value = discountPerBagAmount.toFixed(2);

    // Calculate total discount
    let totalDiscount = discountAmount + discountPerBagAmount;

    // Calculate net amount excluding tax
    let amountExclTax = grossAmount - totalDiscount;
    row.querySelector('.amount-excl-tax-input').value = amountExclTax.toFixed(2);

    // Calculate net amount (including tax if applicable)
    let netAmount = amountExclTax; // Adjust if taxes are added later
    row.querySelector('.amount-incl-tax-input').value = netAmount.toFixed(2);

    // Update totals for all rows
    updateTotals();
}


        function updateTotals() {
            let totalQuantity = 0;
            let totalAmount = 0;
            let totalDiscount = 0;
            let totalBonus = 0;
            let totalExlTaxAmount = 0;
            let totalNetAmount = 0;

            document.querySelectorAll('#invoice-items-table tbody .invoice-item').forEach(row => {
                totalQuantity += parseFloat(row.querySelector('.quantity-input').value) || 0;
                totalAmount += parseFloat(row.querySelector('.gross-amount-input').value) || 0;
                totalDiscount += parseFloat(row.querySelector('.discount-amount-input').value) || 0;
                totalBonus += parseFloat(row.querySelector('.discount-per-bag-amount-input').value) || 0;
                totalExlTaxAmount += parseFloat(row.querySelector('.amount-excl-tax-input').value) || 0;
                totalNetAmount += parseFloat(row.querySelector('.amount-incl-tax-input').value) || 0;
            });

            document.getElementById('total-quantity').textContent = formatNumberWithCommas(totalQuantity);
            document.getElementById('total-amount').textContent = formatNumberWithCommas(totalAmount);
            document.getElementById('total-discount').textContent = formatNumberWithCommas(totalDiscount);
            document.getElementById('total-bonus').textContent = formatNumberWithCommas(totalBonus);
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

    // Ensure discount_rate is set correctly for the new row
    row.querySelector('.discount-value-input').value = 0;

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
