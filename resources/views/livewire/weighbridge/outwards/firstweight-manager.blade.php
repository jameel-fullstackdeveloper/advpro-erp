<div>
    <style>
        .custom-tooltip {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .custom-tooltip .custom-tooltiptext {
            visibility: hidden;
            width: auto;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 5px;
            padding: 5px 10px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .custom-tooltip:hover .custom-tooltiptext {
            visibility: visible;
            opacity: 1;
        }

        label {
            color: #000;
        }

    </style>

    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">First Weight</h4>
                    <div class="flex-shrink-0">
                        <div class="form-check form-switch form-switch-right form-switch-md">
                            <button type="button" wire:click="create()" class="btn btn-success btn-label">
                                <i class="ri-add-circle-line label-icon align-middle fs-16 me-2"></i> New First Weight
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show material-shadow" role="alert">
                            <i class="ri-notification-off-line label-icon"></i>  {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row g-4 mb-3">
                        <div class="col-1">
                            <div>
                                <select wire:model.live="itemsPerPage" class="form-control form-select" style="width:80px;">
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="150">150</option>
                                    <option value="200">200</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-3 ms-auto">
                            <div class="d-flex justify-content-end">
                                <div class="search-box">
                                    <input type="text" class="form-control" placeholder="Search..." wire:model.live="searchTerm" />
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="table align-middle table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Date & Time</th>
                                <th>Vehicle Details</th>
                                <th>First Weight</th>
                                <th>Driver</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($outwardsfirstweight as $outward)
                                <tr>
                                    <td>{{ $outward->id }}</td>
                                    <td>@if($outward->created_at)
                                            {{ \Carbon\Carbon::parse($outward->created_at)->format('d-m-Y') }}<br/>
                                            {{ \Carbon\Carbon::parse($outward->created_at)->format('h:i A') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $outward->truck_number }}</td>

                                    <td>{{ $outward->first_weight }} <small>kg</small> <br/>
                                    <span class="text-muted mb-0">{{ $outward->driveroption }}</span> </td>
                                    <td>  <strong>Name:</strong> {{ $outward->driver_name }} <br/>
                                          <strong>Mobile:</strong> {{ $outward->driver_mobile }}</td>

                                    <td>
                                        <div class="hstack gap-3 flex-wrap">
                                            <a wire:click="edit({{ $outward->id }})" href="javascript:void(0);" class="link-success fs-15"><i class="ri-edit-2-line" style="font-size:18px;"></i></a>
                                            <a onclick="confirmDeletionFirstWeight{{ $outward->id }}({{ $outward->id }})" href="javascript:void(0);" class="link-danger fs-15">
                                                <i class="ri-delete-bin-line" style="font-size:18px;"></i></a>
                                        </div>

                                        <script>
                                                    function confirmDeletionFirstWeight{{ $outward->id }}(accountId) {
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
                                                            @this.confirmDeletionFirstWeight(accountId);
                                                            }
                                                        });
                                                    }
                                        </script>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <p class="mb-0 small text-muted">
                                Showing {{ $outwardsfirstweight->firstItem() }} to {{ $outwardsfirstweight->lastItem() }} of {{ $outwardsfirstweight->total() }} results
                            </p>
                        </div>
                        <div>
                            {{ $outwardsfirstweight->links() }}
                        </div>
                    </div>
                </div>

</div>

</div>
</div>

<!-- Modal for Create/Edit -->
<div wire:ignore.self class="modal fade" id="myModal_firstweight" tabindex="-1" aria-labelledby="myModal_customerLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">

            <div class="modal-content">
                <div class="modal-header card-header align-items-center d-flex pb-2">
                    <h5 class="modal-title">{{ $isEditMode ? 'Edit First Weight' : 'New First Weight' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row">

                        <div class="col-md-4">
                            <div class="mb-3">
                            <label>Date</label>
                                <input type="date-local" wire:model="first_weight_datetime" class="form-control" readonly>
                                @error('first_weight_datetime')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Vehicle Number</label>
                                <input type="text" wire:model="truck_number" class="form-control" required>
                                @error('truck_number') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Driver / Without Driver</label>
                                <select name="driveroption" wire:model="driveroption" class="form-select" required="">
                                    <option value="">---Select---</option>
                                    <option value="With Out Driver">With Out Driver</option>
                                    <option value="With Drive">With Driver</option>
                                </select>
                                @error('driveroption') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Driver Name</label>
                                <input type="text" wire:model="driver_name" class="form-control" required>
                                @error('driver_name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Driver Mobile <small> (without dash)</small></label>
                                <input type="text" wire:model="driver_mobile" class="form-control"
                                    maxlength="11"
                                    pattern="\d{11}"
                                    title="Please enter a valid 11-digit mobile number"
                                    inputmode="numeric"
                                    required />
                                @error('driver_mobile') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>First Weight</label>
                                @if($isEditMode)
                                    <input type="number" id="saved_weight" wire:model="first_weight" class="form-control fs-14 fw-bold"
                                    style="background-color:#fef4e4;color:#000;"
                                    readonly />
                                    @error('first_weight') <span class="text-danger">{{ $message }}</span> @enderror

                                @else
                                    <input type="number" id="first_weight" wire:model="first_weight" class="form-control fs-14 fw-bold"
                                    style="background-color:#fef4e4;color:#000;"
                                    readonly />
                                    @error('first_weight') <span class="text-danger">{{ $message }}</span> @enderror



                                @endif
                            </div>
                        </div>



                    </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button wire:click="store()" class="btn btn-success" {{ $first_weight <= 0 ? 'disabled' : '' }}>{{ $isEditMode ? 'Update' : 'Save' }}</button>
                </div>
            </div>
        </div>
    </div><!-- End of Model -->

</div>




@script
<script>
    window.addEventListener('showModal_firstweight', event => {
            var myModal_customer = new bootstrap.Modal(document.getElementById('myModal_firstweight'));
            myModal_customer.show();
        });

    window.addEventListener('hideModal_firstweight', event => {
            var myModal_customer = bootstrap.Modal.getInstance(document.getElementById('myModal_firstweight'));
            if (myModal_customer) {
                myModal_customer.hide();
            }
    });

    window.addEventListener('initializeSocketForWeight', event => {
        const socket = io('http://172.16.1.205:8888'); // Ensure this URL is correct for your scale

        socket.on("data", message => {
            // Update the input field with live data from the scale
            document.getElementById('first_weight').value = message.data;
            @this.set('first_weight', message.data);  // Update the Livewire component state
        });

        socket.on("disconnect", message => {
            document.getElementById('first_weight').value = 0;  // Set weight to 0 when scale disconnects
        });
    });



</script>

@endscript
