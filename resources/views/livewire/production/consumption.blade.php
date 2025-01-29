<div>

<!-- Spinner element -->
<div wire:loading wire:target="startDate, endDate, searchTerm,product_id" class="spinner"></div>


   <!-- Filters Section -->
<div class="mb-2">
    <div class="card mb-1">
        <div class="mb-1">
            <table class="table table-centered align-middle table-nowrap mb-0">
                <tbody>
                    <tr>
                        <td style="width:95px">
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

                        <td  style="width:420px">

                             <select wire:model.live="product_id" id="product_id" class="form-control form-select">
                             <option value="">--Select Prodcut ---</option>
                             @foreach($finished_Products as $pro)
                                <option value="{{ $pro->id}}"> {{ $pro->name }}</option>
                            @endforeach

                            </select>
                        </td>

                        <td>
                            <input type="text" wire:model.live="searchTerm" id="searchTerm" class="form-control" placeholder="Search">
                        </td>



                        <td class="text-end">
                            <a href="{{ route('productions.create') }}" class="btn btn-success btn-label waves-effect waves-light">
                                <i class="bx bx-alarm-add label-icon align-middle fs-16"></i> New
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="row mb-0">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle text-center">
                <thead class="table-info">
                    <tr>
                        <th>Batch Executed</th>
                        <th>Expected Production </th>
                        <th class="text-danger">Shortage</th>
                        <th class="text-success">Excess</th>
                        <th>Actual Prodcution</th>

                    </tr>
                </thead>
                <tbody class="table-light">
                    <tr>
                        <td class="fs-14 fw-bold"><strong>{{ number_format($totalBatchExecuted) }}</strong></td>
                        <td class="fs-14 fw-bold">{{ number_format($totalExpectedBags) }} <small>Bags</small></td>
                        <td class="fs-14 fw-bold text-danger">{{ number_format($totalShortageBags) }} <small>Bags</small></td>
                        <td class="fs-14 fw-bold text-success">{{ number_format($totalExcessBags) }} <small>Bags</small></td>
                        <td class="fs-14 fw-bold">{{ number_format($totalActualProduction) }} <small>Bags</small></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>




<div class="row">
    <div class="col-12">
        <!-- Invoices Table -->
        <div class="card">
            <div class="card-body">

            <div class="table-responsive">

            <table class="table table-centered align-middle table-nowrap mb-0">
                    <thead class="table-light">
                       <tr>
                            <th>#</th>
                            <th >Prodcution <br/> <small class="text-muted"> Date</small></th>
                            <th>Product <br/> <small class="text-muted"> Produced</small></th>
                            <th class="text-center">Batch <br/><small class="text-muted">Executed</small></th>
                            <th  class="text-center">Expected <br/> <small class="text-muted">Bags</small></th>
                            <th  class="text-center">Shortage <br/> <small class="text-muted">Bags</small></th>
                            <th  class="text-center">Excess <br/> <small class="text-muted">Bags</small></th>
                            <th  class="text-center">Actual <br/> <small class="text-muted">Bags</small></th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($productions as $production)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($production->production_date)->format('d-m-Y, l') }}</td>
                            <td class="text-success"> <strong>{{ $production->product ? $production->product->name : 'N/A' }} </strong></td>
                            <td class="text-center"> {{ number_format($production->lots) }}</td>
                            <td class="text-center"> {{ number_format($production->defaultbags_perlot) }}</td>
                            <td class="text-center"> {{ number_format($production->short_perlot) }}</td>
                            <td class="text-center"> {{ number_format($production->excess_perlot) }}</td>
                            <td class="text-center"> {{ number_format($production->actual_produced) }}</td>
                                <td class="text-center"> <!-- Use text-center to align the action items -->
                                    <div class="hstack gap-2 justify-content-center"> <!-- Use justify-content-center to ensure items are centered -->
                                        <a href="{{ route('productions.edit', $production->id) }}" class="link-success fs-15" title="Edit Invoice">
                                            <i class="ri-edit-2-line" style="font-size:16px;"></i>
                                        </a>

                                        <a wire:click="confirmDeletion({{ $production->id }})" href="javascript:void(0);"
                                                        class="link-danger fs-15"><i class="ri-delete-bin-line"></i>
                                        </a>

                                        <form action="{{ route('consumption.print', $production->id) }}" method="POST" style="display:inline;" target="_blank">
                                            @csrf
                                            <button type="submit" class="link-dark" title="Print" style="border: none; background-color: white;">
                                                <i class="ri-printer-line" style="font-size:16px;"></i>
                                            </button>
                                        </form>

                                            <div class="dropdown">
                                                <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ri-more-2-fill text-muted"></i>
                                                </a>

                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                                        <li>
                                            <a class="dropdown-item" href="#">Created: {{ $production->createdBy->name }}, {{ $production->created_at->format('d-m-Y h:i A') }}</a>
                                        </li>

                                        @if($production->updatedBy)
                                        <li>
                                            <a class="dropdown-item" href="#">Updated: {{ $production->updatedBy->name }}, {{ $production->updated_at->format('d-m-Y h:i A') }}</a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>

                                    </div>
                                </td>
                        </tr>
                        @empty
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <h4 class="text-warning fs-16"> <i class=" ri-information-line">  </i>No record found. </h4></td>
                                </tr>
                            @endforelse
                    </tbody>
                </table>
            </div>


             <!-- Pagination -->
        <div class="d-flex justify-content-between card-body mb-0 pb-0">
                    <div>
                        Showing {{ $productions->firstItem() }} to {{ $productions->lastItem() }} of {{ $productions->total() }} results
                    </div>
                    <div>
                        {{ $productions->links() }}
                    </div>
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
                @this.call('deleteProduction', voucherId);  // Call the delete method with the voucher ID
            }
        });
    });

</script>
@endscript
