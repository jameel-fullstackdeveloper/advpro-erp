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

<div class="row">
    <div class="col">
        <div class="card">

               <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Account Groups</h4>
                                    <div class="flex-shrink-0">
                                        <div class="form-check form-switch form-switch-right form-switch-md">
                                            <button type="button" wire:click="create()" class="btn btn-success btn-label">
                                                <i class="ri-add-circle-line label-icon align-middle fs-16 me-2"></i> New Group</button>
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

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show material-shadow" role="alert">
        <i class="ri-notification-off-line label-icon"></i>  {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif







    <div class="row g-4 mb-3">
                            <div class="col-sm-auto">
                                <div>
                                      <!-- Items per page dropdown -->
                                        <select wire:model.live="itemsPerPage" class="form-contorl form-select">
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                            <option value="150">150</option>
                                            <option value="200">200</option>
                                        </select>
                                </div>
                            </div>
                            <div class="col-sm">
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


    <!--<form wire:submit.prevent="store">
        <input type="text" wire:model="name" placeholder="Name">
        <input type="text" wire:model="type" placeholder="Type">
        <input type="number" wire:model="user_id" placeholder="User ID">
        <button type="submit">Save</button>
    </form>-->


                        <table class="table table-centered align-middle table-nowrap mb-0">
                            <thead class="table-light">
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Name</th>
                <th scope="col">Type</th>
                <th scope="col">Customer / Vendor</th>
                <th scope="col">Created by</th>
                <th scope="col">Updated by</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($accountHeads as $head)
                <tr>
                    <td>{{ $head->id }}</td>
                    <td width="350px;"><strong>{{ $head->name }}</strong></td>
                    <td><span class="badge bg-success-subtle text-success text-uppercase" style="margin-right:5px;">
                        {{ $head->chartOfAccountsType->name }}</span></td>

                        <td>
                                        @if($head->is_customer_vendor == 'customer')
                                                <span class="text-info fs-13 mb-0">CUSTOMER</span>
                                            @endif

                                            @if($head->is_customer_vendor == 'vendor')
                                            <span class="text-warning fs-13 mb-0">VENDOR</span>
                                            @endif

                                            @if($head->is_customer_vendor == 'farm')
                                            <span class="text-primary fs-13 mb-0">FARM</span>
                                            @endif

                        </td>

                        <td>
                            <span class="custom-tooltip">
                                                {{ $head->userCreated->name }}
                                                <span class="custom-tooltiptext">{{ $head->created_at->format('d-m-Y h: i A') }}</span>
                                            </span>
                        </td>

                        <td>
                                            @if($head->userUpdated != NULL)
                                                <span class="custom-tooltip">
                                                    {{ $head->userUpdated->name }}
                                                    <span class="custom-tooltiptext">{{ $head->updated_at->format('d-m-Y h: i A') }}</span>
                                                </span>
                                            @endif
                        </td>

                    <td>

                    @if(in_array($head->id, [1,2,5,6,7,8,9,10,11,12,13,62]))

                    @can('accounting chart of account edit')
                        <a wire:click="edit({{ $head->id }})" href="javascript:void(0);" class="link-success fs-15"><i class="ri-edit-2-line"></i></a>
                    @endcan


                        <i class="ri-lock-2-line" style="font-size:16px;"></i>
                    @else
                                <div class="hstack gap-3 flex-wrap">
                                @can('accounting chart of account edit')
                                                    <a wire:click="edit({{ $head->id }})" href="javascript:void(0);" class="link-success fs-15"><i class="ri-edit-2-line"></i></a>
                                @endcan

                                @can('accounting chart of account delete')
                                    <a wire:click="confirmDeletionAccountGroup({{ $head->id }})"
                                     href="javascript:void(0);" class="link-danger fs-15"><i class="ri-delete-bin-line"></i></a>
                                @endcan
                                </div>


                        @endif

                        <!--<button wire:click="edit({{ $head->id }})" class="btn btn-sm btn-primary">Edit</button>
                        <button onclick="confirmDeletion({{ $head->id }})" class="btn btn-sm btn-danger edit-item-btn">Delete</button>-->

                    </td>
                </tr>
            @endforeach

        </tbody>
    </table>


                <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <p class="mb-0 small text-muted">
                                Showing {{ $accountHeads->firstItem() }} to {{ $accountHeads->lastItem() }} of {{ $accountHeads->total() }} results
                            </p>
                        </div>
                        <div>
                            {{ $accountHeads->links() }}
                        </div>
                    </div>
                </div>


                @if($isOpen_coa_head)
<div id="myModal_chartofaccount_group" class="modal fade @if($isOpen_coa_head) show @endif" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="{{ $isOpen_coa_head ? 'false' : 'true' }}" style="{{ $isOpen_coa_head ? 'display: block;' : '' }}">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">{{ $selected_id ? 'Edit Group' : 'Create Group' }}</h5>
                <button type="button" wire:click="closeModal()" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <!-- Name Field -->
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" wire:model="name">
                        @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>



                    @if($selected_id)

                    @else

                    <!-- Type Field as Dropdown -->
            <div class="form-group mt-3">
                <label for="type">Account Type</label>
                <select class="form-select" id="type_id" wire:model="type_id">
                    <option value="">Select Type</option>
                    @foreach($accountTypes as $accountType)
                        <option value="{{ $accountType->id }}">{{ $accountType->name }}</option>
                    @endforeach

                </select>
                @error('type_id') <span class="text-danger">{{ $message }}</span>@enderror
            </div>

                    @endif


                    <div class="form-group mt-3">
                                                            <label for="is_customer_vendor">Customer / Vendor  <small class="text-warning">(only for customer & vendor Module)</small></label>
                                                            <select class="form-select" id="is_customer_vendor" wire:model.live="is_customer_vendor" >
                                                                <option value="">---Select---</option>
                                                                <option value="customer">Customer</option>
                                                                <option value="vendor">Vendor</option>
                                                                <option value="farm">Farm</option>

                                                            </select>
                                                            @error('is_customer_vendor') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>


                </form>
            </div>
            <div class="modal-footer">
                <button type="button" wire:click="closeModal()" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" wire:click="{{ $selected_id ? 'update' : 'store' }}()" class="btn btn-primary">
                    {{ $selected_id ? 'Save Changes' : 'Create' }}
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
</div><!--row -->




@script
<script>

$wire.on('swal:confirm-deletion-group', ({ voucherId }) => {
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
                @this.call('deletegroup', voucherId);  // Call the delete method with the voucher ID
            }
        });
    });

    $wire.on('showModal_group', () => {
        setTimeout(() => {
            var myModal_chartofaccount_group = new bootstrap.Modal(document.getElementById('myModal_chartofaccount_group'));
            myModal_chartofaccount_group.show();
        }, 50);  // Adjust the delay as needed (100ms is a good starting point)
    });

    $wire.on('hideModal_group', () => {
        var myModal_chartofaccount_group = bootstrap.Modal.getInstance(document.getElementById('myModal_chartofaccount_group'));
        if (myModal_chartofaccount_group) {
            myModal_chartofaccount_group.hide();
        }
    });


</script>

@endscript
