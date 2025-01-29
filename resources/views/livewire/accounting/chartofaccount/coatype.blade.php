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
                                    <h4 class="card-title mb-0 flex-grow-1">Account Types</h4>
                                    <div class="flex-shrink-0">
                                        <div class="form-check form-switch form-switch-right form-switch-md">
                                            <button type="button" wire:click="create()" class="btn btn-success btn-label">
                                                <i class="ri-add-circle-line label-icon align-middle fs-16 me-2"></i> New Type</button>
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

<div>


    <!--<form wire:submit.prevent="store">
        <input type="text" wire:model="name" placeholder="Name">
        <input type="text" wire:model="type" placeholder="Type">
        <input type="number" wire:model="user_id" placeholder="User ID">
        <button type="submit">Save</button>
    </form>-->

    <table class="table align-middle table-nowrap">
    <thead class="table-light">
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Name</th>
                <th scope="col">Category</th>
                <th scope="col">Created</th>
                <th scope="col">Updated</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($accountTypes as $type)
                <tr>
                    <td>{{ $type->id }}</td>
                    <td width="350px;"><strong>{{ $type->name }}</strong></td>
                    <td><span class="badge bg-success-subtle text-success text-uppercase" style="margin-right:5px;">{{ $type->category }}</span></td>

                    <td>
                            <span class="custom-tooltip">
                                                {{ $type->userCreated->name }}
                                                <span class="custom-tooltiptext">{{ $type->created_at->format('d-m-Y h: i A') }}</span>
                                            </span>
                        </td>

                        <td>
                                            @if($type->userUpdated != NULL)
                                                <span class="custom-tooltip">
                                                    {{ $type->userUpdated->name }}
                                                    <span class="custom-tooltiptext">{{ $type->updated_at->format('d-m-Y h: i A') }}</span>
                                                </span>
                                            @endif
                        </td>

                    <td>

                    @if(in_array($type->id, [1, 2, 3, 4, 5, 6]))
                    <i class="ri-lock-2-line" style="font-size:16px;"></i>
                    @else
                                <div class="hstack gap-3 flex-wrap">
                                @can('accounting chart of account edit')
                                                    <a wire:click="edit({{ $type->id }})" href="javascript:void(0);" class="link-success fs-15"><i class="ri-edit-2-line"></i></a>
                                @endcan

                                @can('accounting chart of account delete')
                                                     <a onclick="confirmDeletionAccountType{{ $type->id }}({{ $type->id }})" href="javascript:void(0);" class="link-danger fs-15"><i class="ri-delete-bin-line"></i></a>
                                @endcan
                                </div>
                                @can('accounting chart of account delete')
                                <script>
                                function confirmDeletionAccountType{{ $type->id }}(userId) {
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
                                            @this.confirmDeletion(userId);
                                                /*Swal.fire(
                                                'Deleted!',
                                                'The user has been deleted.',
                                                'success'
                                                )*/
                                            }
                                        });
                                    }
                                </script>
                                @endcan
                    @endif

                    </td>
                </tr>
            @endforeach

        </tbody>
    </table>


                <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <p class="mb-0 small text-muted">
                                Showing {{ $accountTypes->firstItem() }} to {{ $accountTypes->lastItem() }} of {{ $accountTypes->total() }} results
                            </p>
                        </div>
                        <div>
                            {{ $accountTypes->links() }}
                        </div>
                    </div>
                </div>


                @if($isOpen_coa_head)
<div id="myModal_chartofaccount_type" class="modal fade @if($isOpen_coa_head) show @endif" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="{{ $isOpen_coa_head ? 'false' : 'true' }}" style="{{ $isOpen_coa_head ? 'display: block;' : '' }}">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">{{ $selected_id ? 'Edit Type' : 'Create New Type' }}</h5>
                <button type="button" wire:click="closeModal()" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

            @if (session()->has('error'))
                                    <div class="alert alert-danger alert-dismissible fade show material-shadow" role="alert">
                                    <i class="ri-notification-off-line label-icon"></i>  {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                <form>
                    <!-- Name Field -->
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" wire:model="name">
                        @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>

               <!-- Type Field as Dropdown -->
            <div class="form-group mt-3">
                <label for="category">Category</label>
                <select class="form-select" id="category" wire:model="category">
                    <option value="">Select Category</option>
                    <option value="Assets">Assets</option>
                    <option value="Liabilities">Liabilities</option>
                    <option value="Equity">Equity</option>
                    <option value="Revenue">Revenue</option>
                    <option value="Expenses">Expenses</option>

                </select>
                @error('category') <span class="text-danger">{{ $message }}</span>@enderror
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

    $wire.on('showModal_type', () => {
        setTimeout(() => {
            var myModal_chartofaccount_type = new bootstrap.Modal(document.getElementById('myModal_chartofaccount_type'));
            myModal_chartofaccount_type.show();
        }, 50);  // Adjust the delay as needed (100ms is a good starting point)
    });

    $wire.on('hideModal_type', () => {
        var myModal_chartofaccount_type = bootstrap.Modal.getInstance(document.getElementById('myModal_chartofaccount_type'));
        if (myModal_chartofaccount_type) {
            myModal_chartofaccount_type.hide();
        }
    });


</script>

@endscript
