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

        .table thead th {
            text-align: center;
        }

        .table tbody td {
            vertical-align: middle;
        }

        .table-input, .table-select {
            padding: 4px 8px;
            font-size: 14px;
            border-radius: 4px;
            border: 1px solid #ccc;
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .table-input-small {
            width: 70px;
            padding: 4px;
            font-size: 14px;
            text-align: right;
            border-radius: 4px;
        }

        .table-input:focus, .table-select:focus {
            border-color: #2a9d8f;
            box-shadow: 0px 0px 8px rgba(42, 157, 143, 0.2);
        }

        .form-select.table-input {
            background-color: #f9f9f9;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 14px;
        }

        .d-flex {
            display: flex;
            align-items: center;
        }

        .table th, .table td {
            vertical-align: middle;
            padding: 8px;
        }

        .modal-body .form-section {
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }

        .btn-success {
            background-color: #2a9d8f;
            border-color: #2a9d8f;
            color: white;
        }

        .btn-success:hover {
            background-color: #238a75;
            border-color: #238a75;
        }

        .table td .form-control {
            padding: 5px;
        }

        .table-input {
            padding: 6px;
        }

        .tabheading {
            color: #2a9d8f;
        }
    </style>

    <!-- Filters Section -->
    <div class="mb-2">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-1">
                        <select wire:model="itemsPerPage" id="itemsPerPage" class="form-control form-select" style="width:80px;">
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

                    <div class="col-md-3">
                        <select wire:model.live="selectedCustomer" id="selectedCustomer" class="form-control form-select">
                            <option value="">-- Select Vendor --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="text" class="form-control" id="searchTerm" placeholder="Search..." wire:model.live="searchTerm">
                    </div>

                    <div class="col-md-2 text-end">
                        <button type="button" wire:click="create()" class="btn btn-success btn-label waves-effect waves-light"><i class="bx bx-alarm-add label-icon align-middle fs-16"></i> New</button>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Sales Return Table -->
    <div class="card">
        <div class="card-body">

        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

            <div class="table-responsive">
            <table class="table table-centered align-middle table-nowrap mb-0">
    <thead class="table-light">
        <tr>
            <th>Return Number</th>
            <th>Vendor</th>
            <th>Item</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Net Amount</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalQuantity = 0;
            $totalAmount = 0;
        @endphp
        @foreach($returns as $return)
            @foreach($return->items as $index => $item)
                <tr>
                    <!-- Return Information, only for the first row of each return -->
                    @if ($index === 0)
                        <td rowspan="{{ count($return->items) }}">
                        <span class="fw-medium link-muted">{{ $return->return_number }}</span><br/>
                            {{ \Carbon\Carbon::parse($return->return_date)->format('d-m-Y') }}</td>

                        <!-- Customer Details -->
                        <td rowspan="{{ count($return->items) }}">
                            <div class="d-flex align-items-center">

                                <div>
                                    <h5 class="fs-14 my-1 fw-medium">
                                    <span class="text-success text-uppercase">{{ \App\Models\ChartofAccount::find($return->vendor_id)->name ?? 'N/A' }}</span>
                                    </h5>
                                    <span class="text-muted">{{ \App\Models\CustomerDetail::where('account_id', $return->vendor_id)->with('coaGroupTitle')->first()->coaGroupTitle->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </td>
                    @endif

                    <!-- Product Information -->
                    <td>{{ $products->find($item->product_id)->name ?? 'N/A' }}</td>
                    <td>{{ $item->return_quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->return_amount, 2) }}</td>

                    <!-- Status and Actions, only for the first row of each return -->
                    @if ($index === 0)
                        <td rowspan="{{ count($return->items) }}">
                            <span class="badge bg-{{ $return->status === 'draft' ? 'danger' : 'success' }}">{{ ucfirst($return->status) }}</span>
                        </td>
                        <td rowspan="{{ count($return->items) }}">
                            <div class="hstack gap-1 flex-wrap">

                            @can('purchases return edit')
                                <a wire:click="edit({{ $return->id }})" href="javascript:void(0);" class="link-success fs-15" title="Edit Return">
                                    <i class="ri-edit-2-line" style="font-size:16px;"></i>
                                </a>
                            @endcan


                                @can('purchases return delete')
                                        <a onclick="confirmDeletionSalesRetrun{{ $return->id }}({{ $return->id}})" href="javascript:void(0);" class="link-danger fs-15" title="Delete Sales Return">
                                        <i class="ri-delete-bin-line" style="font-size:16px;"></i></a>
                                @endcan

                                <div class="dropdown">
                                    <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-more-2-fill text-muted"></i>
                                    </a>

                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                                        <li>
                                            <a class="dropdown-item" href="#">Created: {{ $return->createdBy->name }}, {{ $return->created_at->format('d-m-Y h:i A') }}</a>
                                        </li>

                                        @if($return->updatedBy)
                                        <li>
                                            <a class="dropdown-item" href="#">Updated: {{ $return->updatedBy->name }}, {{ $return->updated_at->format('d-m-Y h:i A') }}</a>
                                        </li>
                                        @endif
                                    </ul>

                                </div>

                            </div>

                                     @can('purchases return delete')
                                            <script>
                                                    function confirmDeletionSalesRetrun{{ $return->id }}(accountId) {
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
                                                            @this.confirmDeletionSalesRetrun(accountId);
                                                            }
                                                        });
                                                    }
                                        </script>
                                @endcan

                        </td>
                    @endif
                </tr>

                @php
                    // Accumulate totals
                    $totalQuantity += $item->return_quantity;
                    $totalAmount += $item->return_amount;
                @endphp
            @endforeach
        @endforeach
    </tbody>

    <!-- Footer for totals -->
    <tfoot>
        <tr>
            <td colspan="3" class="text-end"><strong>Total:</strong></td>
            <td><strong>{{ $totalQuantity }}</strong></td>
            <td></td> <!-- Empty column for Unit Price -->
            <td><strong>{{ number_format($totalAmount) }}</strong></td>
            <td colspan="2"></td> <!-- Empty columns for Status and Actions -->
        </tr>
    </tfoot>
</table>

            </div>
        </div>
    </div>

    <!-- Sales Return Modal -->
    <div wire:ignore.self class="modal fade" id="modal_return" tabindex="-1" aria-labelledby="modal_returnLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header card-header align-items-center pb-2">
                    <h4 class="card-title mb-0 flex-grow-1">{{ $isEditMode ? 'Edit Purchase Return' : 'Add New Purchase Return' }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if (session()->has('formerrors'))
                        <div class="alert alert-danger alert-dismissible fade show material-shadow" role="alert">
                            <i class="ri-notification-off-line label-icon"></i> {{ session('formerrors') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Sales Return Form -->
                    <div class="form-section">
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Return Date</label>
                                <input type="date" wire:model="return_date" class="form-control shadow-sm">
                                @error('return_date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Return Number</label>
                                <input type="text" wire:model="return_number" class="form-control shadow-sm" placeholder="Return Number">
                                @error('return_number') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Select Vendor</label>
                                <select wire:model="vendor_id" class="form-control form-select shadow-sm">
                                    <option value="">-- Select Vendor --</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }} [ {{ $customer->customerGroup ? $customer->customerGroup->name : 'No Group' }} ]</option>
                                    @endforeach
                                </select>
                                @error('vendor_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Product Return Details Table -->
                    <div class="form-section">
                        <h5 class="mt-2 tabheading">Add / Remove Items</h5>
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 20%;">Product</th>
                                    <th>Return Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Return Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $index => $item)
                                    <tr>
                                        <td style="width:30%;">
                                            <select wire:model="items.{{ $index }}.product_id" class="form-control form-select table-input">
                                                <option value="">Select Product</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                            @error("items.$index.product_id") <span class="text-danger">{{ $message }}</span> @enderror
                                        </td>

                                        <td>
                                            <input type="number" wire:model="items.{{ $index }}.return_quantity" class="form-control table-input" wire:input="calculateAmounts({{ $index }})">
                                            @error("items.$index.return_quantity") <span class="text-danger">{{ $message }}</span> @enderror
                                        </td>

                                        <td>
                                            <input type="number" wire:model="items.{{ $index }}.unit_price" step="0.01" class="form-control table-input" wire:input="calculateAmounts({{ $index }})">
                                            @error("items.$index.unit_price") <span class="text-danger">{{ $message }}</span> @enderror
                                        </td>

                                        <td>
                                            <input type="number" wire:model="items.{{ $index }}.return_amount" step="0.01" class="form-control table-input" readonly>
                                            @error("items.$index.return_amount") <span class="text-danger">{{ $message }}</span> @enderror
                                        </td>

                                        <td style="text-align:center;">
                                            @if ($index === 0)
                                                <i class="ri-add-circle-line text-success" style="cursor:pointer;font-size:20px" wire:click="addItemRow()" title="Add New Row"></i>
                                            @else
                                                <i class="ri-delete-bin-5-line text-danger" style="cursor:pointer;font-size:20px" wire:click="removeItemRow({{ $index }})" title="Delete Row"></i>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                @if(count($items) == 0)
                                    <tr>
                                        <td colspan="5" class="text-center">No items added. Please add an item.</td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-end">Totals:</th>
                                    <th>{{ $totalReturnedQuantity }}</th>
                                    <th></th>
                                    <th>{{ number_format($totalReturnAmount) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
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

<script>
    window.addEventListener('showModal_return', event => {
        var myModal_return = new bootstrap.Modal(document.getElementById('modal_return'));
        myModal_return.show();
    });

    window.addEventListener('hideModal_return', event => {
        var myModal_return = bootstrap.Modal.getInstance(document.getElementById('modal_return'));
        if (myModal_return) {
            myModal_return.hide();
        }
    });
</script>
