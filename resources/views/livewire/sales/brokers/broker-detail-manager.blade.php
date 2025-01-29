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
<div wire:loading wire:target="store, update,deleteCustomer,itemsPerPage,searchTerm" class="spinner"></div>

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

                                <div class="col-md-5"> <!-- Added ms-auto to push it to the right -->
                                    <div class="d-flex justify-content-end">
                                        <div class="search-box">
                                            <input type="text" class="form-control" placeholder="Search..." wire:model.live="searchTerm" />
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                </div>


                            <div class="col-2 text-end">
                                    <button type="button" wire:click="create()" class="btn btn-success btn-label">
                                    <i class="ri-add-circle-line label-icon align-middle fs-16"></i> New </button>
                            </div>

</div>

<div class="table-responsive">



                    <table class="table table-centered align-middle table-nowrap mb-0">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Address, Email, Phone</th>
                <th>CNIC / STRN / NTN</th>
                <th>Limits</th>
                <th>Brokery Rate</th>
                <th>Balance</th>

                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @php $sno=1 ; @endphp
            @foreach($customers as $customer)

                <tr>

                <td>{{ $sno }}</td>
                <td>
                <div class="d-flex align-items-center">
                        <img src="{{ $customer->avatar ? Storage::disk('spaces')->url($customer->avatar) : asset('images/user-dummy-img.jpg') }}"
                            alt="Avatar" class="image avatar-xs rounded-circle" style="width:60px;height:60px">
                </div>
                </td>
                <td>
                    <span class="text-uppercase badge bg-success-subtle text-success" style="font-size:12px;">
                        {{ $customer->coaTitle ? $customer->coaTitle->name : 'No Account Assigned' }}
                    </span><br/>
                    <span class="text-primary" style="font-weight:600;">
                        {{ $customer->coaGroupTitle ? $customer->coaGroupTitle->name : 'No Group' }}
                    </span>

                </td>
                <td style="word-wrap: break-word; white-space: normal;">
                @if($customer->address)
                        <i class="ri-home-7-line"></i>: {{ $customer->address }} <br/>
                    @endif

                    @if($customer->email)
                        <i class="ri-email-7-line"></i>: {{ $customer->email }}<br/>
                    @endif

                    @if($customer->phone)
                        <i class="ri-phone-line"></i>: {{ $customer->phone }}
                    @endif
                </td>

                <td>
                    CNIC # : {{ $customer->cnic }} <br/>
                    STRN #: {{ $customer->strn }} <br/>
                    NTN #: {{ $customer->ntn }}
                </td>


                <td class="d-none">
                    @if($customer->discount > 0)
                     {{ $customer->discount }} <small>%</small>
                    @endif
                    @if($customer->bonus > 0)
                     <br/>{{ $customer->bonus }} <small>per bag</small>
                    @endif
                </td>
                <td>
                    Amount: {{ number_format($customer->credit_limit) }}<br/>
                    Days: {{ $customer->payment_terms }}</td>
                </td>
                <td>
                    {{ number_format($customer->broker_rate,2) }}<small> % </small>
                </td>
                <td>
                    {{ $customer->coaTitle ? number_format($customer->coaTitle->balance) : '0' }} <small> {{ $customer->coaTitle ? $customer->coaTitle->drcr : 'Dr.' }}</small>
                </td>

                    <td>
                        <div class="hstack gap-3 flex-wrap">
                            @can('sales brokers edit')
                                <a wire:click="edit({{ $customer->id }})" href="javascript:void(0);" class="link-success fs-15"><i class="ri-edit-2-line" style="font-size:18px;"></i></a>
                            @endcan

                            @can('sales brokers delete')
                            <a onclick="confirmDeletionCustomer{{ $customer->id }}({{ $customer->id }})" href="javascript:void(0);" class="link-danger fs-15">
                                <i class="ri-delete-bin-line" style="font-size:18px;"></i></a>
                                @endcan
                                <div class="dropdown">
                                                                <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="true" class="show">
                                                                    <i class="ri-more-2-fill text-muted"></i>
                                                                </a>

                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1" data-popper-placement="bottom-start" data-popper-escaped="" style="position: absolute; inset: 0px auto auto 0px; margin: 0px; transform: translate3d(-134.4px, 20.8px, 0px);">

                                                                    <li><a class="dropdown-item" href="#">Created: {{ $customer->userCreated->name }}, {{ $customer->created_at->format('d-m-Y h: i A') }}</a></li>

                                                                    @if($customer->userUpdated != NULL)
                                                                    <li><a class="dropdown-item" href="#">Updated: {{ $customer->userUpdated->name }}, {{ $customer->updated_at->format('d-m-Y h: i A') }}</a></li>
                                                                    @endif
                                                                </ul>
                                            </div>
                        </div>
                        @can('sales brokers delete')
                                        <script>
                                                    function confirmDeletionCustomer{{ $customer->id }}(accountId) {
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
                @php  $sno = $sno + 1; @endphp
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
  <div wire:ignore.self class="modal fade" id="myModal_customer" tabindex="-1" aria-labelledby="myModal_customerLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModal_customerLabel">{{ $isEditMode ? 'Edit Broker' : 'Add Broker' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <!-- ChartOfAccount Fields -->
                        <div class="row mb-2">
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="account_name">Broker Name</label>
                                    <input type="text" wire:model="account_name" class="form-control" id="account_name">
                                    @error('account_name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>


                        </div>

                        <!-- Opening Balance with Debit/Credit Option -->
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="balance">Opening Balance</label>
                                    <input type="number" wire:model="balance" min="0" class="form-control" id="balance">
                                    @error('balance') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="drcr">Debit / Credit</label>
                                    <select wire:model="drcr" class="form-select" id="drcr" style="width:100px;">
                                        <option value="Dr.">Dr.</option>
                                        <option value="Cr.">Cr.</option>
                                    </select>
                                    @error('drcr') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6 mb-2">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" wire:model="email" class="form-control" id="email">
                                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Other Customer Details -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" wire:model="phone" class="form-control" id="phone">
                                    @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <input type="text" wire:model="address" class="form-control" id="address">
                                    @error('address') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3 ">

                        <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cnic">CNIC <small>(National Identity Card)</small></label>
                                    <input type="text" wire:model="cnic" class="form-control" id="cnic"
                                     pattern="\d{5}-\d{7}-\d{1}"
                                     placeholder="12345-1234567-1">
                                    @error('cnic') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="strn">STRN <small>(Sales Tax Registration Number)</small></label>
                                    <input type="text" wire:model="strn" class="form-control" id="strn">
                                    @error('strn') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ntn">NTN <small>(National Tax Number)</small></label>
                                    <input type="text" wire:model="ntn" class="form-control" id="ntn">
                                    @error('ntn') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Grouping Discount, Bonus, Credit Limit, and Payment Terms in One Row -->
                        <div class="row mb-3">
                            <div class="col-md-3 d-none">
                                <div class="form-group">
                                    <label for="discount">Discount <small>(%)</small></label>
                                    <input type="number" wire:model="discount" step="0.01" class="form-control" id="discount">
                                    @error('discount') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-3 d-none">
                                <div class="form-group">
                                    <label for="bonus">Discount <small>(per bag)</small></label>
                                    <input type="number" wire:model="bonus" step="0.01" class="form-control" id="bonus">
                                    @error('bonus') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="credit_limit">Credit Limit (amount)</label>
                                    <input type="number" wire:model="credit_limit" class="form-control" id="credit_limit">
                                    @error('credit_limit') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="payment_terms">Payment Terms (days)</label>
                                    <input type="number" wire:model="payment_terms" class="form-control" id="payment_terms">
                                    @error('payment_terms') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="broker_rate">Broker Rate (%)</label>
                                        <input type="number" wire:model="broker_rate" class="form-control" id="broker_rate" min="0" step="0.01">
                                        @error('broker_rate') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                            </div>
                            </div>


                        <div class="row mb-2">

                            <div class="col-md-4">
                                <div class="form-group align-items-center">
                                            <label for="avatar">Avatar</label>
                                            <input type="file" wire:model="avatar" class="form-control" id="avatar">
                                            @error('avatar') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-4 text-end">
                                <div wire:loading wire:target="avatar" class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>

                                @if ($avatar)
                                                <img src="{{ $avatar->temporaryUrl() }}" alt="Avatar Preview" class="img-thumbnail" style="width: 100px; height: 100px;">
                                            @else
                                                <!--<img src="{{ asset('images/default-avatar.jpg') }}" alt="Default Avatar" class="img-thumbnail" style="width: 100px; height: 100px;">-->
                                 @endif
                                        </div>
                            </div>




                    </form>
                </div>
                <div class="modal-footer">
                            <button wire:click="store()"
                                    wire:loading.attr="disabled"
                                    wire:target="store"
                                    class="btn btn-success">

                                <!-- Show 'Save' or 'Update' based on isEditMode when not submitting -->
                                <span wire:loading.remove wire:target="store">
                                    {{ $isEditMode ? 'Update' : 'Save' }}
                                </span>

                                <!-- Show 'Submitting...' when submitting -->
                                <span wire:loading wire:target="store">
                                    Submitting...
                                </span>

                            </button>
                    </div>
            </div>
        </div>
    </div>

</div>

@script
<script>
        window.addEventListener('showModal_customer', event => {
            var myModal_customer = new bootstrap.Modal(document.getElementById('myModal_customer'));
            myModal_customer.show();
        });

        window.addEventListener('hideModal_customer', event => {
            var myModal_customer = bootstrap.Modal.getInstance(document.getElementById('myModal_customer'));
            if (myModal_customer) {
                myModal_customer.hide();
            }
        });
    </script>

@endscript
