<div>
<style>
        /* Custom Tooltip Styling */
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
    bottom: 125%; /* Adjust this value to position the tooltip */
    left: 50%;
    transform: translateX(-50%);
    white-space: nowrap; /* Prevents text wrapping */
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
    opacity: 0;
    transition: opacity 0.3s;
}

.custom-tooltip:hover .custom-tooltiptext {
    visibility: visible;
    opacity: 1;
}

    </style>

<!-- Spinner element -->
<div wire:loading wire:target="store, update,delete,itemsPerPage,searchTerm,filter" class="spinner"></div>


<div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Finished Goods Adjustments</h4>
                    <!-- export test -->
                    <!--<button wire:click="export" class="btn btn-success">Export</button>-->
                    <div class="flex-shrink-0">
                        <div class="form-check form-switch form-switch-right form-switch-md">
                        <button type="button" wire:click="create()" class="btn btn-success btn-label">
                        <i class="ri-add-circle-line label-icon align-middle fs-16 me-2"></i> New</button>
                        </div>
                    </div>
                </div>

                <div class="card-body">

                      <!-- Displaying session messages -->
                      @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show material-shadow" role="alert">
                            <i class="ri-notification-off-line label-icon"></i>  {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show material-shadow" role="alert">
                            <i class="ri-notification-off-line label-icon"></i>  {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif


                    <div class="row g-4 mb-3">
                        <div class="col-1">
                            <div>
                                <select wire:model.live="itemsPerPage" class="form-contorl form-select" style="width:80px;">
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="150">150</option>
                                    <option value="200">200</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-8">
                            <div>



                            </div>
                        </div>

                        <div class="col-3">
                            <div class="d-flex justify-content-sm-end">
                                <div class="search-box">
                                    <input type="text"
                                           class="form-control"
                                           placeholder="Search..."
                                           wire:model.live="searchTerm" />
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

    <div class="table-responsive">
        <!-- Table Section -->
        <table class="table table-centered align-middle table-nowrap mb-0">
        <thead class="table-light">
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Date</th>
                <th scope="col">Material</th>
                <th scope="col">Shortage</th>
                <th scope="col">Excess</th>
                <!--<th scope="col">Created By</th>
                <th scope="col">Updated By</th> -->
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stockMaterialAdjustments as $adjustment)
                <tr>
                    <td>{{ $adjustment->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($adjustment->adj_date)->format('d-m-Y') }}</td>
                    <td style="word-wrap: break-word; white-space: normal;font-weight:bold;">{{ $adjustment->salesItem->product_name ?? 'N/A' }}</td>
                    <td>{{ $adjustment->shortage }}</td>
                    <td>{{ $adjustment->exccess }}</td>

                    <td class="d-none">
                                            <span class="custom-tooltip">
                                                {{ $adjustment->createdBy->name }}
                                                <span class="custom-tooltiptext">{{ $adjustment->created_at->format('d-m-Y h: i A') }}</span>
                                            </span>
                                        </td>

                    <td class="d-none">
                                            @if($adjustment->updatedBy != NULL)
                                                <span class="custom-tooltip">
                                                    {{ $adjustment->updatedBy->name }}
                                                    <span class="custom-tooltiptext">{{ $adjustment->updated_at->format('d-m-Y h: i A') }}</span>
                                                </span>
                                            @endif
                                        </td>
                    <td>


                                                <div class="d-flex justify-content-end gap-1">

                                                <a wire:click="edit({{ $adjustment->id }})" href="javascript:void(0);" class="link-success fs-15">
                                                    <i class="ri-edit-2-line"></i>
                                                </a> &nbsp;
                                                @auth
                                                    @if(auth()->user()->hasRole('Administrator') || auth()->user()->hasRole('Super Admin'))
                                                     <a wire:click="confirmDeletion({{ $adjustment->id }})" href="javascript:void(0);" class="link-danger fs-15"><i class="ri-delete-bin-line"></i></a>
                                                    @endif
                                                @endauth


                                    <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-more-2-fill text-muted"></i>
                                    </a>

                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                                        <li>
                                            <a class="dropdown-item" href="#">Created: {{ $adjustment->createdBy->name }}, {{ $adjustment->created_at->format('d-m-Y h:i A') }}</a>
                                        </li>

                                        @if($adjustment->updatedBy)
                                        <li>
                                            <a class="dropdown-item" href="#">Updated: {{ $adjustment->updatedBy->name }}, {{ $adjustment->updated_at->format('d-m-Y h:i A') }}</a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>


        <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <p class="mb-0 small text-muted">
                                        Showing {{ $stockMaterialAdjustments->firstItem() }} to {{ $stockMaterialAdjustments->lastItem() }} of {{ $stockMaterialAdjustments->total() }} results
                                    </p>
                                </div>
                                <div>
                                    {{ $stockMaterialAdjustments->links() }}
                                </div>
                    </div>


                        <!-- Modal for Create/Edit -->
                        @if($isOpen)
                            <div id="myModal_stock_adjustment" class="modal fade @if($isOpen) show @endif" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="{{ $isOpen ? 'false' : 'true' }}" style="{{ $isOpen ? 'display: block;' : '' }}">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="myModalLabel">{{ $selected_id ? 'Edit Account' : 'Create Account' }}</h5>
                                                            <button type="button" wire:click="closeModal()" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">

                                                         <!-- Displaying all validation errors -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                                                        <form>
                                                            <div class="form-group mt-2">
                                                                <label for="adj_date">Adjustment Date</label>
                                                                <input type="date" class="form-control" id="adj_date" wire:model="adj_date">
                                                                @error('adj_date') <span class="text-danger">{{ $message }}</span> @enderror
                                                            </div>
                                                            <!-- Show items based on type -->
                                                            <div class="form-group mt-2">
                                                                <label for="material_id">Item</label>
                                                                <select class="form-select" wire:model.live="material_id" required>
                                                                    <option value="">---Select Item---</option>
                                                                    @foreach ($items as $item)
                                                                        <option value="{{ $item->id }}">{{ $item->item_name ?? $item->product_name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('material_id') <span class="text-danger">{{ $message }}</span> @enderror
                                                            </div>

                                                            <!-- Add a field to specify whether entering shortage or excess -->
                                                            <div class="form-group mt-2 d-none">
                                                                <label>Adjustment Type</label>
                                                                <select class="form-select" wire:model="inputType" required>
                                                                    <option value="shortage">Shortage</option>
                                                                    <option value="exccess">Excess</option>
                                                                </select>
                                                            </div>

                                                            <!-- Show the input field based on the type selected -->
                                                            <div class="form-group mt-2">
                                                                    <label for="shortage" class="text-danger">Shortage</label>
                                                                    <input type="number" class="form-control mb-2" id="shortage" wire:model="shortage">
                                                                    @error('shortage') <span class="text-danger">{{ $message }}</span> @enderror

                                                                    <label for="exccess" class="text-success">Excess</label>
                                                                    <input type="number" class="form-control" id="exccess" wire:model="exccess">
                                                                    @error('exccess') <span class="text-danger">{{ $message }}</span> @enderror

                                                            </div>



                                                        </form>

</div>
                                                        <div class="modal-footer">
                                                            <button type="button" wire:click="closeModal()" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                            <button type="button" wire:click="store" class="btn btn-primary">
                                                                {{ $selected_id ? 'Update' : 'Create' }}
                                                            </button>
                                                        </div>
                                                    </div><!-- /.modal-content -->
                                                </div><!-- /.modal-dialog -->
                                            </div><!-- /.modal -->
                        @endif
    </div>
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
                @this.call('delete', voucherId);  // Call the delete method with the voucher ID
            }
        });
    });

    $wire.on('showModal_stock_adj', () => {
        setTimeout(() => {
            var myModal_stock_adjustment = new bootstrap.Modal(document.getElementById('myModal_stock_adjustment'));
            myModal_stock_adjustment.show();
        }, 50);
    });

    $wire.on('hideModal_stock_adj', () => {
        var myModal_stock_adjustment = bootstrap.Modal.getInstance(document.getElementById('myModal_stock_adjustment'));
        if (myModal_stock_adjustment) {
            myModal_stock_adjustment.hide();
        }
    });

</script>

@endscript
