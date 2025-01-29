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
    </style>

    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">All Inwards</h4>
                    <div class="flex-shrink-0">
                        <div class="form-check form-switch form-switch-right form-switch-md">
                        <button type="button" wire:click="$refresh" class="btn btn-warning"> <i class="ri-refresh-line"></i></button>
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
            <th>Vendor</th>
            <th>Products</th>
            <th>Vendro Weight</th>
            <th>Mill Weight</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @php
            $previousOutwardId = null;
            $totalQuantity = 0;  // Initialize total quantity
        @endphp
        @foreach($outwards as $outward)
            @php
                $totalQuantity += $outward->quantity;  // Sum the quantity for each row
            @endphp
            <tr>
                <!-- Only show the ID, Date & Time, Vehicle, and Customer for the first row of each group -->
                @if ($previousOutwardId !== $outward->outward_id)
                    <td rowspan="{{ $outwards->where('outward_id', $outward->outward_id)->count() }}">{{ $outward->outward_id }}</td>
                    <td rowspan="{{ $outwards->where('outward_id', $outward->outward_id)->count() }}">
                        @if($outward->first_weight_datetime)
                            {{ \Carbon\Carbon::parse($outward->first_weight_datetime)->format('d-m-Y') }}<br/>
                            {{ \Carbon\Carbon::parse($outward->first_weight_datetime)->format('h:i A') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td rowspan="{{ $outwards->where('outward_id', $outward->outward_id)->count() }}">{{ $outward->truck_number }}</td>
                    <td rowspan="{{ $outwards->where('outward_id', $outward->outward_id)->count() }}">
                        <span class="text-uppercase badge bg-success-subtle text-success" style="font-size:12px;">{{ $outward->customer_name ?? 'N/A' }}</span><br/>
                        {{ $outward->farm_name ?? 'N/A' }} <br/>
                        {{ $outward->farm_address ?? 'N/A' }}
                    </td>
                @endif

                <!-- Product details -->
                <td>
                    <strong>{{ $outward->item_name }}</strong><br/>{{ $outward->quantity }} <small>Bags</small></td>


                <!-- Weight details, only for the first row of the group -->
                @if ($previousOutwardId !== $outward->outward_id)
                    <td rowspan="{{ $outwards->where('outward_id', $outward->outward_id)->count() }}">

                    <strong>Gross:</strong>  {{ $outward->party_gross_weight }} <small>kg</small> <br/>
                    <strong>Tare:</strong> {{ $outward->party_tare_weight ?? 'N/A' }} <small>kg</small> <br/>
                        <strong>Net:</strong> {{ $outward->party_net_weight ?? 'N/A' }} <small>kg</small>
                    </td>
                @endif

                <!-- Weight details, only for the first row of the group -->
                @if ($previousOutwardId !== $outward->outward_id)
                    <td rowspan="{{ $outwards->where('outward_id', $outward->outward_id)->count() }}">
                        <strong>Tare:</strong> {{ $outward->first_weight }} <small>kg</small> <br/>
                        <strong>Gross:</strong> {{ $outward->net_weight ?? 'N/A' }} <small>kg</small> <br/>
                        <strong>Net:</strong> {{ $outward->second_weight ?? 'N/A' }} <small>kg</small>
                    </td>
                @endif

                <!-- Actions, only for the first row of each group -->
                @if ($previousOutwardId !== $outward->outward_id)
                    <td rowspan="{{ $outwards->where('outward_id', $outward->outward_id)->count() }}">
                        <a wire:click="print({{ $outward->outward_id }})" href="javascript:void(0);" class="link-dark ">
                            <i class="ri-printer-line fs-20"></i>
                        </a>
                    </td>
                @endif
            </tr>
            @php
                $previousOutwardId = $outward->outward_id;
            @endphp
        @endforeach
    </tbody>

    <!-- Total Quantity at the end of the table -->
    <tfoot>
        <tr>
            <td colspan="4" class="text-end"><strong>Total Bags:</strong></td>
            <td><strong>{{ number_format($totalQuantity) }} Bags</strong></td>
            <td colspan="3"></td>
        </tr>
    </tfoot>
</table>



                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <p class="mb-0 small text-muted">
                                Showing {{ $outwards->firstItem() }} to {{ $outwards->lastItem() }} of {{ $outwards->total() }} results
                            </p>
                        </div>
                        <div>
                            {{ $outwards->links() }}
                        </div>
                    </div>
                </div>

</div>

</div>
</div>
</div>
