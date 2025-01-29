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
<div wire:loading wire:target="store, update,deleteCustomer,itemsPerPage,searchTerm,filter,imagePV" class="spinner"></div>

    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                     <div class="row">
                        <div class="col-2 d-flex align-items-center">
                            <label class="me-2 mb-0" for="itemsPerPage_jv">Show</label>
                            <select wire:model.live="itemsPerPage" class="form-select" id="itemsPerPage_jv" style="width: 80px;">
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="150">150</option>
                                <option value="200">200</option>
                            </select>
                            <label class="ms-2 mb-0" for="searchTerm">Entries</label>
                        </div>

                        <div class="col-5">
                                <div class="search-box">
                                    <input type="text" class="form-control" id="searchTerm" placeholder="Serach by voucher number or account title ..." wire:model.live="searchTerm">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                        </div>

                        <div class="col-2">
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

                        <div class="col-3" style="text-align:right">
                        <button type="button" wire:click="createVoucher()" class="btn btn-success btn-label">
                        <i class="ri-add-circle-line label-icon align-middle fs-16 me-2"></i> Add Journal Voucher</button>
                        </div>

		</div>
</div>
</div>
</div>
</div>


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


                <div class="table-responsive">
                <table class="table align-middle table-nowrap mb-0">
                <thead class="table-light">
                            <tr>
                                <!--<th>ID</th>-->
                                <th>Date</th>
                                <th>Voucher</th>
                                <th>Accounts</th>
                                <th>Description</th>
                                <th>Debit</th>
                                <th>Credit</th>

                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>

                        @php
                $totalDebitrow = 0; // Initialize total debit
                $totalCreditrow = 0; // Initialize total credit
            @endphp


                        @forelse ($vouchers as $voucher)
    @foreach ($voucher->voucherDetails as $detail)
        <tr>
            @if ($loop->first)
                <!-- Only show the voucher number and date on the first row -->
                <td rowspan="{{ $voucher->voucherDetails->count() }}">
                <span class="fw-medium link-muted">{{ $voucher->reference_number }}</span></td>
                <td rowspan="{{ $voucher->voucherDetails->count() }}">{{ \Carbon\Carbon::parse($voucher->date)->format('d-M-Y') }}</td>
            @endif

            <!-- Show the account name -->
            <td class="{{ $detail->type == 'debit' ? 'text-success' : 'text-danger' }}" style="word-wrap: break-word; white-space: normal;">
                <strong>{{ $detail->account->name }}</strong>
            </td>

            <!-- Show narration for each voucher detail row -->
            <td style="word-wrap: break-word; white-space: normal;"> {{ $detail->narration ?? '' }}</td> <!-- Display the narration here -->


            <td>
                            @php
                                $debitAmount = $detail->type == 'debit' ? $detail->amount : 0;
                                $totalDebitrow += $debitAmount;
                            @endphp
                            {{ number_format($debitAmount) }}
                        </td>
                        <td>
                            @php
                                $creditAmount = $detail->type == 'credit' ? $detail->amount : 0;
                                $totalCreditrow += $creditAmount;
                            @endphp
                            {{ number_format($creditAmount) }}
                        </td>

            @if ($loop->first)


            @endif


            <!-- Action buttons (only on the first row) -->
            @if ($loop->first)
                <td rowspan="{{ $voucher->voucherDetails->count() }}">
                <div class="d-flex justify-content-end gap-1">
                    @can('accounting journalvoucher edit')
                        <a wire:click="editVoucher({{ $voucher->id }})" href="javascript:void(0);" class="link-info">
                            <i class="ri-edit-2-line" style="font-size:16px;"></i>
                        </a>
                    @endcan

                    @can('accounting journalvoucher delete')
                        <a wire:click="confirmDeletionJV({{ $voucher->id }})" href="javascript:void(0);" class="link-danger">
                            <i class="ri-delete-bin-line" style="font-size:16px;"></i>
                        </a>

                    @endcan

                    <a @click="window.open('{{ route('voucher.jvprint', ['id' => $voucher->id]) }}', '_blank')" class="link-primary" href="javascript:void(0);">
                        <i class="ri-printer-line" style="font-size:16px;"></i>
                    </a>


                    <div class="dropdown">
                                        <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="true" class="show">
                                            <i class="ri-more-2-fill text-muted"></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                                            <li><a class="dropdown-item" href="#">Created: {{ $voucher->createdBy->name }}, {{ $voucher->created_at->format('d-m-Y h:i A') }}</a></li>
                                            @if($voucher->updatedBy != NULL)
                                                <li><a class="dropdown-item" href="#">Updated: {{ $voucher->updatedBy->name }}, {{ $voucher->updated_at->format('d-m-Y h:i A') }}</a></li>
                                            @endif
                                        </ul>
                                    </div>
                    </div>

                        @can('journalvoucher delete')
                                <script>
                                    function confirmDeletionAccount{{ $voucher->id }}(accountId) {
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
            @endif
        </tr>
    @endforeach
@empty
    <tr>
        <td colspan="8">No vouchers found.</td>
    </tr>
@endforelse



        <tfoot class="table-light">
            <tr>
                <td colspan="4" style="font-weight:bold;text-align:center">Total</td>
                <td style="font-weight:bold;">{{ number_format($totalDebitrow) }}</td>
                <td style="font-weight:bold;">{{ number_format($totalCreditrow) }}</td>
                <td></td>

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
                        <div id="myModal_journal" class="modal modal-xl fade @if($isOpen) show @endif " tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="{{ $isOpen ? 'false' : 'true' }}" style="{{ $isOpen ? 'display: block;' : '' }}">
                            <div class="modal-dialog modal-dialog-centered model-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title text-uppercase" id="myModalLabel">
                                        {{ $isEditing ? 'Edit Journal Voucher' : 'New Journal Voucher' }} </h5>
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
                                            <input type="text" class="form-control" id="reference_number" wire:model="reference_number"
                                            readonly style="">

                                            @error('reference_number') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="col-7">
                                            <label for="description" class="form-label">Description</label>
                                            <input type="text" class="form-control" id="description" wire:model="description" required />
                                            @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                </div>

                <div class="table-responsive">
                <table class="table table-bordered  table-striped align-middle">
        <thead class="table-light">
            <tr>
                <th>Account Title</th>
                <th style="width: 10%;">Debit</th>
                <th style="width: 10%;">Credit</th>
                <th style="width: 50%;">Narration</th>
                <th style="width: 10%;">Actions</th>
            </tr>
        </thead>
        <tbody>
        @php
                                                                        $visibleRowIndex = 0; // Track visible rows to handle correct indexing
                                                                    @endphp
            @foreach($voucherDetails as $index => $detail)
            <tr wire:key="voucher-detail-{{ $index }}">
                <td style="width: 36%;">
                    <select wire:model="voucherDetails.{{ $index }}.account_id" class="form-select select2" required>
                        <option value="">---Select Account----</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }} [{{ $account->chartOfAccountGroup->name }}]</option>
                        @endforeach
                    </select>
                    @error('voucherDetails.' . $index . '.account_id') <span class="text-danger">{{ $message }}</span> @enderror
                </td>
                <td style="width: 12%;">
                    <input type="number" class="form-control" wire:model.live="voucherDetails.{{ $index }}.debit_amount" placeholder="Debit" min="0"  @if($voucherDetails[$index]['credit_amount'] > 0) readonly @endif>
                    @error('voucherDetails.' . $index . '.amount') <span class="text-danger">{{ $message }}</span> @enderror
                </td>
                <td style="width: 12%;">
                    <input type="number" class="form-control" wire:model.live="voucherDetails.{{ $index }}.credit_amount" placeholder="Credit" min="0" @if($voucherDetails[$index]['debit_amount'] > 0) readonly @endif>
                    @error('voucherDetails.' . $index . '.amount') <span class="text-danger">{{ $message }}</span> @enderror
                </td>
                <td style="width: 30%;">
                    <input type="text" class="form-control" wire:model="voucherDetails.{{ $index }}.narration" placeholder="Optional" maxlength="100">
                    @error('voucherDetails.' . $index . '.narration') <span class="text-danger">{{ $message }}</span> @enderror
                </td>
                <td class="text-center" style="width: 10%;">
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
            @endforeach
        </tbody>
    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3">
                    <span class="text-dark font-size-14 font-weight-bold">Total:</span>
                    <div>
                        <span class="text-dark font-size-14 font-weight-bold me-3">Debit:<span>{{ number_format($totalDebit, 2) }}</span></span>
                        <span class="text-dark font-size-14 font-weight-bold">Credit: <span>{{ number_format($totalCredit, 2) }}</span></span>
                    </div>
                    <span class="text-dark font-size-14 font-weight-bold">Balance: <span id="balance">{{ number_format($balance, 2) }}</span></span>
                </div>

                </form>
            </div>

                <div class="d-flex justify-content-between modal-footer">
                    <button type="button" class="btn btn-dark" wire:click="closeModal()" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="btnCreateUpdate" class="btn btn-primary"
                            wire:click="{{ $isEditing ? 'updateVoucher' : 'storeVoucher' }}" @disabled($balance !== 0.00)>
                        <i class="bx bx-book-open label-icon"></i> {{ $isEditing ? 'Update' : 'Create' }}
                    </button>
                </div>

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
</div>
</div>
</div>


@script
<script>

$wire.on('swal:confirm-deletion-JV', ({ voucherId }) => {
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
                @this.call('deleteVoucherJV', voucherId);  // Call the delete method with the voucher ID
            }
        });
    });

    $wire.on('showModal_journal', () => {
        setTimeout(() => {
            var myModal_account = new bootstrap.Modal(document.getElementById('myModal_journal'));
            myModal_account.show();
        }, 50);
    });

    $wire.on('hideModal_journal', () => {
        var myModal_account = bootstrap.Modal.getInstance(document.getElementById('myModal_journal'));
        if (myModal_account) {
            myModal_account.hide();
        }
    });

    document.addEventListener('alpine:init', () => {
        Alpine.data('printVoucher', () => ({
            print(url) {
                window.open(url, '_blank', 'width=800,height=600');
            }
        }))
    })


</script>

@endscript
