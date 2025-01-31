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
                    <h4 class="card-title mb-0 flex-grow-1">Accounts Titles</h4>
                    <!-- export test -->
                    <!--<button wire:click="export" class="btn btn-success">Export</button>-->
                    <div class="flex-shrink-0">
                        <div class="form-check form-switch form-switch-right form-switch-md">
                        <button type="button" wire:click="create()" class="btn btn-success btn-label">
                        <i class="ri-add-circle-line label-icon align-middle fs-16 me-2"></i> New Account</button>
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
                                <ul class="nav nav-tabs nav-justified nav-border-top nav-border-top-primary" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link {{ $selectedCategory === null ? 'active' : '' }}" data-bs-toggle="tab" href="javascript:void(0);" role="tab" aria-selected="true" wire:click="filterByCategory(null)">All
                                        </a>
                                    </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link {{ $selectedCategory === 'Assets' ? 'active' : '' }}" data-bs-toggle="tab" href="#border-navs-home" role="tab" aria-selected="true" wire:click="filterByCategory('Assets')">Assets
                                            <span class="badge bg-dark rounded-circle text-white">{{ $counts['assets'] }}</span>
                                            </a>

                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link {{ $selectedCategory === 'Liabilities' ? 'active' : '' }}" data-bs-toggle="tab" href="#border-navs-profile" role="tab" aria-selected="false" tabindex="-1" wire:click="filterByCategory('Liabilities')">Liabilities <span class="badge bg-dark rounded-circle text-white"> {{ $counts['liabilities'] }}</span></a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link {{ $selectedCategory === 'Equity' ? 'active' : '' }}" data-bs-toggle="tab" href="#border-navs-messages" role="tab" aria-selected="false" tabindex="-1" wire:click="filterByCategory('Equity')">Equity
                                            <span class="badge bg-dark rounded-circle text-white"> {{ $counts['equity'] }}</span>
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link {{ $selectedCategory === 'Revenue' ? 'active' : '' }}"
                                            data-bs-toggle="tab" href="#border-navs-settings" role="tab" aria-selected="false" tabindex="-1"
                                            wire:click="filterByCategory('Revenue')">Revenue <span class="badge bg-dark rounded-circle text-white"> {{ $counts['revenue'] }}</span></a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link {{ $selectedCategory === 'Expenses' ? 'active' : '' }}" data-bs-toggle="tab" href="#border-navs-settings" role="tab" aria-selected="false" tabindex="-1" wire:click="filterByCategory('Expenses')">Expenses <span class="badge bg-dark rounded-circle text-white"> {{ $counts['expenses'] }}</span> <a/>
                                        </li>
                                    </ul>

                            <ul class="nav nav-tabs nav-justified mb-3 d-none" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#nav-badge-home" role="tab" aria-selected="false" tabindex="-1">
                                                Assets <span class="badge bg-dark rounded-circle"> </span>
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link align-middle" data-bs-toggle="tab" href="#nav-badge-profile" role="tab" aria-selected="false" tabindex="-1">
                                                Liablaties <span class="badge bg-dark rounded-circle"> 0</span>
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link align-middle" data-bs-toggle="tab" href="#nav-badge-messages" role="tab" aria-selected="false" tabindex="-1">
                                                Equity <span class="badge bg-dark rounded-circle"> 0</span>
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" data-bs-toggle="tab" href="#nav-badge-settings" role="tab" aria-selected="true">
                                                Revenue <span class="badge bg-dark rounded-circle"> 0</span>
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" data-bs-toggle="tab" href="#nav-badge-settings" role="tab" aria-selected="true">
                                                Expenses <span class="badge bg-dark rounded-circle"> 0</span>
                                            </a>
                                        </li>
                                    </ul>
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
                        <table class="table table-centered align-middle table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Group</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Customer / Vendor</th>
                                    <th scope="col">Balance</th>
                                    <th scope="col">Segment</th>
                                   <!-- <th scope="col">Cost Center</th> -->
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($accounts as $account)
                                    <tr>
                                        <td>{{ $account->id }}</td>
                                        <td width="300px;" style="word-wrap: break-word; white-space: normal;"><strong>{{ $account->name }}</strong></td>
                                        <td><span class="badge bg-success-subtle text-success text-uppercase" style="margin-right:5px;">
                                             {{ $account->chartOfAccountGroup->name }}
                                        </span></td>

                                        <td>  {{ $account->chartOfAccountGroup->chartOfAccountsType->category ?? 'No Type' }}</td>
                                        <td>
                                            @if($account->is_customer_vendor == 'customer')
                                                <span class="text-info fs-13 mb-0">CUSTOMER</span>
                                            @endif

                                            @if($account->is_customer_vendor == 'vendor')
                                            <span class="text-warning fs-13 mb-0">VENDOR</span>
                                            @endif

                                        </td>
                                        <td>
                                            {{ number_format($account->balance,0) }}
                                            @if($account->balance > 0)
                                                <small> {{ $account->drcr }}</small>
                                            @endif
                                        </td>

                                        <td>
                                            {{ $account->company->name }}

                                        </td>


                                        <td>


                                                <div class="hstack gap-1 flex-wrap">

                                                    @if(in_array($account->id, [1,2,3,4,5,6,7,8,9,10,11,12,13,14,610]))
                                                        @can('accounting chart of account edit')
                                                        <a wire:click="edit({{ $account->id }})" href="javascript:void(0);" class="link-success fs-15"><i class="ri-edit-2-line"></i></a>
                                                         @endcan
                                                         <i class="ri-lock-2-line" style="font-size:16px;"></i>

                                                    @else

                                                    @can('accounting chart of account edit')
                                                        <a wire:click="edit({{ $account->id }})" href="javascript:void(0);" class="link-success fs-15"><i class="ri-edit-2-line"></i></a>
                                                    @endcan

                                                    @can('accounting chart of account delete')
                                                        <a wire:click="confirmDeletion({{ $account->id }})" href="javascript:void(0);"
                                                        class="link-danger fs-15"><i class="ri-delete-bin-line"></i></a>
                                                    @endcan


                                                    @endif


                                                    <div class="dropdown">
                                                                <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="true" class="show">
                                                                    <i class="ri-more-2-fill text-muted"></i>
                                                                </a>

                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1" data-popper-placement="bottom-start" data-popper-escaped="" style="position: absolute; inset: 0px auto auto 0px; margin: 0px; transform: translate3d(-134.4px, 20.8px, 0px);">

                                                                    <li><a class="dropdown-item" href="#">Created: {{ $account->userCreated->name }}, {{ $account->created_at->format('d-m-Y h: i A') }}</a></li>

                                                                    @if($account->userUpdated != NULL)
                                                                    <li><a class="dropdown-item" href="#">Updated: {{ $account->userUpdated->name }}, {{ $account->updated_at->format('d-m-Y h: i A') }}</a></li>
                                                                    @endif
                                                                </ul>
                                </div>



                                                </div>

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <p class="mb-0 small text-muted">
                                        Showing {{ $accounts->firstItem() }} to {{ $accounts->lastItem() }} of {{ $accounts->total() }} results
                                    </p>
                                </div>
                                <div>
                                    {{ $accounts->links() }}
                                </div>
                    </div>



                    @if($isOpen)
                        <div id="myModal_account" class="modal fade @if($isOpen) show @endif" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="{{ $isOpen ? 'false' : 'true' }}" style="{{ $isOpen ? 'display: block;' : '' }}">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="myModalLabel">{{ $selected_id ? 'Edit Account' : 'Create Account' }}</h5>
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

                                            <!-- COA Head Dropdown -->
                                            <div class="form-group mt-2">
                                            @if(in_array($selected_id, [1,2,3,4,5,6,7,8,9,10,11,12,13,14,610]))


                                                @else
                                                <label for="group_id">Group</label>

                                                <select class="form-select" id="group_id" wire:model.live="group_id">
                                                    <option value="">Select Group</option>
                                                    @foreach ($accountHeads as $head)
                                                        <option value="{{ $head->id }}">{{ $head->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('group_id') <span class="text-danger">{{ $message }}</span>@enderror

                                                @endif
                                            </div>

                                            <div class="row">
                                            <!-- Balance Field -->
                                                <div class="col-6 form-group mt-2">
                                                    <label for="balance">Starting Balance</label>
                                                    <input type="text" class="form-control" id="balance" wire:model="balance">
                                                    @error('balance') <span class="text-danger">{{ $message }}</span>@enderror
                                                </div>

                                                <!-- Dr or Cr-->
                                                <div class="col-6 form-group mt-2">
                                                    <label for="drcr">Debit / Credit</label>
                                                    <select class="form-select" id="drcr" wire:model="drcr">
                                                            <option value="">---Select Debit/Credit---</option>
                                                            <option value="Dr.">Dr.</option>
                                                            <option value="Cr.">Cr.</option>
                                                        </select>
                                                    @error('drcr') <span class="text-danger">{{ $message }}</span>@enderror
                                                </div>


                                                        <div class="form-group mt-3">
                                                            <label for="segment_id">Segment</label>
                                                            <select class="form-select" id="segment_id" wire:model.live="segment_id" required >
                                                                <option value="">---Select Segment---</option>
                                                                @foreach ($segments as $segment)
                                                                    <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('segment_id') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>

                                                        <div class="form-group mt-3 d-none">
                                                            <label for="cost_center_id">Cost Center</label>
                                                            <select class="form-select" id="cost_center_id" wire:model="cost_center_id" @if(!$segment_id) disabled @endif required>
                                                                <option value="">---Select Cost Center---</option>
                                                                @foreach ($costCenters as $Costcenter)
                                                                    <option value="{{ $Costcenter->id }}">{{ $Costcenter->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('cost_center_id') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>

                                                        <div class="form-group mt-3">
                                                            <label for="is_customer_vendor">Customer / Vendor  <small class="text-warning">(only for customer & vendor Module)</small></label>
                                                            <select class="form-select" id="is_customer_vendor" wire:model.live="is_customer_vendor" >
                                                                <option value="">---Select---</option>
                                                                <option value="customer">Customer</option>
                                                                <option value="vendor">Vendor</option>

                                                            </select>
                                                            @error('is_customer_vendor') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer mt-3">
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



    $wire.on('showModal_account', () => {
        setTimeout(() => {
            var myModal_account = new bootstrap.Modal(document.getElementById('myModal_account'));
            myModal_account.show();
        }, 50);
    });

    $wire.on('hideModal_account', () => {
        var myModal_account = bootstrap.Modal.getInstance(document.getElementById('myModal_account'));
        if (myModal_account) {
            myModal_account.hide();
        }
    });

</script>

@endscript
