<div>
    <!-- Custom Styles -->
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
    font-size:14px;
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


</style>

<!-- Spinner element -->
<div wire:loading wire:target="store, update, create, startDate, endDate, selectedCustomer, selectedItem, selectedGroup, selectedBroker" class="spinner"></div>


   <!-- Filters Section -->
<div class="mb-2">
    <div class="card mb-1">
        <div class="card-body mb-1">
            <table class="table table-centered align-middle table-nowrap mb-0">
                <tbody>
                    <tr>
                        <td style="width:75px">
                            <select wire:model.live="itemsPerPage" id="itemsPerPage" class="form-control form-select">
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="150">150</option>
                                <option value="200">200</option>
                            </select>
                        </td>

                        <td  style="width:120px">
                            <input type="date" wire:model.live="startDate" id="startDate" class="form-control" placeholder="Start Date">
                        </td>

                        <td  style="width:120px">
                            <input type="date" wire:model.live="endDate" id="endDate" class="form-control" placeholder="End Date">
                        </td>

                        <td>
                            <select wire:model.live="selectedCustomer" id="selectedCustomer" class="form-control form-select">
                                <option value="">-- Select Customer(s) --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <select wire:model.live="selectedItem" id="selectedItem" class="form-control form-select">
                                <option value="">-- Select Item(s) --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <select wire:model.live="selectedGroup" id="selectedGroup" class="form-control form-select">
                                <option value="">-- Select Groups(s) --</option>
                                @foreach($filter_groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <select wire:model.live="selectedBroker" id="selectedBroker" class="form-control form-select">
                                <option value="">-- Select Broker(s) --</option>
                                @foreach($filter_brokers as $broker)
                                    <option value="{{ $broker->id }}">{{ $broker->name }}</option>
                                @endforeach
                            </select>
                        </td>



                        <td class="text-end">
                            <button type="button" wire:click="create()" class="btn btn-success btn-label waves-effect waves-light">
                                <i class="bx bx-alarm-add label-icon align-middle fs-16"></i> New
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="row mb-1 d-none">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>Bags</th>
                        <th>Gross Amount</th>
                        <th class="text-danger">Discount</th>
                        <th class="text-success">Sales Tax</th>
                        <th>Net Amount</th>
                        <th class="text-danger">Freight</th>
                        <th class="text-danger">Brokerage</th>
                    </tr>
                </thead>
                <tbody class="table-primary">
                    <tr>
                        <td class="fs-16"><strong>{{ number_format($totalBagsu, 2) }}</strong></td>
                        <td class="fs-16">{{ number_format($totalGrossAmountu, 2) }}</td>
                        <td class="fs-16">{{ number_format($totalDiscountu, 2) }}</td>
                        <td class="fs-16">{{ number_format($totalSalesTaxu, 2) }}</td>
                        <td class="fs-16">{{ number_format($totalNetAmountu, 2) }}</td>
                        <td class="fs-16">{{ number_format($totalFreightu, 2) }}</td>
                        <td class="fs-16">{{ number_format($totalBrokerageu, 2) }}</td>


                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


    <!-- Invoices Table -->
<div class="card">
    <div class="card-body">
    <div class="table-responsive">
    <table class="table table-centered align-middle table-nowrap mb-0">
        <thead class="table-light">
                <tr>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>Product(s)</th>
                    <th>Unit Price</th>
                    <th>Gross Amount</th>
                    <th class="text-danger">Discount</th>
                    <th class="text-success">Sales Tax</th>
                    <th>Net Amount</th>
                    <th>Due</th>
                    <th>Freight</th>
                    <th>Brokerage</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

                @php
                    $totalGrossAmount1 = 0;
                    $totalDiscount1 = 0;
                    $totalDiscountPerBag1 = 0;
                    $totalNetAmount1 = 0;
                    $totalFreight1 = 0;
                    $totalBags1 = 0;
                    $totalTax1 = 0;
                    $totalbrokery= 0;
                @endphp
                @foreach($invoices as $invoice)
                    @foreach($invoice->items as $index => $item)
                        @php
                            // Convert all values to floats to avoid string addition errors
                            $netAmount = floatval($item->net_amount ?? 0);
                            $discountAmount = floatval($item->discount_amount ?? 0);
                            $discountPerBagAmount = floatval($item->discount_per_bag_amount ?? 0);
                            $amountInclTax = floatval($item->amount_incl_tax ?? 0);
                            $vehicleFare = floatval($invoice->salesOrder->vehicle_fare ?? 0);
                            $quantity = intval($item->quantity ?? 0);
                            $salestax = floatval($item->sales_tax_amount ?? 0);
                            $brokery = floatval($invoice->broker_amount ?? 0);

                            // Accumulate totals
                            $totalGrossAmount1 += $netAmount;
                            $totalDiscount1 += $discountAmount;
                            $totalDiscountPerBag1 += $discountPerBagAmount;
                            $totalNetAmount1 += $amountInclTax;
                            //$totalFreight1 += $vehicleFare;
                            $totalBags1 += $quantity;
                            $totalTax1 += $salestax;
                            //$totalbrokery += $brokery;


                            if ($index === 0) {
                                        $totalFreight1 += floatval($invoice->salesOrder->vehicle_fare ?? 0);
                            }

                            if ($index === 0) {
                                        $totalbrokery += floatval($invoice->broker_amount ?? 0);
                            }

                        @endphp
                    <tr>
                        <!-- Invoice Information, only for the first row of each invoice -->
                         <!-- Invoice Information, only for the first row of each invoice -->
                            @if ($index === 0)
                                <td rowspan="{{ count($invoice->items) }}">
                                <span class="fw-medium link-muted">{{ $invoice->invoice_number }}</span><br/>
                                {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') }}</td>

                                <!-- Customer Details -->
                                <td rowspan="{{ count($invoice->items) }}">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-2">
                                            <img src="{{ \App\Models\CustomerDetail::where('account_id', $invoice->customer_id)->value('avatar')
                                                        ? Storage::disk('spaces')->url(\App\Models\CustomerDetail::where('account_id', $invoice->customer_id)->value('avatar'))
                                                        : asset('images/user-dummy-img.jpg') }}"
                                                        alt="Customer Avatar" class="avatar-sm rounded-circle">


                                                </div>
                                            <div>
                                            <h5 class="fs-14 my-1 fw-medium">
                                                <span class="text-success text-uppercase"> {{ \App\Models\ChartofAccount::find($invoice->customer->id)->name ?? 'N/A' }}</span>
                                            </h5>
                                            <span class="text-muted">{{ \App\Models\CustomerDetail::where('account_id', $invoice->customer_id)->with('coaGroupTitle')->first()->coaGroupTitle->name ?? 'N/A' }}</span>
                                        </div>

                                        <!--<span class="text-uppercase badge bg-success-subtle text-success fs-12"> {{ $invoice->customer->name ?? 'N/A' }}</span>-->


                                </td>
                            @endif

                        <!-- Product Information -->
                        <td><strong>{{ $products->find($item->product_id)->product_name ?? 'N/A' }}</strong><br/>
                            {{ $quantity }} <span style="font-size:10px"> Bags</span></td>
                        <td>{{ number_format(floatval($item->unit_price),2) }}</td>
                        <td>{{ number_format($netAmount,2) }}</td>
                        <!-- Discount Column -->
                            <td>
                                @if ($discountAmount == 0 && $item->discount_rate == 0)
                                    -
                                @else
                                    {{ number_format($discountAmount,2) }} <span style="font-size:10px"> <br/>@ {{ number_format(floatval($item->discount_rate),2) }}%</span><br/>
                                @endif
                                @if ($discountPerBagAmount == 0 && $item->discount_per_bag_rate == 0)

                                @else
                                    {{ number_format($discountPerBagAmount,2) }} <span style="font-size:10px"> <br/>@ {{ number_format(floatval($item->discount_per_bag_rate),2) }} p/b</span>
                                @endif
                            </td>


                         <!-- Sales Tax Column -->
                        <td>
                            @if ($salestax == 0 && $item->sales_tax_rate == 0)
                                -
                            @else
                                {{ number_format($salestax,2) }}<br/><span style="font-size:10px">{{ number_format(floatval($item->sales_tax_rate),2) }} p/bag</span>
                            @endif
                        </td>
                        <td>{{ number_format($amountInclTax,2) }}</td>

                        <td>{{ $invoice->invoice_due_days }} <small> days</small></td>

                        <!-- Freight Column -->
                        <td>
                            @if ($index === 0)
                                @if ($vehicleFare == 0)
                                    -
                                @else
                                    {{ number_format($vehicleFare) }}
                                @endif

                            @else
                                -
                            @endif
                        </td>

                        <!-- Brokerage Column -->
                        <td>
                        @if ($index === 0)
                            @if ($brokery == 0)
                                -
                            @else
                                {{ number_format($brokery) }}
                            @endif
                        @else
                                -
                        @endif
                        </td>

                        <!-- Status and Actions, only for the first row of each invoice -->
                        @if ($index === 0)
                            <td rowspan="{{ count($invoice->items) }}">
                                @if($invoice->status =='draft')
                                <span class="badge bg-danger">Not Posted</span>
                                @else
                                <span class="badge bg-success">Posted</span>
                                @endif

                                @if($invoice->is_weighbridge ==1)
                                    <br/><i class="ri-scales-line fs-16 text-warning" title="dispatched from weighbridge"></i>
                                @endif

                            </td>
                            <td rowspan="{{ count($invoice->items) }}">

                            <div class="hstack gap-2 flex-wrap">
                            @can('sales invocies edit')
                                <a wire:click="edit({{ $invoice->id }})" href="javascript:void(0);" class="link-success fs-15" title="Edit Invoice">
                                        <i class="ri-edit-2-line" style="font-size:16px;"></i></a>
                            @endcan

                                    <!--<a onclick="confirmDeletionOrder{{ $invoice->id }}($invoice->id)" href="javascript:void(0);" class="link-danger fs-15" title="Delete Order">
                                        <i class="ri-delete-bin-line" style="font-size:16px;"></i></a>-->

                                <form action="{{ route('invoices.print', ['invoiceId' => $invoice->id]) }}" method="POST"  style="display:inline;">
                                                @csrf
                                                <button type="submit" class="link-dark" title="Print Delivery Challan" style="border: none;background-color: white;">
                                                    <i class="ri-printer-line" style="font-size:16px;"></i>
                                                </button>
                                </form>

                                <div class="dropdown">
                                    <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-more-2-fill text-muted"></i>
                                    </a>

                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                                        <li>
                                            <a class="dropdown-item" href="#">Created: {{ $invoice->createdBy->name }}, {{ $invoice->created_at->format('d-m-Y h:i A') }}</a>
                                        </li>

                                        @if($invoice->updatedBy)
                                        <li>
                                            <a class="dropdown-item" href="#">Updated: {{ $invoice->updatedBy->name }}, {{ $invoice->updated_at->format('d-m-Y h:i A') }}</a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>


                        </div>
                            </td>
                        @endif
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
                <tfoot class="table-info">
                    <tr>
                        <th colspan="2" class="text-center">Total:</th>
                        <th>{{ number_format($totalBags1) }} <small> Bags</small></th> <!-- Total Bags -->
                        <th></th>
                        <th>{{ number_format($totalGrossAmount1,2) }}</th>
                        <th>{{ number_format($totalDiscount1 + $totalDiscountPerBag1,2) }}</th>
                        <th>{{ number_format($totalTax1,2) }}</th>
                        <th>{{ number_format($totalNetAmount1,2) }}</th>
                        <th></th>
                        <th>{{ number_format($totalFreight1) }}</th>
                        <th>{{ number_format($totalbrokery,2) }}</th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>
        </table>
    </div>
        <!-- Pagination -->
        <div class="d-flex justify-content-between card-body mb-0 pb-0">
                    <div>
                        Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} results
                    </div>
                    <div>
                        {{ $invoices->links() }}
                    </div>
                </div>
    </div>
</div>



<div>
    <!-- Invoice Form -->
    <div wire:ignore.self class="modal fade" id="modal_invoice" tabindex="-1" aria-labelledby="modal_invoiceLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header card-header align-items-cd-flex pb-2">
                    <h4 class="card-title mb-0 flex-grow-1" id="myModal_orderLabel">
                        {{ $isEditMode ? 'Edit Sale Invoice' : 'Add New Sale Invoice' }}
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                @if (session()->has('formerrors'))
                    <div class="alert alert-danger alert-dismissible fade show material-shadow" role="alert">
                        <i class="ri-notification-off-line label-icon"></i> {{ session('formerrors') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="form-section">
                    <!-- Invoice Details -->
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Invoice Date</label>
                            <input type="date" wire:model="invoice_date" class="form-control shadow-sm">
                            @error('invoice_date') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Invoice Number</label>
                            <input type="text" wire:model="invoice_number" class="form-control shadow-sm" placeholder="Invoice Number" readonly />
                            @error('invoice_number') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-7 mb-3">
                            <label class="form-label">Select Customer</label>
                            <select wire:model.live="customer_id" class="form-control form-select shadow-sm">
                                <option value="">-- Select Customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-1 mb-3">
                            <label class="form-label">Due Days</label>
                            <input type="number" wire:model="invoice_due_days" class="form-control shadow-sm" placeholder="Due Days" />
                            @error('invoice_due_days') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                    </div>
				</div>



					<div class="form-section">

                    <!-- Product Details Table with Merged Columns -->
                    <h5 class="tabheading">Add / Remove Prodcuts</h5>
                     <table class="table table-bordered table-striped">
							<thead class="table-light">
								<tr>
									<th style="width: 20%;">Product</th>
									<th>Quantity</th>
									<th>Unit Price</th>
                                    <th>Gross Amount</th>
									<th class="text-danger">Discount <small>(%)</small></th>
									<th class="text-danger">Discount <small>(p/b)</small></th>
                                    <th class="text-success">Sales Tax <small>(p/b)</small></th>
                                    <th>Net Amount</th>
									<th>Action</th>
								</tr>
							</thead>
								<tbody id="invoice-items" >
									@foreach($items as $index => $item)
									<tr wire:key="item-{{ $index }}-{{ $item['product_id'] }}">
										<td style="width:30%;">
											<select wire:model="items.{{ $index }}.product_id" class="form-control form-select table-input">
												<option value="">Select Product</option>
												@foreach($products as $product)
												<option value="{{ $product->id }}">{{ $product->product_name }}</option>
												@endforeach
											</select>
											@error("items.$index.product_id") <span class="text-danger">{{ $message }}</span> @enderror
										</td>

										<td>
											<input type="number" wire:model="items.{{ $index }}.quantity" class="form-control table-input"  wire:input.debounce.300ms="calculateAmounts({{ $index }})">
											@error("items.$index.quantity") <span class="text-danger">{{ $message }}</span> @enderror
										</td>

										<td>
											<input type="number" wire:model="items.{{ $index }}.unit_price" step="0.01" class="form-control table-input"  wire:input.debounce.300ms="calculateAmounts({{ $index }})">
											@error("items.$index.unit_price") <span class="text-danger">{{ $message }}</span> @enderror
										</td>

										<td>
                                        <input type="number" wire:model="items.{{ $index }}.net_amount" step="0.01" class="form-control table-input" wire:input.debounce.300ms="calculateAmounts({{ $index }})"
											 readonly>
                                             @error("items.$index.net_amount") <span class="text-danger">{{ $message }}</span> @enderror

										</td>

                                        <!-- Merged Discount Column -->
										<td>
											<div class="d-flex">
												<input type="number" wire:model.defer="items.{{ $index }}.discount_rate" step="0.01" class="form-control table-input-small me-1" placeholder="Rate (%)" wire:input.debounce.300ms="calculateAmounts({{ $index }})"  min="0">
												<input type="number" wire:model="items.{{ $index }}.discount_amount" step="0.01" class="form-control table-input"
                                                min="0" readonly>
											</div>
                                            @error("items.$index.discount_rate") <span class="text-danger">{{ $message }}</span> @enderror
                                            @error("items.$index.discount_amount") <span class="text-danger">{{ $message }}</span> @enderror

										</td>



                                    <td>
										<div class="d-flex">
											<input type="number" wire:model.defer="items.{{ $index }}.discount_per_bag_rate" step="0.01" class="form-control table-input-small me-1" placeholder="Rate (%)" wire:input.debounce.300ms="calculateAmounts({{ $index }})"  min="0">
											<input type="number" wire:model="items.{{ $index }}.discount_per_bag_amount" step="0.01" class="form-control table-input"  min="0" readonly>
                                            </div>
                                            @error("items.$index.discount_per_bag_rate") <span class="text-danger">{{ $message }}</span> @enderror
                                            @error("items.$index.discount_per_bag_amount") <span class="text-danger">{{ $message }}</span> @enderror

									</td>


									<td>
										<div class="d-flex">
											<input type="number" wire:model.defer="items.{{ $index }}.sales_tax_rate" step="0.01" class="form-control table-input-small me-1" placeholder="Rate (%)" wire:input.debounce.300ms="calculateAmounts({{ $index }})"  min="0">
											<input type="number" wire:model="items.{{ $index }}.sales_tax_amount" step="0.01" class="form-control table-input"  min="0" readonly>
                                            </div>
                                            @error("items.$index.sales_tax_rate") <span class="text-danger">{{ $message }}</span> @enderror
                                            @error("items.$index.sales_tax_amount") <span class="text-danger">{{ $message }}</span> @enderror

									</td>

										<td>
											<input type="number" wire:model="items.{{ $index }}.amount_incl_tax" step="0.01" class="form-control table-input" min="0" readonly>
                                            @error("items.$index.amount_incl_tax") <span class="text-danger">{{ $message }}</span> @enderror
										</td>

										<td style="text-align:center;">
												@if ($index === 0)
													<i class="ri-add-circle-line text-success" style="cursor:pointer;font-size:20px" wire:click="addItemRow()" title="Add New Row"></i>
												@else
													<i class="ri-delete-bin-5-line text-danger" style="cursor:pointer;font-size:20px" wire:click="removeItemRow({{ $index }})" title="Delete Row"></i>
												@endif
											<!--<i class="ri-delete-bin-line text-danger" style="font-size:16px;" wire:click="removeItemRow({{ $index }})"></i>-->
										</td>
									</tr>
									@endforeach

									<!-- Show at least one empty row for adding an item -->
									@if(count($items) == 0)
									<tr>
										<td colspan="9" class="text-center">No items added. Please add an item.</td>
									</tr>
									@endif
								</tbody>
								<tfoot>
									<tr>
										<th class="text-end">Totals:</th>
										<th>
											<span class="total_lable"> {{ number_format($totalQuantity) }} </span>
										</th>

                                        <th></th>

                                        <th class="text-center">
										    <span class="total_lable">  {{ number_format($totalNetAmount,2) }}</span>
										</th>

										<th class="text-center">
										    <span class="total_lable"> {{ number_format($totalDiscount,2) }}</span>
										</th>

										<th class="text-center">
										    <span class="total_lable"> {{ number_format($totalDiscountPerBag,2) }}</span>
										</th>

                                        <th class="text-center">
                                            <span class="total_lable"> {{ number_format($totalSalesTax,2) }}</span>
										</th>
										<th>
                                            <span class="total_lable">  {{ number_format($totalInclTax,2) }} </span>
                                        </th>
									</tr>
								</tfoot>
							</table>
						</div>

                        <div class="form-section">
                        <!-- Delivery Challan Section -->
					<div class="row">
                    <h5 class="section-heading tabheading" ><i class="ri-truck-line"></i>
                    Delivery Challan &  Brokerage Details</h5>
                        <div class="col-2">
                            <label for="farm_name">Farm Name</label>
                            <input type="text" wire:model="farm_name" id="farm_name" class="form-control table-input" placeholder="">
                            @error('farm_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-4">
                            <label for="farm_address">Farm Address</label>
                            <input type="text" wire:model="farm_address" id="farm_address" class="form-control table-input" placeholder="">
                            @error('farm_address') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-2">
                            <label for="farm_supervisor_mobile">Supervisor Mobile</label>
                            <input type="text" wire:model="farm_supervisor_mobile" id="farm_supervisor_mobile" class="form-control table-input" placeholder="">
                            @error('farm_supervisor_mobile') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-1">
                            <label for="vehicle_no">Vehicle No.</label>
                            <input type="text" wire:model.live="vehicle_no" id="vehicle_no" class="form-control table-input" placeholder="">
                            @error('vehicle_no') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-1">
                            <label for="vehicle_fare">Freight</label>
                            <input type="number" wire:model.live="vehicle_fare" id="vehicle_fare" class="form-control table-input" placeholder="0">
                            @error('vehicle_fare') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-2 mb-3">
                            <label for="vehicle_fare_adj">Freight Credit</label>
                                <select wire:model.live="vehicle_fare_adj" class="form-control form-select table-input">
									<option value="0">---Select---</option>
									<option value="1">Customer</option>
                                    <option value="2">Freight-Out Payable</option>
                                </select>
                        </div>


                                <div class="col-6">
                                    <label for="broker_id">Select Broker</label>
                                    <select wire:model="broker_id" class="form-control form-select shadow-sm">
                                        <option value="">-- Select Broker --</option>
                                        @foreach($brokers as $broker)
                                            <option value="{{ $broker->id }}">{{ $broker->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('broker_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-2">
                                    <label for="broker_rate">Brokerage Rate (%)</label>
                                    <input type="number" wire:model="broker_rate" class="form-control table-input" placeholder="Brokerage Rate">
                                    @error('broker_rate') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-2">
                                    <label for="broker_amount">Brokerage Amount</label>
                                    <input type="number" wire:model="broker_amount" class="form-control table-input" placeholder="Brokerage Amount" >
                                    @error('broker_amount') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                    </div>

                 </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Close</button>
                    <button type="button" wire:click="store()" class="btn btn-primary shadow-sm">{{ $isEditMode ? 'Update' : 'Save' }}</button>
                </div>
            </div>
        </div>
    </div>
    </div>
</div><!--Model -->



</div>


<script>
      window.addEventListener('showModal_invoice', event => {
        var myModal_invoice = new bootstrap.Modal(document.getElementById('modal_invoice'));
        myModal_invoice.show();
    });

    window.addEventListener('hideModal_invoice', event => {
        var myModal_invoice = bootstrap.Modal.getInstance(document.getElementById('modal_invoice'));
        if (myModal_invoice) {
            myModal_invoice.hide();
        }
    });



</script>
