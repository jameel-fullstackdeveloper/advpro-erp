<div>
    <!-- Custom Styles -->
    <style>
    .form-control, .form-select {
        border-radius: 8px;
        padding: 12px;
        transition: box-shadow 0.2s;
    }

    .form-control:focus, .form-select:focus {
        border-color: #2a9d8f;
        box-shadow: 0px 0px 8px rgba(42, 157, 143, 0.2);
    }

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

    h5 {
        font-weight: 600;
        color: #2a9d8f;
        border-bottom: 2px solid #2a9d8f;
        padding-bottom: 5px;
        margin-bottom: 15px;
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

    /* Small button padding */
    .btn {
        padding: 6px 12px;
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

</style>
    <!-- Filters Section -->
    <div class="mb-3">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-1">
                        <select wire:model.live="itemsPerPage" id="itemsPerPage" class="form-control form-select" style="width:80px;">
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="150">150</option>
                            <option value="200">200</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="date" wire:model.live="startDate" id="startDate" class="form-control" placeholder="Start Date">
                    </div>

                    <div class="col-md-2">
                        <input type="date" wire:model.live="endDate" id="endDate" class="form-control" placeholder="End Date">
                    </div>

                    <div class="col-md-4">
                        <select wire:model.live="selectedCustomer" id="selectedCustomer" class="form-control form-select">
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="text" class="form-control" id="searchTerm" placeholder="Search..." wire:model.live="searchTerm">
                    </div>

                    <div class="col-md-1 text-end">
                        <button type="button" wire:click="create()" class="btn btn-success btn-label waves-effect waves-light">
                         <i class="bx bx-alarm-add label-icon align-middle fs-16"></i> New</button>
                    </div>

                </div>
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
                    <th>Product</th>
                    <th>Unit Price</th>
                    <th>Net Amount</th>
                    <th>Discount</th>
                    <th>Excl. Tax</th>
                    <th>Sales Tax</th>
                    <th>Further Tax</th>
                    <th>Incl. Tax</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                    @foreach($invoice->items as $index => $item)
                    <tr>
                        <!-- Invoice Information, only for the first row of each invoice -->
                         <!-- Invoice Information, only for the first row of each invoice -->
                            @if ($index === 0)
                                <td rowspan="{{ count($invoice->items) }}">{{ $invoice->invoice_number }}<br/>
                                {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') }}</td>

                                <!-- Customer Details -->
                                <td rowspan="{{ count($invoice->items) }}">
                                        <span class="text-uppercase badge bg-success-subtle text-success fs-12"> {{ $invoice->customer->name ?? 'N/A' }}</span>


                                </td>
                            @endif

                        <!-- Product Information -->
                        <td><strong>{{ $products->find($item->product_id)->product_name ?? 'N/A' }}</strong><br/>
                        {{ $item->quantity }} <span style="font-size:10px"> Bags</span></td>
                        <td>{{ number_format($item->unit_price) }}</td>
                        <td>{{ number_format($item->net_amount) }}</td>
                        <td>{{ number_format($item->discount_amount) }} <br/><span style="font-size:10px">{{ number_format($item->discount_rate, 2) }} %</span>

                            </td>
                        <td>{{ number_format($item->amount_excl_tax) }}</td>
                        <td>{{ number_format($item->sales_tax_amount) }}<br/><span style="font-size:10px">{{ number_format($item->sales_tax_rate, 2) }} %</span>


                        </td>
                        <td>{{ number_format($item->further_sales_tax_amount) }}<br/>
                        <span style="font-size:10px"> {{ number_format($item->further_sales_tax_rate, 2) }}%</span>
                            </td>
                        <td>{{ number_format($item->amount_incl_tax) }}</td>

                        <!-- Status and Actions, only for the first row of each invoice -->
                        @if ($index === 0)
                            <td rowspan="{{ count($invoice->items) }}">
                                @if($invoice->status =='draft')
                                <span class="badge bg-danger">Not Posted</span>
                                @else
                                <span class="badge bg-success">Posted</span>
                                @endif

                            </td>
                            <td rowspan="{{ count($invoice->items) }}">

                            <div class="hstack gap-2 flex-wrap">
                                    <a wire:click="edit({{ $invoice->id }})" href="javascript:void(0);" class="link-success fs-15" title="Edit Invoice">
                                        <i class="ri-edit-2-line" style="font-size:16px;"></i></a>

                                    <!--<a onclick="confirmDeletionOrder{{ $invoice->id }}($invoice->id)" href="javascript:void(0);" class="link-danger fs-15" title="Delete Order">
                                        <i class="ri-delete-bin-line" style="font-size:16px;"></i></a>-->

                                <form action="{{ route('invoices.print', ['invoiceId' => $invoice->id]) }}" method="POST"  style="display:inline;">
                                                @csrf
                                                <button type="submit" class="link-dark" title="Print Delivery Challan" style="border: none;background-color: white;">
                                                    <i class="ri-printer-line" style="font-size:16px;"></i>
                                                </button>
                                </form>

                        </div>
                            </td>
                        @endif
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
        <!-- Pagination -->
        <div class="d-flex justify-content-between">
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
                <div class="modal-header card-header align-items-center d-flex pb-2">
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
                            <input type="text" wire:model="invoice_number" class="form-control shadow-sm" placeholder="Invoice Number">
                            @error('invoice_number') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label">Select Customer</label>
                            <select wire:model.live="customer_id" class="form-control form-select shadow-sm">
                                <option value="">-- Select Customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
				</div>



					<div class="form-section">

                    <!-- Product Details Table with Merged Columns -->
                    <h5 class="mt-2">Add / Remove Prodcuts</h5>
                    <table class="table table-bordered table-striped">
							<thead class="table-light">
								<tr>
									<th style="width: 20%;">Product</th>
									<th>Quantity</th>
									<th>Unit Price</th>
									<th>Discount</th>
									<th>Amount Excl. Tax</th>
									<th>Sales Tax</th>
									<th>Further Tax</th>
									<th>Amount Incl. Tax</th>
									<th>Action</th>
								</tr>
							</thead>
								<tbody>
									@foreach($items as $index => $item)
									<tr>
										<td>
											<select wire:model="items.{{ $index }}.product_id" class="form-control form-select table-input">
												<option value="">Select Product</option>
												@foreach($products as $product)
												<option value="{{ $product->id }}">{{ $product->product_name }}</option>
												@endforeach
											</select>
											@error("items.$index.product_id") <span class="text-danger">{{ $message }}</span> @enderror
										</td>

										<td>
											<input type="number" wire:model="items.{{ $index }}.quantity" class="form-control table-input"  wire:input="calculateAmounts({{ $index }})">
											@error("items.$index.quantity") <span class="text-danger">{{ $message }}</span> @enderror
										</td>

										<td>
											<input type="number" wire:model="items.{{ $index }}.unit_price" step="0.01" class="form-control table-input"  wire:input="calculateAmounts({{ $index }})">
											@error("items.$index.unit_price") <span class="text-danger">{{ $message }}</span> @enderror
										</td>

										<!-- Merged Discount Column -->
										<td>
											<div class="d-flex">
												<input type="number" wire:model="items.{{ $index }}.discount_rate" step="0.01" class="form-control table-input-small me-1" placeholder="Rate (%)" wire:input="calculateAmounts({{ $index }})"  min="0">
												<input type="number" wire:model="items.{{ $index }}.discount_amount" step="0.01" class="form-control table-input"
                                                min="0" readonly>
											</div>
                                            @error("items.$index.discount_rate") <span class="text-danger">{{ $message }}</span> @enderror
                                            @error("items.$index.discount_amount") <span class="text-danger">{{ $message }}</span> @enderror

										</td>

										<td>
											<input type="number" wire:model="items.{{ $index }}.amount_excl_tax" step="0.01" class="form-control table-input"
											 readonly>
                                             @error("items.$index.amount_excl_tax") <span class="text-danger">{{ $message }}</span> @enderror
                                        </td>

										<td>
										<div class="d-flex">
											<input type="number" wire:model="items.{{ $index }}.sales_tax_rate" step="0.01" class="form-control table-input-small me-1" placeholder="Rate (%)" wire:input="calculateAmounts({{ $index }})" min="0">
											<input type="number" wire:model="items.{{ $index }}.sales_tax_amount" step="0.01"  class="form-control table-input"  min="0" readonly>
                                        </div>
                                        @error("items.$index.sales_tax_rate") <span class="text-danger">{{ $message }}</span> @enderror
                                        @error("items.$index.sales_tax_amount") <span class="text-danger">{{ $message }}</span> @enderror

									</td>

									<td>
										<div class="d-flex">
											<input type="number" wire:model="items.{{ $index }}.further_sales_tax_rate" step="0.01" class="form-control table-input-small me-1" placeholder="Rate (%)" wire:input="calculateAmounts({{ $index }})"  min="0">
											<input type="number" wire:model="items.{{ $index }}.further_sales_tax_amount" step="0.01" class="form-control table-input"  min="0" readonly>
                                            </div>
                                            @error("items.$index.further_sales_tax_rate") <span class="text-danger">{{ $message }}</span> @enderror
                                            @error("items.$index.further_sales_tax_amount") <span class="text-danger">{{ $message }}</span> @enderror

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
										<th>
										<span class="total_lable"> {{ number_format($totalDiscount) }}</span>
										</th>
										<th>
										<span class="total_lable">  {{ number_format($totalExclTax) }}</span>
										</th>
										<th>
										<span class="total_lable"> {{ number_format($totalSalesTax) }}</span>
										</th>
										<th>
										<span class="total_lable">  {{ number_format($totalFurtherTax) }}</span>
										</th>
										<th>
										<span class="total_lable">  {{ number_format($totalInclTax) }} </span>
										</th>
										<th></th>
									</tr>
								</tfoot>
							</table>
						</div>

                        <div class="form-section">
                        <!-- Delivery Challan Section -->
					<div class="row">
                    <h5 class="section-heading mt-2" ><i class="ri-truck-line"></i> Delivery Challan Details</h5>
                        <div class="col-2">
                            <label for="farm_name">Farm Name</label>
                            <input type="text" wire:model="farm_name" id="farm_name" class="form-control" placeholder="Enter Farm Name">
                            @error('farm_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-4">
                            <label for="farm_address">Farm Address</label>
                            <input type="text" wire:model="farm_address" id="farm_address" class="form-control" placeholder="Enter Farm Address">
                            @error('farm_address') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-2">
                            <label for="farm_supervisor_mobile">Supervisor Mobile</label>
                            <input type="text" wire:model="farm_supervisor_mobile" id="farm_supervisor_mobile" class="form-control" placeholder="Enter Mobile #">
                            @error('farm_supervisor_mobile') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-2">
                            <label for="vehicle_no">Vehicle No.</label>
                            <input type="text" wire:model.live="vehicle_no" id="vehicle_no" class="form-control" placeholder="Enter Vehicle No.">
                            @error('vehicle_no') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-2">
                            <label for="vehicle_fare">Vehicle Fare</label>
                            <input type="number" wire:model.live="vehicle_fare" id="vehicle_fare" class="form-control" placeholder="Enter Vehicle Fare">
                            @error('vehicle_fare') <span class="text-danger">{{ $message }}</span> @enderror
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
