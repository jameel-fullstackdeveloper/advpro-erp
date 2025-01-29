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


                            <div class="row mb-3">
                                <div class="col-md-2">
                                        <select wire:model.live="itemsPerPage" class="form-control form-select" style="width:80px;">
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                            <option value="150">150</option>
                                            <option value="200">200</option>
                                        </select>
                                </div>

                                <div class="col-md-3">

                                </div>

                                <div class="col-md-5">
                                    <div class="d-flex justify-content-end">
                                        <div class="search-box">
                                            <input type="text" class="form-control" placeholder="Search..." wire:model.live="searchTerm" />
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-2 text-end" >
                                    <button type="button" wire:click="creategroup()" class="btn btn-success btn-label">
                                        <i class="ri-add-circle-line label-icon align-middle fs-16 me-2"></i> New</button>
                                </div>

                            </div>


                    <div>




                    <table class="table align-middle table-nowrap mb-0">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Created at</th>
                <th>Updated at</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)

                <tr>
                <td>{{ $customer->id }}</td>
                <td> <span style="font-weight:600;">{{ $customer->name }} </span><br/>
                <td>{{ $customer->userCreated->name }}, {{ $customer->created_at->format('d-m-Y h: i A') }}</td>
                <td>
                    @if($customer->userUpdated != NULL)
                    {{ $customer->userUpdated->name }}, {{ $customer->updated_at->format('d-m-Y h: i A') }}</a></li>
                                                                    @endif</td>

                    <td>
                        <div class="hstack gap-3 flex-wrap">
                        @can('customers edit')
                            <a wire:click="editgroup({{ $customer->id }})" href="javascript:void(0);" class="link-success fs-15"><i class="ri-edit-2-line" style="font-size:18px;"></i></a>
                        @endcan

                        @can('customers delete')

                            <a onclick="confirmDeletionCustomergroup{{ $customer->id }}({{ $customer->id }})" href="javascript:void(0);" class="link-danger fs-15">
                                <i class="ri-delete-bin-line" style="font-size:18px;"></i></a>
                        @endcan


                        </div>
                        @can('customers delete')
                                        <script>
                                                    function confirmDeletionCustomergroup{{ $customer->id }}(accountId) {
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
                                                            @this.confirmDeletiongroup(accountId);
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

                        <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <p class="mb-0 small text-muted">
                                        Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} results
                                    </p>
                                </div>
                                <div>
                                    {{ $customers->links() }}
                                </div>
                    </div>




                </div>
            </div>
        </div>
    </div>
</div>

  <!-- Modal for Add/Edit Customer -->
  <div wire:ignore.self class="modal fade" id="myModal_customergroup" tabindex="-1" aria-labelledby="myModal_customerLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModal_customerLabell">{{ $isEditMode ? 'Edit Customer Group' : 'Add Customer Group' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <!-- ChartOfAccount Fields -->
                        <div class="row mb-2">
                            <div class="col-md-6 mb-2">
                                <div class="form-group">
                                    <label for="account_name">Group Name</label>
                                    <input type="text" wire:model="name" class="form-control" id="name">
                                    @error('account_name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button wire:click="store()" class="btn btn-success">{{ $isEditMode ? 'Update' : 'Save' }}</button>
                </div>
            </div>
        </div>
    </div>

</div>

@script
<script>
        window.addEventListener('showModal_customergroup', event => {
            var myModal_customer = new bootstrap.Modal(document.getElementById('myModal_customergroup'));
            myModal_customer.show();
        });

        window.addEventListener('hideModal_customergroup', event => {
            var myModal_customer = bootstrap.Modal.getInstance(document.getElementById('myModal_customergroup'));
            if (myModal_customer) {
                myModal_customer.hide();
            }
        });
    </script>

@endscript
