<div>
    <style>
        label {
            color: #000;
        }
        .table-header {
            background-color: #f8f9fa;
        }
    </style>

    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Second Weight</h4>
                    <button type="button" wire:click="$refresh" class="btn btn-warning"> <i class="ri-refresh-line"></i></button>
                </div>

                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show material-shadow" role="alert">
                            <i class="ri-notification-off-line label-icon"></i> {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif


                    @if ($errors->has('transaction'))
                                    <div class="alert alert-danger alert-dismissible fade show material-shadow" role="alert">
                                        <i class="ri-notification-off-line label-icon"></i> {{ $errors->first('transaction') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                    @endif

                    <!-- Current Date and Time for Second Weight -->
                    <div class="row mb-2">
                        <div class="col-md-2">
                            <label for="second_weight_s_datetime">Date & Time</label>
                            <input type="datetime-local" id="second_weight_s_datetime" wire:model="second_weight_s_datetime" class="form-control" required
                           readonly>
                            @error('second_weight_s_datetime') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <!-- Select Vehicle Dropdown -->
                        <div class="col-md-2">
                            <label for="selectedVehicle">Select Vehicle</label>
                            <select id="selectedVehicle" wire:model.live="selectedVehicle" class="form-select" required>
                                <option value="">-- Select Vehicle --</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->truck_number }}</option>
                                @endforeach
                            </select>
                            @error('selectedVehicle') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <!-- Select Order Dropdown -->
                        <div class="col-md-8">
                            <label for="selectedOrder">Select Order(s)</label>
                            <div class="d-flex align-items-center">
                                <select id="selectedOrder" wire:model="selectedOrder" class="form-select me-2">
                                    <option value="">-- Select Order --</option>
                                    @foreach($orders as $order)
                                        <option value="{{ $order->order_id }}">
                                            #{{ $order->order_number }},
                                            [Customer: {{ $order->customer_name ?? 'No Customer' }}],
                                            [Farm: {{ $order->farm_name ?? 'No Farm' }}]
                                        </option>
                                    @endforeach
                                </select>
                                <!-- Add Icon -->
                                <i class="ri-add-circle-line text-success" style="cursor:pointer;font-size:24px" wire:click="addOrderToTruck" title="Add Order"></i>
                            </div>
                        </div>
                    </div>

                  <!-- Display Added Orders for the Truck -->
<div class="row g-4 mb-4">
    <div class="col-md-12">
        <label class="fs-5 fw-bold mb-3 mt-3 text-info">Orders for this Vehicle</label>
        <table class="table align-middle table-nowrap mb-0">
            <thead class="table-info">
                <tr>
                    <th scope="col">Order #</th>
                    <th scope="col">Customer </th>
                    <th scope="col">Farm</th>
                    <th scope="col">Product</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Initialize the total quantity (bags) across all orders
                    $grandTotalQuantity = 0;
                @endphp

                @foreach($ordersForTruck as $orderId => $orderDetails)
                    @foreach($orderDetails['products'] as $product)
                        <tr>
                            <!-- Show order details only for the first product in the loop -->
                            @if ($loop->first)
                                <!-- Order Number -->
                                <td class="align-middle" rowspan="{{ count($orderDetails['products']) }}">
                                    #{{ $orderDetails['order']->order_number }}
                                </td>

                                <!-- Customer Name -->
                                <td class="align-middle" rowspan="{{ count($orderDetails['products']) }}">
                                <span class="text-uppercase badge bg-success-subtle text-success" style="font-size:12px;">{{ $orderDetails['order']->customer_name }}</span>
                                </td>

                                <!-- Farm Name -->
                                <td class="align-middle" rowspan="{{ count($orderDetails['products']) }}">
                                    {{ $orderDetails['order']->farm_name }}<br/>
                                    {{ $orderDetails['order']->farm_address }}
                                </td>
                            @endif

                            <!-- Product Name -->
                            <td class="align-middle">{{ $product->product_name }} <small>Bags</small></td>

                            <!-- Product Quantity -->
                            <td class="align-middle">
                                {{ $product->quantity }}
                                @php
                                    // Sum up the total quantity for all orders
                                    $grandTotalQuantity += $product->quantity;
                                @endphp
                            </td>

                            <!-- Actions (only show for the first product) -->
                            @if ($loop->first)
                                <td class="align-middle" rowspan="{{ count($orderDetails['products']) }}">
                                    <a wire:click="removeOrderFromTruck({{ $orderId }})" href="javascript:void(0);" class="link-danger fs-15">
                                <i class="ri-delete-bin-line" style="font-size:18px;"></i></a>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endforeach
            </tbody>

            <!-- Display Grand Total Bags at the End of the Table -->
            <tfoot>
                <tr class="table-light">
                    <td colspan="4" class="text-end fw-bold">Total Bags:</td>
                    <td class="fw-bold">{{ $grandTotalQuantity }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <div>
        @error('ordersForTruck')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>

    </div>
</div>



                    <!-- Second Weight Input -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-2">
                            <label for="driveroption">Driver / Without Driver</label>
                            <input type="text" id="driveroption" wire:model="driveroption" class="form-control" required readonly>
                            @error('driveroption') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2">
                            <label for="driver_name">Driver Name</label>
                            <input type="text" id="driver_name" wire:model="driver_name" class="form-control" required readonly>
                            @error('driver_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-2">
                            <label for="driver_mobile">Driver Mobile</label>
                            <input type="number" id="driver_mobile" wire:model="driver_mobile" class="form-control" required readonly>
                            @error('driver_mobile') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-2">
                            <label for="first_weight_s">First Weight</label>
                            <input type="number" id="first_weight_s" wire:model="first_weight_s" class="form-control" required readonly>
                            @error('first_weight_s') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-2">
                            <label for="second_weight_s">Second Weight</label>
                            <input type="number" id="second_weight_s" wire:model="second_weight_s" class="form-control fs-14 fw-bold"  style="background-color:#fef4e4;color:#000;"  required>
                            @error('second_weight_s') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-2">
                            <label for="net_weight_s">Net Weight</label>
                            <input type="number" id="net_weight_s" wire:model="net_weight_s" class="form-control" required >
                            @error('net_weight_s') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Save Button -->
                        <div class="row">
                            <div class="col-md-12">
                                    <button type="button"
                                            wire:click="store"
                                            class="btn btn-success"
                                            wire:loading.attr="disabled"
                                            wire:target="store"
                                            @if($isSubmitting || $second_weight_s <= 0 || $net_weight_s <= 0) disabled @endif>
                                            <span wire:loading wire:target="store" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            <span wire:loading.remove wire:target="store">Save Second Weight</span>
                                            <span wire:loading wire:target="store">Saving...</span>
                                    </button>
                            </div>
                        </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>

window.addEventListener('saved', event => {
        Swal.fire({
            title: 'Success!',
            text: 'Outward Second Weight saved successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                // Reload the page after the user clicks OK
                window.location.reload();
            }
        });
    });

    window.addEventListener('initializeSocketForWeight_s', event => {
        const socket = io('http://172.16.1.205:8888'); // Ensure this URL is correct for your scale

        socket.on("data", message => {
            // Update the input field with live data from the scale
            document.getElementById('second_weight_s').value = message.data;
            @this.set('second_weight_s', message.data);  // Update the Livewire component state
            calculateNetWeight();
        });

        socket.on("disconnect", message => {
            document.getElementById('second_weight_s').value = 0;  // Set weight to 0 when scale disconnects
            calculateNetWeight();
        });
    });

    function calculateNetWeight() {
    // Get the first and second weights from the inputs
    const firstWeight = parseFloat(document.getElementById('first_weight_s').value) || 0;
    const secondWeight = parseFloat(document.getElementById('second_weight_s').value) || 0;

    // Calculate net weight
    const netWeight = secondWeight - firstWeight ;

    // Set the net weight input and update Livewire state
    document.getElementById('net_weight_s').value = netWeight;
    @this.set('net_weight_s', netWeight);  // Update the Livewire component state
}


</script>
