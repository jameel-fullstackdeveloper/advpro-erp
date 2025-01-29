<div>
<style>
/* Modern styles for the modal headings */
 .section-heading {
    font-size: 14px;
    font-weight: 600;
    color: #2a9d8f; /* Add a subtle green or your theme color */
    border-bottom: 2px solid #2a9d8f;
    padding-bottom: 5px;
    margin-bottom: 15px;
}

/* Style for input fields */
.form-control {
    border-radius: 8px;
    padding: 10px;
    border: 1px solid #ccc;
    transition: border-color 0.2s;
}

.form-control:focus {
    border-color: #2a9d8f;
    box-shadow: 0px 0px 8px rgba(42, 157, 143, 0.2);
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

/* Improve table styling */
table.table {
    border-collapse: separate;
    border-spacing: 0 12px;
}

table.table th, table.table td {
    padding: 12px 15px;
    vertical-align: middle;
}

/* Make modal sections stand out */
.modal-body .form-section {
    padding: 15px;
    background-color: #f9f9f9;
    border-radius: 8px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
}

/* Add space between rows and icons */
.table td .form-control, .table td i {
    margin-top: 4px;
    margin-bottom: 4px;
}

/* Center buttons more neatly in table */
.hstack {
    align-items: center;
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
</style>


 <!-- Filters -->
<div class="mb-2">
    <div class="card">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-1">
                    <select wire:model.live="itemsPerPage" class="form-control form-select" style="width:80px;">
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="150">150</option>
                        <option value="200">200</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" wire:model.live="startDate" class="form-control" value="{{ $startDate }}">
                    @error('startDate') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-2">
                    <input type="date" wire:model.live="endDate" class="form-control" value="{{ $endDate }}">
                    @error('endDate') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-2">
                    <select wire:model.live="status" class="form-control form-select">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="delivered">Delivered</option>
                        <option value="invoiced">Invoiced</option>
                        <option value="canceled">Canceled</option>
                    </select>
                </div>
                <!--<div class="col-md-1">
                    <button class="btn btn-primary" wire:click="filter">
                        <i class="ri-filter-line" style="font-size:13px;"></i></button>
                </div>-->
                <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="Search by order # or customer..." wire:model.live="searchTerm">
                </div>
                <div class="col-md-2 text-end">
                <button type="button" wire:click="create()" class="btn btn-success btn-label waves-effect waves-light"><i class="bx bx-alarm-add label-icon align-middle fs-16"></i> New</button>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show material-shadow">{{ session('message') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
            <div class="table-responsive">
    <table class="table table-centered align-middle table-nowrap mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Order Details</th>
                            <th>Customer Details</th>
                            <th>Farm Details</th>
                            <th>Products Details</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($salesOrders as $order)
                        <tr>

                                <td>
                                <span class="fw-medium link-muted">{{ $order->order_number }}</span> <br/>
                                {{ \Carbon\Carbon::parse($order->order_date)->format('d-m-Y') }}
                            </td>
                                <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-2">
                                                <img src="{{ \App\Models\CustomerDetail::where('account_id', $order->customer_id)->value('avatar')
                                                        ? Storage::disk('spaces')->url(\App\Models\CustomerDetail::where('account_id', $order->customer_id)->value('avatar'))
                                                        : asset('images/user-dummy-img.jpg') }}"
                                                        alt="Customer Avatar" class="avatar-sm rounded-circle">

                                                </div>
                                            <div>
                                            <h5 class="fs-14 my-1 fw-medium">

                                                    <span class="text-success text-uppercase" title=" {{ \App\Models\ChartofAccount::find($order->customer_id)->name ?? 'N/A' }}">
                                                    {{
                                                        Str::words(\App\Models\ChartofAccount::find($order->customer_id)->name ?? 'N/A', 3, ' ...')
                                                    }}

                                                    </span>

                                                    </h5>

                                                     <!-- Ensure this is showing for both debtor and creditor -->
                                                @php
                                                    $customer = \App\Models\ChartOfAccount::find($order->customer_id);
                                                    $groupName = $customer->group_id ? \App\Models\ChartOfAccountGroup::find($customer->group_id)->name : 'N/A';
                                                @endphp
                                                <span class="text-muted">{{ $groupName }}</span>

                                            <span class="text-muted d-none">
                                                {{ \App\Models\CustomerDetail::where('account_id', $order->customer_id)->with('coaGroupTitle')->first()->coaGroupTitle->name ?? 'N/A' }}</span>
                                        </div>
                                    </div>



                                </td>
                                <td>{{ $order->farm_name }} <br/>
                                {{ $order->farm_address }}</td>
                                <td>
                                    <!-- Display Products and Quantities for this Order -->
                                    @foreach ($order->items as $item)
                                        <div>
                                        <strong title="{{ $item->product->name ?? 'N/A' }}">
                                            {{ Str::words($item->product->name ?? 'N/A', 4, ' ...') }}
                                        </strong>
                                        <br/> {{ $item->quantity }} <small>Bags</small></div>
                                    @endforeach
                                </td>
                                <td>

                                @if($order->created==1)
                                    <span class="badge bg-primary-subtle text-primary">
                                                Created by Sales Module</span>
                                @else
                                @if($order->status=='pending')
                                    <span class="badge bg-warning">
                                        {{ ucfirst($order->status) }}</span>
                                    @endif

                                    @if($order->status=='confirmed')
                                    <span class="badge bg-success">
                                        {{ ucfirst($order->status) }}</span>
                                    @endif

                                    @if($order->status=='delivered')
                                        <span class="badge bg-info">
                                            {{ ucfirst($order->status) }} <small>{{ \Carbon\Carbon::parse($order->weighbridge_outward_data->second_weight_datetime)->format('d-m-Y, h:i A') }} </small></span>

                                            @php
                                               /* @if($order->weighbridge_outward_data)
                                                    <div><strong>Delivered On:</strong> </div>
                                                    <div><strong>Truck Number:</strong> {{ $order->weighbridge_outward_data->truck_number }}</div>
                                                    <div><strong>Delivered By:</strong> {{ $order->weighbridge_outward_data->name }}</div>
                                                @else
                                                    <div>No delivery data available</div>
                                                @endif */

                                            @endphp
                                    @endif

                                    @if($order->status=='canceled')
                                    <span class="badge bg-danger">
                                        {{ ucfirst($order->status) }}</span>
                                    @endif


                                @endif






                                    </td>

                                <td>
                                <div class="hstack gap-3 flex-wrap">
                                @if($order->created==1 || $order->status=='delivered')


                                    @else

                                    @can('sales orders edit')
                                    <a wire:click="edit({{ $order->id }})" href="javascript:void(0);" class="link-success fs-15" title="Edit Order">
                                        <i class="ri-edit-2-line" style="font-size:16px;"></i></a>
                                    @endcan

                                            @if($order->status != 'confirmed') {{-- Hide the delete option if the status is confirmed --}}
                                                @can('sales orders delete')
                                                    <a onclick="confirmDeletionOrder{{ $order->id }}({{ $order->id }})" href="javascript:void(0);" class="link-danger fs-15" title="Delete Order">
                                                        <i class="ri-delete-bin-line" style="font-size:16px;"></i>
                                                    </a>
                                                @endcan
                                            @endif
                                @endif

                                         @if($order->status == 'confirmed' || $order->status=='delivered')
                                            <form action="{{ route('print.challan', ['orderId' => $order->id]) }}" method="POST"  style="display:inline;">
                                                @csrf
                                                <button type="submit" class="link-dark" title="Print Delivery Challan" style="border: none;background-color: white;">
                                                    <i class="ri-printer-line" style="font-size:16px;"></i>
                                                </button>
                                            </form>
                                        @endif



                                            <div class="dropdown">
                                                                <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="true" class="show">
                                                                    <i class="ri-more-2-fill text-muted"></i>
                                                                </a>

                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1" data-popper-placement="bottom-start" data-popper-escaped="" style="position: absolute; inset: 0px auto auto 0px; margin: 0px; transform: translate3d(-134.4px, 20.8px, 0px);">

                                                                    <li><a class="dropdown-item" href="#">Created: {{ $order->userCreated->name }}, {{ $order->created_at->format('d-m-Y h: i A') }}</a></li>

                                                                    @if($order->userUpdated != NULL)
                                                                    <li><a class="dropdown-item" href="#">Updated: {{ $order->userUpdated->name }}, {{ $order->updated_at->format('d-m-Y h: i A') }}</a></li>
                                                                    @endif
                                                                </ul>
                                            </div>
                        </div>
                         @can('sales orders delete')
                                        <script>
                                                    function confirmDeletionOrder{{ $order->id }}(accountId) {
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
                                                            @this.confirmDeletion(accountId);
                                                            }
                                                        });
                                                    }
                                        </script>
                            @endcan

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                                                </div>

                <div class="d-flex justify-content-between">
                    <div>
                        Showing {{ $salesOrders->firstItem() }} to {{ $salesOrders->lastItem() }} of {{ $salesOrders->total() }} results
                    </div>
                    <div>
                        {{ $salesOrders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




<div wire:ignore.self class="modal flip" id="myModal_order" tabindex="-1" aria-labelledby="myModal_orderLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header card-header align-items-center d-flex pb-2">
                <h5 class="card-title mb-0 flex-grow-1" id="myModal_orderLabel">
                    {{ $isEditMode ? 'Edit Sales Order' : 'Add New Sales Order' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body" style="padding-bottom:0px;">
                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show material-shadow" role="alert">
                        <i class="ri-notification-off-line label-icon"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Unified Form Section -->
                <div class="form-section">
                    <h5 class="section-heading"><i class="bx bx-receipt"></i> Sales Order Details</h5>
                    <!-- Order Number and Date -->
                    <div class="row">
                        <div class="col-2">
                            <div class="mb-3">
                                <label for="order_number">Order Number</label>
                                <input type="text" wire:model="order_number" class="form-control" readonly>
                                @error('order_number') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-2">
                            <div class="mb-3">
                                <label for="order_date">Order Date</label>
                                <input type="date" wire:model.live="order_date" class="form-control" value="{{ $order_date }}">
                                @error('order_date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-6">
                        <div class="mb-3">
                            <label for="customer_id">Select Customer</label>
                            <select wire:model="customer_id" class="form-select">
                                <option value="">---Select Customer---</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}  [ {{ $customer->customerGroup ? $customer->customerGroup->name : 'No Group' }} ]</option>
                                    @endforeach
                            </select>
                            @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                </div>

                        <div class="col-2">
                            <div class="mb-3">
                                <label for="order_status">Order Status</label>
                                <select wire:model="order_status" class="form-select">
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="canceled">Canceled</option>
                                </select>
                                @error('order_status') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>



                   <!-- Product Selection Section with Reduced Space Between Label and Table -->
<div class="row">

    <div class="col-12">
         <div class="mb-2">
            <table class="table table-sm" style="padding:0px;margin:0px;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 20%;padding:5px;">Product</th>
                        <th style="padding:5px;">Quantity</th>
                        <th style="padding:5px;" class="text-center">Action</th>

                    </tr>
                </thead>
            <tbody>
                @foreach ($products as $index => $productRow)
                    <tr>
                        <td style="width:70%;padding:5px;">
                            <select wire:model="products.{{ $index }}.product_id" class="form-select table-input" style="margin-top: 0;">
                                <option value="">---Select Product---</option>
                                @foreach ($allProducts as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                            @error("products.$index.product_id") <span class="text-danger">{{ $message }}</span> @enderror
                        </td>
                        <td style="width:10%;padding:5px;">
                            <input type="number" min="0" wire:model="products.{{ $index }}.quantity" class="form-control text-center table-input" placeholder="Quantity" style="margin-top: 0;">
                            @error("products.$index.quantity") <span class="text-danger">{{ $message }}</span> @enderror
                        </td>
                        <td class="text-center" style="width:20%;padding:5px;">
                            @if ($index === 0)
                                <i class="ri-add-circle-line text-success" style="cursor:pointer;font-size:20px" wire:click="addProductRow()" title="Add New Row"></i>
                            @else
                                <i class="ri-delete-bin-5-line text-danger" style="cursor:pointer;font-size:20px" wire:click="removeProductRow({{ $index }})" title="Delete Row"></i>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div></div>
</div>

</div>
</div>
<div class="form-section">
                    <!-- Delivery Challan Section -->
                    <h5 class="section-heading mt-0" ><i class="ri-truck-line"></i> Delivery Challan Details</h5>
                    <div class="row mb-2">
                        <div class="col-4">
                            <label for="farm_name">Farm Name</label>
                            <input type="text" wire:model="farm_name" class="form-control" placeholder="Enter Farm Name">
                            @error('farm_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-4">
                            <label for="farm_address">Farm Address</label>
                            <input type="text" wire:model="farm_address" class="form-control" placeholder="Enter Farm Address">
                            @error('farm_address') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-4">
                            <label for="farm_supervisor_mobile">Supervisor Mobile</label>
                            <input type="text" wire:model="farm_supervisor_mobile" class="form-control" placeholder="Enter Mobile #">
                            @error('farm_supervisor_mobile') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4">
                            <label for="vehicle_no">Vehicle No.</label>
                            <input type="text" wire:model.live="vehicle_no" class="form-control" placeholder="Enter Vehicle No.">
                            @error('vehicle_no') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-4">
                            <label for="vehicle_fare">Freight</label>
                            <input type="number" wire:model.live="vehicle_fare" class="form-control" placeholder="Enter Freight">
                            @error('vehicle_fare') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-4">
                            <label for="comments">Remarks</label>
                            <input type="text" wire:model="comments" class="form-control" placeholder="Enter Remarks">
                            @error('comments') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer mt-0">
                <button wire:click="store()" class="btn btn-success">{{ $isEditMode ? 'Update' : 'Save' }}</button>
            </div>
        </div>
    </div>
</div>

</div>
@script
<script>
    window.addEventListener('showModal_order', event => {
        var myModal_order = new bootstrap.Modal(document.getElementById('myModal_order'));
        myModal_order.show();
    });

    window.addEventListener('hideModal_order', event => {
        var myModal_order = bootstrap.Modal.getInstance(document.getElementById('myModal_order'));
        if (myModal_order) {
            myModal_order.hide();
        }
    });
</script>
@endscript
