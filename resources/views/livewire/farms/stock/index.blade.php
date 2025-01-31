<div>
    <!-- Custom Styles -->
    <style>


</style>

<!-- Spinner element -->
<div wire:loading wire:target="store, update,delete, create, startDate, endDate, selectedCustomer, itemsPerPage,selectedFarm,selectedItem, selectedGroup, selectedBroker, serach" class="spinner"></div>

   <!-- Filters Section -->
<div class="mb-2">
    <div class="card mb-1">
        <div class="card-body mb-1">
            <table class="table table-centered align-middle table-nowrap mb-0">
                <tbody>
                    <tr>
                        <td style="width:100px">
                            <select wire:model.live="itemsPerPage" id="itemsPerPage" class="form-control form-select">
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="150">150</option>
                                <option value="200">200</option>
                            </select>
                        </td>

                        <td  style="width:120px">
                            <input type="date" wire:model.live="startDate" id="startDate" class="form-control" placeholder="Start Date">
                        </td>

                        <td  style="width:120px">
                            <input type="date" wire:model.live="endDate" id="endDate" class="form-control" placeholder="End Date">
                        </td>

                        <td>
                            <select wire:model.live="selectedFarm" id="selectedFarm" class="form-control form-select">
                                <option value="">-- Select Farm --</option>
                                @foreach($farms as $farm)
                                    <option value="{{ $farm->id }}">{{ $farm->name }}</option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <select wire:model.live="selectedProduct" id="selectedProduct" class="form-control form-select">
                                <option value="">-- Select Item --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </td>


                        <td class="d-none">
                             <input type="text" wire:model.live="searchTerm" id="searchTerm" class="form-control" placeholder="Search">
                        </td>



                        <td class="text-end">
                        @can('sales invocies create')
                            <a href="{{ url('farms/stock/create') }}" class="btn btn-success btn-label waves-effect waves-light">
                                <i class="bx bx-alarm-add label-icon align-middle fs-16"></i> New
                            </a>
                        @endcan
                        </td>

                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



    <!-- Invoices Table -->
<div class="card">
    <div class="card-body">
    <div class="table-responsive">
    <table class="table table-centered align-middle table-nowrap mb-0">
        <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Farm Name</th>
                    <th>Product(s)</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>


            <tbody>
                @php
                    $totalQuantity= 0;
                    $totalNetAmount = 0;
                @endphp

                @php $sno=1 ; @endphp
            @foreach($invoices as $invoice)

            <tr>
                <td>{{ $invoice->id }}</td>
                <td>{{ \Carbon\Carbon::parse($invoice->transfer_date)->format('d-m-Y') }}</td>

                <td><span class="text-info text-uppercase fw-bold">{{ $invoice->farm->name ?? 'N/A' }}</span></td> <!-- Display Farm Name -->
                <td>
                    @foreach($invoice->items as $item)
                        <div class="mb-1">
                            {{ $item->product->name ?? 'N/A' }}
                        </div>
                    @endforeach
                </td>
                <td>
                @foreach($invoice->items as $item)
                        <div class="mb-1">
                        {{ number_format($item->unit_price,2) }}
                        </div>
                    @endforeach


                </td>

                <td>
                @foreach($invoice->items as $item)
                        <div class="mb-1">
                        {{ number_format($item->quantity,2) }}
                        @php  $totalQuantity += $item->quantity; @endphp
                        </div>
                    @endforeach

                   </td>
                <td>

                @foreach($invoice->items as $item)
                        <div class="mb-1">
                        {{ number_format($item->net_amount,2) }}
                        @php  $totalNetAmount += $item->net_amount; @endphp
                        </div>
                    @endforeach
                    </td>

                <td>

                    <div class="hstack gap-2 flex-wrap">
                            @can('customers edit')
                            <a wire:click="edit({{ $invoice->id }})" href="javascript:void(0);" class="link-success fs-15"><i class="ri-edit-2-line" style="font-size:18px;"></i></a>
                            @endcan

                            @can('customers delete')
                                    <a wire:click="confirmDeletion({{ $invoice->id }})" href="javascript:void(0);" class="link-danger">
                                        <i class="ri-delete-bin-line" style="font-size:16px;"></i>
                                     </a>
                            @endcan

                                <div class="dropdown">
                                                                <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="true" class="show">
                                                                    <i class="ri-more-2-fill text-muted"></i>
                                                                </a>

                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1" data-popper-placement="bottom-start" data-popper-escaped="" style="position: absolute; inset: 0px auto auto 0px; margin: 0px; transform: translate3d(-134.4px, 20.8px, 0px);">

                                                                    <li><a class="dropdown-item" href="#">Created: {{ $invoice->createdBy->name }}, {{ $invoice->created_at->format('d-m-Y h: i A') }}</a></li>

                                                                    @if($invoice->updatedBy != NULL)
                                                                    <li><a class="dropdown-item" href="#">Updated: {{ $invoice->updatedBy->name }}, {{ $invoice->updated_at->format('d-m-Y h: i A') }}</a></li>
                                                                    @endif
                                                                </ul>
                                            </div>
                        </div>

                </td>
            </tr>

            @endforeach




            </tbody>
                <tfoot class="table-info">
                    <tr>
                        <th colspan="3" class="text-center">Total:</th>
                        <th>-</th>
                        <th>-</th>
                        <th>{{ number_format($totalQuantity,2) }}</th>
                        <th>{{ number_format($totalNetAmount,2) }}</th>

                        <th></th>
                    </tr>
                </tfoot>
        </table>
    </div>
        <!-- Pagination -->
        <div class="d-flex justify-content-between card-body mb-0 pb-0">
                    <div>
                        Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} results
                    </div>
                    <div>
                        {{ $invoices->links() }}
                    </div>
                </div>
    </div>
</div>



<div>




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

      window.addEventListener('showModal_invoice', event => {
        var myModal_invoice = new bootstrap.Modal(document.getElementById('modal_invoice'));
        myModal_invoice.show();
    });

    window.addEventListener('hideModal_invoice', event => {
        var myModal_invoice = bootstrap.Modal.getInstance(document.getElementById('modal_invoice'));
        if (myModal_invoice) {
            myModal_invoice.hide();
        }
    });


</script>

@endscript
