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
<div wire:loading wire:target="store, update,deleteCustomer,itemsPerPage,searchTerm,filter,imagePV" class="spinner"></div>

    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Payments</h4>
                    <div class="flex-shrink-0">
                        <div class="form-check form-switch form-switch-right form-switch-md">
                            <button type="button" wire:click="createVoucherPV()" class="btn btn-danger btn-label">
                                <i class="ri-add-circle-line label-icon align-middle fs-16 me-2"></i> Add Bank Payment
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
                        <div class="col-2 d-flex align-items-center">
                            <label class="me-2 mb-0" for="itemsPerPage_pv">Show</label>
                            <select wire:model="itemsPerPage" class="form-select" id="itemsPerPage_pv" style="width: 80px;">
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="150">150</option>
                                <option value="200">200</option>
                            </select>
                            <label class="ms-2 mb-0" for="searchTerm">Entries</label>
                        </div>

                        <div class="col-8">
                            <div class="search-box">
                                <input type="text" class="form-control" id="searchTerm" placeholder="Search by voucher number or account title ..." wire:model.live="searchTerm">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>

                        <div class="col-2">
                            <div>
                                <select class="form-select" wire:model.live="filter" id="selectFilter" style="width:160px;">
                                    <option value="Today">Today</option>
                                    <option value="CurrentMonth">Current Month</option>
                                    <option value="CurrentYear">Current Year</option>
                                    <option value="LastMonth">Last Month</option>
                                    <option value="LastQuarter">Last Quarter</option>
                                    <option value="LastYear">Last Year</option>
                                    <option value="Last30Days">Last 30Days</option>
                                    <option value="Last60Days">Last 60Days</option>
                                    <option value="Last90Days">Last 90Days</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="row">
                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                    <div class="table-responsive">
                                    <table class="table align-middle table-nowrap mb-0">
    <thead class="table-light">
        <tr>
            <th>Date</th>
            <th>Voucher</th>
            <th>Payment from</th>
            <th>Payment at</th>
            <th>Description</th>
            <th>Total Amount</th>

            @if (env('ALLOW_UPLOAD_VOUCHER_ATTACHMENTS') === 'true')<th>Image</th>@endif
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalAmountPay = 0;
        @endphp
        @forelse ($vouchers as $voucher)
            @php
                $creditAccount = $voucher->voucherDetails->firstWhere('type', 'credit');
                $debitAccounts = $voucher->voucherDetails->where('type', 'debit');
            @endphp

            @if ($debitAccounts->isNotEmpty())
                @foreach ($debitAccounts as $index => $debitAccount)
                    <tr>
                        @if ($loop->first)
                            <td rowspan="{{ $debitAccounts->count() }}">{{ \Carbon\Carbon::parse($voucher->date)->format('d-m-Y') }}</td>
                            <td rowspan="{{ $debitAccounts->count() }}">{{ $voucher->reference_number }}</td>
                            <td rowspan="{{ $debitAccounts->count() }}" style="word-wrap: break-word; white-space: normal;">
                                @if ($creditAccount)
                                    <span class="text-danger mb-0"><strong>{{ $creditAccount->account->name }}</strong></span>
                                @else
                                    <em>No credit account found</em>
                                @endif
                            </td>
                        @endif

                        <!-- Display payment at details (debit accounts) -->
                        <td style="word-wrap: break-word; white-space: normal;">
                            <span class="text-info mb-0"><strong>{{ $debitAccount->account->name }}</strong></span>
                        </td>

                        @if ($loop->first)
                            <td rowspan="{{ $debitAccounts->count() }}" style="word-wrap: break-word; white-space: normal;">{{ $voucher->description }}</td>
                        @endif

                        <!-- Display total amount only for the first row -->

                            <td >{{  number_format($debitAccount->amount, 0) }}</td>

                        @if ($loop->first)


                        @if (env('ALLOW_UPLOAD_VOUCHER_ATTACHMENTS') === 'true')
                            <!-- Image Thumbnail with Modal -->
                            <td rowspan="{{ $debitAccounts->count() }}">
                                @if ($voucher->image_path)
                                    <img src="{{ Storage::disk('spaces')->url($voucher->image_path) }}" alt="Image Thumbnail" class="img-thumbnail" style="width: 40px; height: 40px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imageModal{{ $voucher->id }}">
                                @else
                                    <em>No Image</em>
                                @endif

                                <!-- Modal for showing the full image -->
                                <div class="modal fade" id="imageModal{{ $voucher->id }}" tabindex="-1" aria-labelledby="imageModalLabel{{ $voucher->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="imageModalLabel{{ $voucher->id }}">Image Preview</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center" style="overflow: auto; max-height: 90vh;">
                                                @if ($voucher->image_path)
                                                    <img src="{{ Storage::disk('spaces')->url($voucher->image_path) }}" alt="Full Image" class="img-fluid" style="max-width: 100%; height: auto;">
                                                @else
                                                    <em>No Image</em>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @endif

                            <!-- Actions -->
                            <td rowspan="{{ $debitAccounts->count() }}">
                            <div class="d-flex justify-content-end gap-1">

                                @can('accounting bankbook edit')

                                @if($voucher->farm_account == 0 )
                                    <a wire:click="editVoucherPV({{ $voucher->id }})" href="javascript:void(0);" class="link-info">
                                        <i class="ri-edit-2-line" style="font-size:16px;"></i>
                                    </a>
                                    @endif
                                @endcan

                                @can('accounting bankbook delete')

                                @if($voucher->farm_account == 0 )
                                <a wire:click="confirmDeletionPV({{ $voucher->id }})" href="javascript:void(0);" class="link-danger">
                                        <i class="ri-delete-bin-line" style="font-size:16px;"></i>
                                    </a>

                                    @else

                                            <span class="text-muted" style="padding-right:5px;">Farm Exp</span>
                                         @endif
                                @endcan
                                    <a @click="window.open('{{ route('voucher.pvprint', ['id' => $voucher->id]) }}', '_blank')" class="link-primary" href="javascript:void(0);">
                                        <i class="ri-printer-line" style="font-size:16px;"></i>
                                    </a>

                                    <!-- Dropdown for more actions -->
                                    <div class="dropdown">
                                        <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="true" class="show">
                                            <i class="ri-more-2-fill text-muted"></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                                            <li><a class="dropdown-item" href="#">Created: {{ $voucher->createdBy->name }}, {{ $voucher->created_at->format('d-m-Y h: i A') }}</a></li>
                                            @if($voucher->updatedBy)
                                                <li><a class="dropdown-item" href="#">Updated: {{ $voucher->updatedBy->name }}, {{ $voucher->updated_at->format('d-m-Y h: i A') }}</a></li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>


                            </td>
                        @endif
                    </tr>
                @endforeach
            @endif

            @php
                $totalAmountPay += $voucher->total_amount;
            @endphp
        @empty
            <tr>
                <td colspan="9">No vouchers found.</td>
            </tr>
        @endforelse
        <tfoot class="table-light">
            <tr>
                <td colspan="5" style="font-weight:bold;">Total</td>
                <td style="font-weight:bold;">{{ number_format($totalAmountPay) }}</td>

                <td></td>
            </tr>
        </tfoot>
    </tbody>
</table>

                                    </div>

                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div>
                                                <p class="mb-0 small text-muted">
                                                    Showing {{ $vouchers->firstItem() }} to {{ $vouchers->lastItem() }} of {{ $vouchers->total() }} results
                                                </p>
                                            </div>
                                            <div>
                                                {{ $vouchers->links() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($isOpen)
                                    <div id="myModal_payment" class="modal modal-xl fade @if($isOpen) show @endif " tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="{{ $isOpen ? 'false' : 'true' }}" style="{{ $isOpen ? 'display: block;' : '' }}">
                                        <div class="modal-dialog modal-dialog-centered model-xl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-uppercase" id="myModalLabel">
                                                        {{ $isEditing ? 'Edit Payment Voucher' : 'New Payment Voucher' }}
                                                    </h5>
                                                    <button type="button" wire:click="closeModalPV()" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    @if (session()->has('error'))
                                                        <div class="alert alert-danger alert-dismissible fade show material-shadow" role="alert">
                                                            <i class="ri-notification-off-line label-icon"></i>  {{ session('error') }}
                                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                        </div>
                                                    @endif

                                                    <form>

                                                    <div class="row mb-4">
                                                            <div class="col-6 d-none">
                                                                <label for="segment" class="form-label">Segment</label>

                                                                <select wire:model.live="selectedSegment" class="form-select" id="segment" required>
                                                                    <option value="">---Select Segment---</option>
                                                                    @foreach($segments as $segment)
                                                                        <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('selectedSegment') <span class="text-danger">{{ $message }}</span> @enderror
                                                            </div>

                                                            <div class="col-6 d-none">
                                                                <label for="cost_center" class="form-label">Cost Center</label>

                                                                <select wire:model.live="selectedCostCenter" class="form-select" id="cost_center" required>
                                                                    <option value="">---Select Cost Center---</option>
                                                                    @foreach($costCenters as $costCenter)
                                                                        <option value="{{ $costCenter->id }}">{{ $costCenter->name }}
                                                                                ({{ $costCenter->company->name }})


                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @error('selectedCostCenter') <span class="text-danger">{{ $message }}</span> @enderror
                                                            </div>

                                                    </div>


                                                        <div class="row mb-3">
                                                            <div class="col-3">
                                                                <label for="voucher_date" class="form-label">Date</label>
                                                                <input type="date" class="form-control" id="voucher_date" wire:model="voucher_date">
                                                                @error('voucher_date') <span class="text-danger">{{ $message }}</span> @enderror
                                                            </div>
                                                            <div class="col-2">
                                                                <label for="reference_number" class="form-label">Voucher No</label>
                                                                <input type="text" class="form-control" id="reference_number" wire:model="reference_number" readonly>
                                                                @error('reference_number') <span class="text-danger">{{ $message }}</span> @enderror
                                                            </div>
                                                            <div class="col-3">
                                                                <label for="paid_to" class="form-label">Payment from</label>
                                                                <select wire:model="paid_to" class="form-select" id="paid_to">
                                                                    @foreach($bankAndCashAccounts as $account)
                                                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('paid_to') <span class="text-danger">{{ $message }}</span> @enderror
                                                            </div>
                                                            <div class="col-4">
                                                                <label for="description" class="form-label">Description</label>
                                                                <input type="text" class="form-control" id="description" wire:model="description" required />
                                                                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>

                                                        <div class="table-responsive">
                                                        <table class="table table-bordered table-striped align-middle">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th style="width: 40%;">Account Title</th>
                                                                <th style="width: 15%;">Amount</th>
                                                                <th style="width: 40%;">Narration</th>
                                                                <th style="width: 5%;">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        @php
                                                                        $visibleRowIndex = 0; // Track visible rows to handle correct indexing
                                                                    @endphp
                                                            @foreach($voucherDetails as $index => $detail)
                                                                @if($detail['account_id'] !== $paid_to)
                                                                <tr wire:key="voucher-detail-{{ $index }}">
                                                                    <td>
                                                                        <select wire:model="voucherDetails.{{ $index }}.account_id" class="form-select" required>
                                                                            <option value="">---Select Account----</option>
                                                                            @foreach($accounts as $account)
                                                                                <option value="{{ $account->id }}">{{ $account->name }} [{{ $account->chartOfAccountGroup->name }}]</option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('voucherDetails.' . $index . '.account_id') <span class="text-danger">{{ $message }}</span> @enderror
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" class="form-control" wire:model.live="voucherDetails.{{ $index }}.amount" placeholder="Amount" min="0">
                                                                        @error('voucherDetails.' . $index . '.amount') <span class="text-danger">{{ $message }}</span> @enderror
                                                                    </td>
                                                                    <td>
                                                                    <input type="text" class="form-control" wire:model.live="voucherDetails.{{ $index }}.narration" placeholder="Narration (optional)">
                                                                    @error('voucherDetails.' . $index . '.narration') <span class="text-danger">{{ $message }}</span> @enderror
                                                                </td>
                                                                    <td class="text-center">
                                                                        <!-- For Editing Mode -->
                                                                        @if($isEditing)
                                                                                        @if($visibleRowIndex == 0)
                                                                                            <!-- Always show the Plus icon for the first row (no delete option) -->
                                                                                            <i class="ri-add-circle-line text-success me-2" style="cursor:pointer;font-size:20px;" wire:click="addVoucherDetail"></i>
                                                                                        @else
                                                                                            <!-- Show Minus icon for all rows except the first row -->
                                                                                            <i class="ri-delete-bin-5-line text-danger" style="cursor:pointer;font-size:20px;" wire:click="removeVoucherDetail({{ $index }})"></i>
                                                                                        @endif
                                                                                    @else
                                                                                        <!-- For Create Mode -->
                                                                                        @if($visibleRowIndex == 0)
                                                                                            <!-- Show Plus icon only for the first row in create mode -->
                                                                                            <i class="ri-add-circle-line text-success me-2" style="cursor:pointer;font-size:20px;" wire:click="addVoucherDetail"></i>
                                                                                        @endif
                                                                                        @if($visibleRowIndex > 0)
                                                                                            <!-- Show Minus icon for all rows except the first row in create mode -->
                                                                                            <i class="ri-delete-bin-5-line text-danger" style="cursor:pointer;font-size:20px;" wire:click="removeVoucherDetail({{ $index }})"></i>
                                                                                        @endif
                                                                                    @endif
                                                                    </td>
                                                                </tr>
                                                                @php
                                                                                $visibleRowIndex++; // Increment visible row index only for rows that pass the condition
                                                                            @endphp
                                                                @endif
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                        </div>

                                                        <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3">
                                                            <span class="text-dark font-size-14 font-weight-bold">Total:</span>
                                                            <span class="text-dark font-size-14 font-weight-bold">{{ number_format($totalAmountPV, 2) }}</span>
                                                        </div>

                                                        @if (env('ALLOW_UPLOAD_VOUCHER_ATTACHMENTS') === 'true')

                                                        <!-- Image Upload Field -->
                                                        <div class="row mb-3">
                                                                <div class="col-6">
                                                                    <label for="imagePV" class="form-label">Upload Image (Optional)</label>
                                                                    <input type="file" class="form-control" id="imagePV" wire:model="imagePV">
                                                                    @error('imagePV') <span class="text-danger">{{ $message }}</span> @enderror
                                                                </div>
                                                                    <div class="col-6">
                                                                    <!-- Image Preview -->
                                                                    @if ($imagePV)
                                                                        <div class="mt-2">
                                                                            <img src="{{ $imagePV->temporaryUrl() }}" alt="Image Preview" class="img-thumbnail" style="max-height: 200px;">
                                                                        </div>
                                                                    @elseif ($isEditing && $voucherId)
                                                                        @php
                                                                            $voucher = App\Models\Voucher::find($voucherId);
                                                                        @endphp
                                                                        @if ($voucher && $voucher->image_path && $imagePV)
                                                                            <div class="mt-2">
                                                                                <img src="{{ Storage::disk('spaces')->url($voucher->image_path) }}" alt="Existing Image" class="img-thumbnail" style="max-height: 200px;">
                                                                            </div>
                                                                        @endif
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif

                                                    </form>
                                                </div>
                                                <div class="d-flex justify-content-between modal-footer">
                                                    <button type="button" class="btn btn-dark" wire:click="closeModalPV()" data-bs-dismiss="modal">Cancel</button>

                                                    <!-- Disable the button and show loading text/spinner only during valid submission -->
                                                    <button type="button" id="btnCreateUpdate" class="btn btn-primary"
                                                                wire:click="{{ $isEditing ? 'updateVoucher' : 'storeVoucher' }}"
                                                                wire:loading.attr="disabled"
                                                                wire:target="{{ $isEditing ? 'updateVoucher' : 'storeVoucher' }}">

                                                            <span wire:loading.remove wire:target="{{ $isEditing ? 'updateVoucher' : 'storeVoucher' }}">
                                                                <i class="bx bx-book-open label-icon"></i> {{ $isEditing ? 'Update' : 'Create' }}
                                                            </span>

                                                            <span wire:loading wire:target="{{ $isEditing ? 'updateVoucher' : 'storeVoucher' }}">
                                                                <i class="bx bx-loader bx-spin"></i> {{ $isEditing ? 'Updating' : 'Saving' }}
                                                            </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>

$wire.on('swal:confirm-deletion-PV', ({ voucherId }) => {
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
                @this.call('deleteVoucherPV', voucherId);  // Call the delete method with the voucher ID
            }
        });
    });



    $wire.on('showModal_payment', () => {
        setTimeout(() => {
            var myModal_payment = new bootstrap.Modal(document.getElementById('myModal_payment'));
            myModal_payment.show();
        }, 50);
    });

    $wire.on('hideModal_payment', () => {
        var myModal_payment = bootstrap.Modal.getInstance(document.getElementById('myModal_payment'));
        if (myModal_payment) {
            myModal_payment.hide();
        }
    });

    document.addEventListener('alpine:init', () => {
        Alpine.data('printVoucher', () => ({
            print(url) {
                window.open(url, '_blank', 'width=800,height=600');
            }
        }))
    });
</script>
@endscript
