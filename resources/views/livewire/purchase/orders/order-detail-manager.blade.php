
<div>
<style>
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
<div wire:loading wire:target="itemsPerPage,store, update, create, order_date, vendor_id, product_id, searchTerm" class="spinner"></div>


    <!-- Filters Section -->
    <div class="mb-3">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-end">
                <div class="col-md-1">
                    <select wire:model.live="itemsPerPage" class="form-control form-select" style="width:80px;">
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="150">150</option>
                        <option value="200">200</option>
                        <option value="300">300</option>
                        <option value="400">400</option>
                    </select>
                </div>



                    <div class="col-md-2">
                        <input type="date" wire:model="order_date" class="form-control" placeholder="Order Date">
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="vendor_id" class="form-control form-select">
                            <option value="">-- Select Vendor --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="product_id" class="form-control form-select">
                            <option value="">-- Select Product --</option>
                            @foreach($allProducts as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" wire:model.live="searchTerm" class="form-control" placeholder="Search Orders">
                    </div>
                    <div class="col-md-1 text-end">
                         <button type="button" wire:click="create()" class="btn btn-success btn-label">
                                <i class="ri-add-circle-line label-icon align-middle fs-16"></i> New </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Orders Table -->
    <div class="card">
        <div class="card-body">


        <div>
    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Error Message -->
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
</div>



            <table class="table align-middle table-nowrap">
                <thead class="table-light">
                    <tr>
                        <th>Order</th>
                        <th>Vendor</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Delivery Mode</th> <!-- New Field -->
                         <th>Credit Days</th> <!-- New Field -->
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrders as $order)
                    <tr>
                        <td>{{ $order->order_number }}<br/>
                        {{ \Carbon\Carbon::parse($order->order_date)->format('d-m-Y') }}
                     </td>
                        <td style="word-wrap: break-word; white-space: normal;">
                                    <h5 class="fs-14 my-1 fw-medium">
                                                <span class="text-success text-uppercase">  {{ $order->vendor->name }}</span>
                                    </h5>


                                    <span class="text-muted">
                                        {{ \App\Models\CustomerDetail::where('account_id', $order->vendor_id)->with('coaGroupTitle')->first()->coaGroupTitle->name ?? 'N/A' }}
                                    </span>
                                </div>

                                </div>
                        </td>
                        <td>
                                @foreach ($order->items as $item)
                                    <strong>{{ optional($item->product)->name ?? 'N/A' }}</strong><br>
                                    <span class="text-warning fs-13 mb-0 fw-bold" >@ {{ $item->price }}</span><br/>
                                @endforeach
                        </td>

                        <td>
                                @foreach ($order->items as $item)

                            <p>Ord: {{ number_format($item->quantity, 0) }} |
                            Rec: {{ number_format($item->remaining_quantity, 0) }} |
                            Bal: {{ number_format($item->quantity - $item->remaining_quantity, 0) }} </p>
                            @endforeach


                        </td>



                        <td>
                                @if($order->delivery_mode== 'deliverd')
                                    <span class="text-success fs-13 mb-0 fw-bold" >{{ ucfirst($order->delivery_mode) }}</span>

                                @endif

                                @if($order->delivery_mode== 'ex-mill')
                                    <span class="text-info fs-13 mb-0 fw-bold">{{ ucfirst($order->delivery_mode) }}</span>

                                @endif</td>

                        <!-- New Field -->
                        <td>{{ $order->credit_days }}</td> <!-- New Field -->
                        <td>

                            @if($order->status== 'init')
                                <span class="badge bg-info">{{ ucfirst($order->status) }}</span>
                            @endif

                            @if($order->status== 'progress')
                                <span class="badge bg-warning">{{ ucfirst($order->status) }}</span>
                            @endif

                            @if($order->status== 'completed')
                                <span class="badge bg-success">{{ ucfirst($order->status) }}</span>
                            @endif

                        </td>
                        <td>
                            <div class="hstack gap-3 flex-wrap">

                            @can('purchases orders edit')
                            <a wire:click="edit({{ $order->id }})" href="javascript:void(0);" class="link-success fs-15"><i class="ri-edit-2-line" style="font-size:18px;"></i></a>
                            @endcan

                            @can('purchases orders delete')
                            <a onclick="confirmDeletionOrder{{ $order->id }}({{ $order->id }})" href="javascript:void(0);" class="link-danger fs-15">
                                 <i class="ri-delete-bin-line" style="font-size:18px;"></i></a>
                                 @endcan

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

                            @can('purchases orders delete')
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
            {{ $purchaseOrders->links() }}
        </div>
    </div>

    <!-- Model -->
    <div wire:ignore.self class="modal fade" id="modal_order" tabindex="-1" aria-labelledby="modal_orderLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $isEditMode ? 'Edit Purchase Order' : 'Add New Purchase Order' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <!-- Order Date -->
                    <div class="col-md-3">
                        <label for="order_date">Order Date</label>
                        <input type="date" wire:model="order_date" class="form-control">
                        @error('order_date') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <!-- Broker -->
                    <div class="col-md-9">
                        <label for="broker_id">Broker (optional)</label>
                        <select wire:model="broker_id" class="form-select">
                            <option value="0">-- Select Broker --</option>
                            @foreach($brokers as $broker)
                                <option value="{{ $broker->id }}">{{ $broker->name }}</option>
                            @endforeach
                        </select>
                        @error('broker_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>


                </div>

                <!-- Vendor (on its own row) -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="vendor_id">Vendor</label>
                        <select wire:model="vendor_id" class="form-select">
                            <option value="">-- Select Vendor --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }} [ {{ $vendor->customerGroup ? $vendor->customerGroup->name : 'No Group' }} ]</option>
                            @endforeach
                        </select>
                        @error('vendor_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row mb-3">

                        <div class="col-12">
                            <h5>Order Items</h5>
                            <table class="table table-sm" style="padding:0px;margin:0px;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 20%;padding:5px;">Product</th>
                                        <th style="padding:5px;">Quantity</th>
                                        <th style="padding:5px;">Rate</th>
                                        <th style="padding:5px;" class="text-center">Action</th>

                                    </tr>
                                </thead>
						        <tbody>
                                @foreach($items as $index => $item)
								    <tr>
										<td style="width:70%;padding:5px;">
											<select wire:model="items.{{ $index }}.product_id" class="form-select">
												<option value="">-- Select Product --</option>
												@foreach($allProducts as $product)
													<option value="{{ $product->id }}">{{ $product->name }}</option>
												@endforeach
											</select>
											@error('items.' . $index . '.product_id') <span class="text-danger">{{ $message }}</span> @enderror
										</td>

								<td>
									<input type="number" wire:model="items.{{ $index }}.quantity" class="form-control" step="any">
									@error('items.' . $index . '.quantity') <span class="text-danger">{{ $message }}</span> @enderror
								</td>

								<td>
									<input type="number" wire:model="items.{{ $index }}.price" class="form-control" step="any">
									@error('items.' . $index . '.price') <span class="text-danger">{{ $message }}</span> @enderror
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





                <div class="row mb-3">

                    <!-- Delivery Mode -->
                    <div class="col-md-4 mb-3">
                            <label for="delivery_mode">Delivery Mode</label>
                            <select wire:model="delivery_mode" class="form-select">
                                <option value="">-- Select Delivery Mode --</option>
                                <option value="ex-mill">Ex-mill</option>
                                <option value="deliverd">Delivered</option>
                            </select>
                            @error('delivery_mode') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                     <!-- Credit Days -->
                    <div class="col-md-4 mb-3">
                        <label for="credit_days">Credit Days</label>
                        <input type="number" wire:model="credit_days" class="form-control" min="0">
                        @error('credit_days') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <!-- Status -->
                    <div class="col-md-4 mb-3">
                        <label for="status">Comments</label>
                               <input type="text" wire:model="comments" class="form-control">
                            @error('comments') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>


                    <!-- Status -->
                    <div class="col-md-4 mb-3">
                        <label for="status">Status</label>
                        @if($isEditMode)
                                <select wire:model="status" class="form-select">
                                    <option value="init">Initial</option>
                                    <option value="progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            @else
                                <input type="text" wire:model="status" class="form-control" readonly>
                            @endif
                            @error('status') <span class="text-danger">{{ $message }}</span> @enderror
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


<script>
    window.addEventListener('showModal_order', event => {
        var myModal_order = new bootstrap.Modal(document.getElementById('modal_order'));
        myModal_order.show();
    });

    window.addEventListener('hideModal_order', event => {
        var myModal_order = bootstrap.Modal.getInstance(document.getElementById('modal_order'));
        if (myModal_order) {
            myModal_order.hide();
        }
    });
</script>
