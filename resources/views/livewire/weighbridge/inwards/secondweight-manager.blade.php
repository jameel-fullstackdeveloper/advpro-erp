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
                    <h4 class="card-title mb-0 flex-grow-1">Second Weight (Inwards)</h4>
                    <button type="button" wire:click="$refresh" class="btn btn-warning"> <i class="ri-refresh-line"></i></button>
                </div>

                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show material-shadow" role="alert">
                            <i class="ri-notification-off-line label-icon"></i> {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif



                    <!-- Second Weight Form -->
                    <div class="row mb-3">
                        <!-- Date & Time -->
                        <div class="col-md-2">
                            <label for="second_weight_datetime">Date & Time</label>
                            <input type="datetime-local" id="second_weight_datetime" wire:model="second_weight_datetime" class="form-control" required readonly>
                            @error('second_weight_datetime') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <!-- Select Vehicle -->
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

                        <!-- Driver/Without Driver (Auto-populated) -->
                        <div class="col-md-2">
                            <label for="driveroption">Driver / Without Driver</label>
                            <input type="text" id="driveroption" wire:model="driveroption" class="form-control" readonly>
                        </div>

                        <!-- Billty Number (Auto-populated) -->
                        <div class="col-md-2">
                            <label for="billty_number">Billty Number</label>
                            <input type="text" id="billty_number" wire:model="billty_number" class="form-control" readonly>
                        </div>

                        <!-- Total Bags (Auto-populated) -->
                        <div class="col-md-2">
                            <label for="total_bags">Total Bags</label>
                            <input type="number" id="total_bags" wire:model="total_bags" class="form-control" readonly>
                        </div>

                        <!-- Freight (Auto-populated) -->
                        <div class="col-md-2">
                            <label for="freight">Freight</label>
                            <input type="number" id="freight" wire:model="freight" class="form-control" readonly>
                        </div>


                    </div>

                    <hr/>

                    <!-- Orders Table -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-12">
                            <label class="fs-5 fw-bold mb-3 mt-3 text-info">Orders for this Vehicle</label>
                            <table class="table align-middle table-nowrap mb-0">
                                <thead class="table-info">
                                    <tr>
                                        <th scope="col">Order #</th>
                                        <th scope="col">Vendor</th>
                                        <th scope="col">Item</th>

                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($ordersForTruck as $orderId => $orderDetails)
                                        @foreach($orderDetails['products'] as $product)
                                            <tr>
                                                @if ($loop->first)
                                                    <td rowspan="{{ count($orderDetails['products']) }}">#{{ $orderDetails['order_number'] }}</td>
                                                    <td rowspan="{{ count($orderDetails['products']) }}">
                                                    <span class="text-uppercase badge bg-success-subtle text-success" style="font-size:12px;">
                                                        {{ $orderDetails['customer_name'] }} </span></td>
                                                @endif
                                                <td><strong>{{ $product['product_name'] }}</strong></td>

                                            </tr>
                                        @endforeach
                                @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <hr/>

                    <div class="row mb-3">


                        <!-- Party Gross Weight (Auto-populated) -->
                        <div class="col-md-2">
                            <label for="party_gross_weight">Party Gross Weight</label>
                            <input type="number" id="party_gross_weight" wire:model="party_gross_weight" class="form-control" readonly>
                        </div>

                        <!-- Party Tare Weight (Auto-populated) -->
                        <div class="col-md-2">
                            <label for="party_tare_weight">Party Tare Weight</label>
                            <input type="number" id="party_tare_weight" wire:model="party_tare_weight" class="form-control" readonly>
                        </div>

                        <!-- Party Net Weight (Auto-populated) -->
                        <div class="col-md-2">
                            <label for="party_net_weight">Party Net Weight</label>
                            <input type="number" id="party_net_weight" wire:model="party_net_weight" class="form-control" readonly>
                        </div>

                        <!-- First Weight (Auto-populated) -->
                        <div class="col-md-2">
                            <label for="first_weight_s">First Weight</label>
                            <input type="number" id="first_weight_s" wire:model="first_weight_s" class="form-control" readonly>
                            @error('first_weight_s') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <!-- Second Weight -->
                        <div class="col-md-2">
                            <label for="second_weight_s">Second Weight</label>
                            <input type="number" id="second_weight_s" wire:model="second_weight_s" class="form-control fs-14 fw-bold"  style="background-color:#fef4e4;color:#000;"
                            required wire:input="calculateNetWeight">
                            @error('second_weight_s') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <!-- Net Weight -->
                        <div class="col-md-2">
                            <label for="net_weight_s">Net Weight</label>
                            <input type="number" id="net_weight_s" wire:model="net_weight_s" class="form-control" readonly>
                            @error('net_weight_s') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="row">
                        <div class="col-md-12">
                                    <button wire:click="store()" class="btn btn-success"
                                    {{ $second_weight_s <= 0 || $isSubmitting ? 'disabled' : '' }}>
                                    {{ $isSubmitting ? 'Submitting...' :  'Save' }}
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
            text: 'Inwards Second Weight saved successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                // Reload the page after the user clicks OK
                window.location.reload();
            }
        });
    });

    window.addEventListener('initializeSocketForWeight_inwards_s', event => {
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
    const netWeight = firstWeight - secondWeight;

    // Set the net weight input and update Livewire state
    document.getElementById('net_weight_s').value = netWeight;
    @this.set('net_weight_s', netWeight);  // Update the Livewire component state
}


</script>
