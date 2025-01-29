<div>

    <style>


    .btn-primary, .btn-danger, .btn-secondary {
        padding: 10px 20px;
        border-radius: 6px;
        transition: background-color 0.3s ease-in-out;
    }

    .btn-primary:hover {
        background-color: #237a6e;
    }

    .btn-danger:hover {
        background-color: #c62828;
    }

    .btn-secondary:hover {
        background-color: #7a7a7a;
    }

    label.form-label {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 5px;
        color: #555;
    }

    .table thead th {
        text-align: center;
    }

    .table tbody td {
        vertical-align: middle;
    }

    /* Custom styles for inputs within the table */
    .table-input, .table-select {
        padding: 4px 8px;
        font-size: 14px;
        border-radius: 4px;
        border: 1px solid #ccc;
        transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    /* Smaller inputs for discount and sales tax rate fields */
    .table-input-small {
        width: 70px;
        padding: 4px;
        font-size: 14px;
        text-align: right;
        border-radius:4px;
    }

    /* Focus effects for inputs */
    .table-input:focus, .table-select:focus {
        border-color: #2a9d8f;
        box-shadow: 0px 0px 8px rgba(42, 157, 143, 0.2);
    }

    /* Adjust the appearance of the select dropdown */
    .form-select.table-input {
        background-color: #f9f9f9;
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 14px;
    }

    /* Align inputs in merged columns */
    .d-flex {
        display: flex;
        align-items: center;
    }

    /* Reduce table cell padding for compact look */
    .table th, .table td {
        vertical-align: middle;
        padding: 8px;
    }



    /* Margin for merged input fields */
    .me-1 {
        margin-right: 5px;
    }

    /* Make modal sections stand out */
.modal-body .form-section {
    padding: 15px;
    background-color: #f9f9f9;
    border-radius: 8px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
}




/* Style for buttons */


.btn-success {
    background-color: #2a9d8f;
    border-color: #2a9d8f;
    color: white;
}

.btn-success:hover {
    background-color: #238a75;
    border-color: #238a75;
}

.table td .form-control  {
   padding:5px;

}

.table-input {
    padding:6px;
}

.tabheading {
    color:#2a9d8f;
}

/* Spinner styles */
.spinner {
    display: none; /* Hide by default */
    position: fixed;
    top: 50%;
    left: 50%;
    width: 50px;
    height: 50px;
    border: 6px solid #ccc;
    border-top-color: #2a9d8f;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 9999; /* Ensure it's on top of everything */
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.table td .form-control {
    padding: 8px;
}


</style>

<!-- Spinner element -->
<div wire:loading wire:target="itemsPerPage,selectedFarm,store, update, create, start_date, end_date,filer_vendor_id, filter_items, searchTerm" class="spinner"></div>


    <!-- Filters Section -->
    <div class="mb-2">
    <div class="card">

        <table class="table table-borderless table-nowrap mb-1">
    <tr>
        <td width="80px;">
            <select wire:model.live="itemsPerPage" id="itemsPerPage" class="form-control form-select">
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="150">150</option>
                <option value="200">200</option>
                <option value="250">250</option>
                <option value="300">300</option>
            </select>
        </td>

        <!-- Start Date -->
        <td width="150px;">
            <input type="date" wire:model.live="start_date" class="form-control" placeholder="Start Date">
            @error('start_date') <span class="text-danger">{{ $message }}</span> @enderror
        </td>

        <!-- End Date -->
        <td width="150px;">
            <input type="date" wire:model.live="end_date" class="form-control" placeholder="End Date">
            @error('end_date') <span class="text-danger">{{ $message }}</span> @enderror
        </td>

        <!-- Vendor Dropdown -->
        <td width="350px;">
            <select wire:model.live="filer_vendor_id" class="form-control form-select">
                <option value="">-- Select Vendor --</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                @endforeach
            </select>
            @error('filer_vendor_id') <span class="text-danger">{{ $message }}</span> @enderror
        </td>

        <!-- Item Filter -->
        <td width="200px;">
            <select wire:model.live="filter_items" class="form-control form-select">
                <option value="">-- Select Items --</option>
                @foreach($allProducts as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
            @error('filter_items') <span class="text-danger">{{ $message }}</span> @enderror
        </td>

        <td>
        <select wire:model.live="selectedFarm" id="selectedFarm" class="form-control form-select">
                                <option value="">-- Select Farm --</option>
                                @foreach($farms as $farm)
                                    <option value="{{ $farm->id }}">{{ $farm->name }}</option>
                                @endforeach
                            </select>

        </td>


        <!-- Create New Button -->
        <td class="text-end">
        @can('purchases bills create')
            <a href="{{ url('farms/bills/farms/create') }}" class="btn btn-success btn-label waves-effect waves-light">
                <i class="bx bx-alarm-add label-icon align-middle fs-16"></i> New
            </a>
        @endcan
        </td>
    </tr>
</table>


    </div>
</div>


<div class="row mb-1 d-none">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle text-center">
                <thead class="table-success">
                    <tr>
                        <th>Bags</th>
                        <th>Gross Amount</th>
                        <th>Sales Tax</th>
                        <th>Further Sales Tax</th>
                        <th>Net Amount</th>
                        <th>Freight</th>
                        <th>Brokerage</th>
                    </tr>
                </thead>
                <tbody class="table-light">
                    <tr>
                        <td class="fs-14 fw-bold"><strong>23,355</strong></td>
                        <td class="fs-14 fw-bold">125,424,700</td>
                        <td class="fs-14 fw-bold">12,542,468</td>
                        <td class="fs-14 fw-bold">0</td>
                        <td class="fs-14 fw-bold">137,967,153</td>
                        <td class="fs-14 fw-bold">0</td>
                        <td class="fs-14 fw-bold">0</td>


                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

    <!-- Purchase Bills Table -->
    <div class="card">
        <div class="card-body">

        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif


        <div class="table-responsive">
    <table class="table table-centered align-middle table-nowrap mb-0">
    <thead class="table-light" style="vertical-align: middle;">
                        <tr>
                        <th class="text-center text-dark">Bill Detail</th>
                            <th class="text-center text-dark">Vendor Detail</th>
                            <th class="text-center text-dark">Product</th>
                            <th class="text-center text-dark d-none">Mode</th>
                            <th class="text-center text-dark">Quantity <br/><span class="text-dark" style="font-size:10px">After Deduction</span></th>
                            <th class="text-center text-dark">Rate <br/> <span class="text-dark" style="font-size:10px">Per kgs / Per unit</span></th>
                            <th class="text-center text-dark">Amount <br/> <span class="text-dark" style="font-size:10px">Excluding Taxes</span></th>
                            <th class="text-center text-dark">Sales Tax <br/> <span class="text-dark" style="font-size:10px">Rate & Amount</span></th>
                            <th class="text-center text-dark">Amount <br/><span class="text-dark" style="font-size:10px">Including Taxes</span></th>
                            <!--<th class="text-center text-info">Freight</th>
                            <th class="text-center text-info">Brokerage</th>-->
                            <th class="text-center text-dark">Farm</th>
                            <th class="text-center text-dark">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Initialize totals
                            $totalQuantity = 0;
                            $totalDeduction = 0;
                            $totalNetQuantity = 0;
                            $totalGrossAmount = 0;
                            $totalSalesTax = 0;
                            $totalWithholdingTax = 0;
                            $totalNetAmount = 0;
                            $totalFreightAmount = 0;
                            $totalBrokrageAmount = 0;
                        @endphp

                        @forelse($purchaseBills as $bill)
                        <tr>
                            <!-- Bill Number and Date -->
                            <td>{{ $bill->bill_number }} <br/>
                            {{ \Carbon\Carbon::parse( $bill->bill_date)->format('d-m-Y') }}
                            </td>

                            <!-- Vendor Details -->
                            <td style="word-wrap: break-word; white-space: normal;"">
                                        <h5 class="fs-14 my-1 fw-medium">
                                            <span class="text-success text-uppercase">{{ $bill->vendor->name }}</span>
                                        </h5>
                                        <span class="text-muted">{{ \App\Models\CustomerDetail::where('account_id', $bill->vendor_id)->with('coaGroupTitle')->first()->coaGroupTitle->name ?? 'N/A' }}</span>
                            </td>

                            <!-- Display each product and its details row by row -->
                            <td class="text-primary">
                                @foreach ($bill->items as $item)
                                <strong>{{ optional($item->product)->name ?? 'N/A' }}</strong><br>
                                @endforeach
                            </td>

                            <td class="d-none">
                            @if($bill->delivery_mode == 'ex-mill')
                                <span class="text-warning fw-medium fs-12">Ex-Mill</span> <br/>
                                <small>{{ $bill->bill_due_days }}  days</small>
                            @endif

                            @if($bill->delivery_mode == 'deliverd')
                                <span class="text-info fw-medium fs-12">Delivered</span> <br/>
                                <small>{{ $bill->bill_due_days }}  days</small>
                            @endif

                                </td>

                            <td>
                                @foreach ($bill->items as $item)
                                        {{ $item->net_quantity }}<br>
                                @php
                                    // Sum quantity for totals
                                    $totalNetQuantity += $item->net_quantity;
                                @endphp
                                @endforeach
                            </td>

                            <!-- Rate -->
                            <td>
                                @foreach ($bill->items as $item)
                                {{ number_format($item->price, 2) }}<br>
                                @endforeach
                            </td>

                            <!-- Gross Amount -->
                            <td>
                                @foreach ($bill->items as $item)
                                {{ number_format($item->gross_amount, 2) }}<br>
                                @php
                                    // Sum gross amount for totals
                                    $totalGrossAmount += $item->gross_amount;
                                @endphp
                                @endforeach
                            </td>

                            <!-- Sales Tax -->
                            <td>
                                @foreach ($bill->items as $item)
                                    {{ number_format($item->sales_tax_amount, 2) }}<br>
                                    <small class="text-muted"> @ {{ number_format($item->sales_tax_rate, 2) }} %</small>
                                    @php
                                        // Sum sales tax for totals
                                        $totalSalesTax += $item->sales_tax_amount;
                                    @endphp
                                @endforeach
                            </td>



                            <!-- Net Amount -->
                            <td>
                                @foreach ($bill->items as $item)
                                {{ number_format($item->net_amount, 2) }}<br>
                                @php
                                    // Sum net amount for totals
                                    $totalNetAmount += $item->net_amount;
                                @endphp
                                @endforeach
                            </td>





                            <td class="d-none">  {{ number_format($bill->freight) }} @php $totalFreightAmount += $bill->freight; @endphp

                            </td>

                            <td  class="d-none">
                                @if($bill->broker_amount > 0)
                                    {{ number_format($bill->broker_amount, 2) }} <br/>
                                    <small class="text-muted">
                                        @if($bill->broker_id == 0)
                                            Self
                                        @else
                                            @php
                                                $broker = \App\Models\ChartOfAccount::find($bill->broker_id);
                                                $brokerName = $broker?->name ?? 'N/A';
                                                $firstWord = strtok($brokerName, ' ');
                                            @endphp
                                            <span title="{{ $brokerName }}">{{ $firstWord }}@if(strlen($brokerName) > strlen($firstWord))...@endif</span><br/>
                                            <!--<small class="text-muted"> @ {{ number_format($bill->broker_rate, 2) }} %</small>-->
                                        @endif
                                    </small>
                                @else
                                    {{ number_format($bill->broker_amount, 2) }}
                                @endif
                                @php $totalBrokrageAmount += $bill->broker_amount; @endphp
                            </td>

                            <td style="word-wrap: break-word; white-space: normal;"> @php
                                $farmAccount = \App\Models\ChartOfAccount::find($bill->farm_account);
                            @endphp
                            <span class="text-info fw-bold">{{ $farmAccount ? $farmAccount->name : 'N/A' }} </span>

                        </td>


                            <!-- Actions -->
                            <td>
                            <div class="hstack gap-1 flex-wrap">

                                @can('purchases bills edit')

                                    <a href="{{ route('billsfarms.edit', $bill->id) }}" class="link-success fs-15" title="Edit Bill">
                                        <i class="ri-edit-2-line" style="font-size:16px;"></i>
                                    </a>
                                @endcan

                                @can('purchases bills delete')
                                        <a wire:click="confirmDeletionBill({{ $bill->id }})" href="javascript:void(0);" class="link-danger fs-15" title="Delete Purchase Bill">
                                        <i class="ri-delete-bin-line" style="font-size:16px;"></i></a>
                                @endcan


                                <div class="dropdown">
                                    <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-more-2-fill text-muted"></i>
                                    </a>

                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                                        <li>
                                            <a class="dropdown-item" href="#">Created: {{ $bill->createdBy->name }}, {{ $bill->created_at->format('d-m-Y h:i A') }}</a>
                                        </li>

                                        @if($bill->updatedBy)
                                        <li>
                                            <a class="dropdown-item" href="#">Updated: {{ $bill->updatedBy->name }}, {{ $bill->updated_at->format('d-m-Y h:i A') }}</a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>


                                </div>


                            </td>
                        </tr>
                             @empty
                                <tr>
                                    <td colspan="14" class="text-center">No Purchase Bill found</td>
                                </tr>
                                @endforelse
                    </tbody>

                    <!-- Total Row -->
                    <tfoot class="table-info">
                        <tr>
                            <th colspan="3" class="text-center">Total:</th>
                            <th>{{ number_format($totalNetQuantity, 2) }}</th>
                            <th></th>
                            <th>{{ number_format($totalGrossAmount, 2) }}</th>
                            <th>{{ number_format($totalSalesTax, 2) }}</th>
                            <th>{{ number_format($totalNetAmount, 2) }}</th>
                            <th></th>
                            <th></th>



                        </tr>
                    </tfoot>
                </table>


            {{ $purchaseBills->links() }}

                                                </div>
        </div>
    </div>

    <!-- Modal -->
    <div wire:ignore.self class="modal fade" id="modal_bill" tabindex="-1" aria-labelledby="modal_billLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header card-header align-items-cd-flex pb-2">
                    <h4 class="card-title mb-0 flex-grow-1" id="myModal_orderLabel">
                        {{ $isEditMode ? 'Edit Purchase Bill' : 'Add New Purchase Bill' }}
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            <div class="modal-body">

            {{ $bill_number }}


            <div class="form-section">
                <div class="row mb-3">
                    <!-- Bill Date -->
                    <div class="col-md-2">
                        <label  class="form-label" for="bill_date">Bill Date</label>
                        <input type="date" wire:model="bill_date" class="form-control">
                        @error('bill_date') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-2">
                        <label  class="form-label" for="vehicle_no">Vehicle Number </label>
                        <input type="text" wire:model="vehicle_no" class="form-control">
                        @error('vehicle_no') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <!-- Vendor Dropdown -->
                    <div class="col-md-5">
                        <label class="form-label" for="vendor_id">Vendor</label>
                        <select wire:model.live="vendor_id" class="form-select">
                            <option value="">-- Select Vendor --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                        @error('vendor_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                     <!-- Purchase Order Dropdown -->
                     <div class="col-md-3">
                            <label class="form-label" for="order_id">Purchase Order</label>
                            <select wire:model.live="order_id" class="form-select">
                                <option value="">-- Select Purchase Order --</option>
                                @foreach($purchaseOrders as $order)
                                    <option value="{{ $order->id }}" {{ $order_id == $order->id ? 'selected' : '' }}>Order #{{ $order->order_number }}</option>
                                @endforeach
                            </select>
                            @error('order_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                </div>
</div>

<div class="form-section">

                <!-- Bill Items -->
                <div class="row mb-3">
                    <div class="col-12">
                       <!-- Bill Items Table -->
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Product</th>
                <th>Quantity <br/><span style="font-size:10px" class="">kgs / unit</span></th>
                <th>Deduction <br/><span style="font-size:10px">kgs / unit</span> </th>
                <th>Net Quantiy <br/><span style="font-size:10px">kgs / unit</span> </th>
                <th>Rate <br/><span style="font-size:10px">per kgs / per unit</span></th>
                <th>Gross Amount <br/><span style="font-size:10px">excluding taxes</span> </th>
                <th>Sales Tax</th>
                <!--<th>Withholding Tax</th>-->
                <th>Net Amount <br/><span style="font-size:10px">including taxes</span></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
                    @foreach($items as $index => $item)
                        <tr>
                            <td width="20%">
                                <select wire:model="items.{{ $index }}.product_id" class="form-control form-select table-input">
                                    <option value="">-- Select Product --</option>
                                    @foreach($allProducts as $product)
                                        <option value="{{ $product->id }}">{{ $product->item_name }}</option>
                                    @endforeach
                                </select>
                                @error('items.' . $index . '.product_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </td>
                            <td>
                                <input type="number" wire:model.defer ="items.{{ $index }}.quantity" class="form-control" step="any" wire:input="calculateAmounts({{ $index }})">
                                @error('items.' . $index . '.quantity') <span class="text-danger">{{ $message }}</span> @enderror
                            </td>
                            <td>
                                <input type="number" wire:model.defer ="items.{{ $index }}.deduction" class="form-control" step="any" wire:input="calculateAmounts({{ $index }})">
                                @error('items.' . $index . '.deduction') <span class="text-danger">{{ $message }}</span> @enderror
                            </td>
                            <td>
                                <input type="number" wire:model.defer ="items.{{ $index }}.net_quantity" class="form-control" step="any" readonly>
                                @error('items.' . $index . '.net_quantity') <span class="text-danger">{{ $message }}</span> @enderror
                            </td>
                            <td>
                                <input type="number" wire:model.defer ="items.{{ $index }}.price" class="form-control" step="any" wire:input="calculateAmounts({{ $index }})">
                                @error('items.' . $index . '.price') <span class="text-danger">{{ $message }}</span> @enderror
                            </td>
                            <td>
                                <input type="number" wire:model="items.{{ $index }}.gross_amount" class="form-control" step="any" readonly>
                                @error('items.' . $index . '.gross_amount') <span class="text-danger">{{ $message }}</span> @enderror
                            </td>

                          <!-- Sales Tax -->
                            <td>
                                <div class="d-flex">
                                    <input type="number" wire:model.defer="items.{{ $index }}.sales_tax_rate" step="0.01" class="form-control table-input-small me-1" placeholder="Rate (%)" wire:input="calculateAmounts({{ $index }})" min="0">
                                    <input type="number" wire:model="items.{{ $index }}.sales_tax_amount" step="0.01" class="form-control table-input" min="0" readonly>
                                </div>
                                @error('items.' . $index . '.sales_tax_rate') <span class="text-danger">{{ $message }}</span> @enderror
                                @error('items.' . $index . '.sales_tax_amount') <span class="text-danger">{{ $message }}</span> @enderror
                            </td>

                            <!-- Withholding Tax -->
                            <td class="d-none">
                                <div class="d-flex">
                                    <input type="number" wire:model.defer="items.{{ $index }}.withholding_tax_rate" step="0.01" class="form-control table-input-small me-1" placeholder="Rate (%)" wire:input="calculateAmounts({{ $index }})" min="0">
                                    <input type="number" wire:model="items.{{ $index }}.withholding_tax_amount" step="0.01" class="form-control table-input" min="0" readonly>
                                </div>
                                @error('items.' . $index . '.withholding_tax_rate') <span class="text-danger">{{ $message }}</span> @enderror
                                @error('items.' . $index . '.withholding_tax_amount') <span class="text-danger">{{ $message }}</span> @enderror
                            </td>


                            <td>
                                <input type="number" wire:model="items.{{ $index }}.net_amount" class="form-control" step="any" readonly>
                                @error('items.' . $index . '.net_amount') <span class="text-danger">{{ $message }}</span> @enderror
                            </td>
                            <td>
                                @if ($index === 0)
                                    <i class="ri-add-circle-line text-success" style="cursor:pointer;font-size:20px" wire:click="addItem()" title="Add New Row"></i>
                                @else
                                    <i class="ri-delete-bin-5-line text-danger" style="cursor:pointer;font-size:20px" wire:click="removeItem({{ $index }})" title="Delete Row"></i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
        </tbody>
    </table>




                    </div>
                </div>
</div>


        <div class="form-section">
                <!-- Status and Comments -->
                <div class="row mb-3">

                    <div class="col-md-2">
                        <label class="form-label" for="status">Delivery Mode</label>
                                <select wire:model="delivery_mode" class="form-select">
                                <option value="">-- Select Delivery Mode --</option>
                                <option value="ex-mill">Ex-mill</option>
                                <option value="deliverd">Delivered</option>
                            </select>
                        @error('delivery_mode') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-1">
                        <label class="form-label" for="bill_due_days">Due Days</label>
                        <input type="number" wire:model="bill_due_days" class="form-control" id="bill_due_days">
                        @error('bill_due_days') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-1">
                        <label class="form-label" for="freight">Freight In</label>
                        <input type="number" wire:model="freight" class="form-control" id="freight">
                        @error('freight') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4 mb-2">
                        <label class="form-label" for="broker">Broker</label>
                        <select wire:model="broker_id" class="form-select">
                            <option value="">-- Select Broker --</option>
                            @foreach($brokers as $broker)
                                <option value="{{ $broker->id }}">{{ $broker->name }}</option>
                            @endforeach
                        </select>
                        @error('broker_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="form-label" for="broker_rate">Brokery Rate</label>
                        <input type="number" wire:model="broker_rate" class="form-control" id="broker_rate">
                        @error('broker_rate') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="form-label" for="broker_amount">Brokery Amount</label>
                        <input type="number" wire:model="broker_amount" class="form-control" id="broker_amount">
                        @error('broker_amount') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <label class="form-label" for="comments">Comments (if any)</label>
                        <input type="text" wire:model="comments" class="form-control">
                        @error('comments') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

            </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" wire:click="store()" class="btn btn-primary">{{ $isEditMode ? 'Update' : 'Save' }}</button>
            </div>
        </div>
    </div>
</div>
</div>

@script
<script>

$wire.on('swal:confirm-deletion', ({ voucherId }) => {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('deletePurchaseBill', voucherId);  // Call the delete method with the voucher ID
            }
        });
    });


    window.addEventListener('showModal_bill', event => {
        var myModal_bill = new bootstrap.Modal(document.getElementById('modal_bill'));
        myModal_bill.show();
    });

    window.addEventListener('hideModal_bill', event => {
        var myModal_bill = bootstrap.Modal.getInstance(document.getElementById('modal_bill'));
        if (myModal_bill) {
            myModal_bill.hide();
        }
    });
</script>

@endscript
