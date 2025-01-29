<div>
    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif


    <!-- Search and Add New Company -->
    <div class="row mb-3">
        <div class="col-2 d-flex align-items-center">
                            <label class="me-2 mb-0" for="itemsPerPage_rv">Show</label>
                            <select wire:model.live="itemsPerPage" class="form-select" id="itemsPerPage_rv" style="width: 80px;">
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="150">150</option>
                                <option value="200">200</option>
                            </select>
                            <label class="ms-2 mb-0" for="searchTerm">Entries</label>
                        </div>

        <div class="col-md-6">
            <input type="text" wire:model.live="searchTerm" class="form-control" placeholder="Search Cost Center, Farms, Segments...">
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-success" wire:click="create">Add New Cost Center</button>
        </div>
    </div>


    <div class="table-responsive">

    @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show material-shadow" role="alert">
                            <i class="ri-notification-off-line label-icon"></i>  {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif


    <!-- Company List Table -->
    <table class="table table-centered align-middle table-nowrap mb-0">
       <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Cost Center</th>
                <th>Address</th>
                <th>Description</th>
                <!--<th>Opening Date</th>
                <th>Closing Date</th>-->
                <th>Status</th>
                <th>Segment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($companies as $company)
                <tr>
                    <td class="text-nowrap">{{ $company->id }}</td>

                    <td class="text-nowrap">
                        <span class="text-uppercase fs-12 fw-bold">
                        {{ $company->name }}</span> <br/>
                        {{ $company->abv }}
                    </td>

                    <td class="text-nowrap">{{ $company->address }}</td>

                    <td class="text-nowrap">{{ $company->description }}</td>
                    <!--<td class="text-nowrap">{{ \Carbon\Carbon::parse($company->opening_date)->format('d-m-Y') }}</td>
                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($company->closing_date)->format('d-m-Y') }}</td> -->
                    <td class="text-nowrap">
                        @if($company->status == 1)
                        <span class="badge bg-success-subtle text-success" >Active</span>
                        @elseif($company->status == 2)
                        <span class="badge bg-danger-subtle text-danger" >Inactive</span>
                        @else
                            {{ $company->status }} <!-- Optionally handle other cases -->
                        @endif
                    </td>

                    <td class="text-nowrap">
                         {{ $company->company ? $company->company->name : 'N/A'  }}
                    </td>

                    <td>
                       <div class="hstack gap-3 flex-wrap">
                            <a wire:click="edit({{ $company->id }})" href="javascript:void(0);" class="link-success fs-15"><i class="ri-edit-2-line" style="font-size:18px;"></i></a>

                            @if($company->id == 1)
                                <i class="ri-lock-2-line"> </i>
                            @else
                                <a onclick="confirmDeletionCustomer{{ $company->id }}({{ $company->id }})" href="javascript:void(0);" class="link-danger fs-15">
                                    <i class="ri-delete-bin-line" style="font-size:18px;"></i></a>
                            @endif

                        </div>

                                        <script>
                                                    function confirmDeletionCustomer{{ $company->id }}(accountId) {
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
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>



    <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div>
                                                <p class="mb-0 small text-muted">
                                                    Showing {{ $companies->firstItem() }} to {{ $companies->lastItem() }} of {{ $companies->total() }} results
                                                </p>
                                            </div>
                                            <div>
                                                {{ $companies->links() }}
                                            </div>
                                        </div>


</div>


    <!-- Modal for Add/Edit Company -->
<div wire:ignore.self class="modal fade" id="companyModal" tabindex="-1" aria-labelledby="companyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                @if($segment->type == 'Feed Mill')
                    <h5 class="modal-title">{{ $isEditMode ? 'Edit Cost Center' : 'Add Cost Center' }}</h5>
                @elseif($segment->type == 'Poultry Farm')
                    <h5 class="modal-title">{{ $isEditMode ? 'Edit Farm' : 'Add New Farm' }}</h5>
                @else
                    <h5 class="modal-title">{{ $isEditMode ? 'Edit Cost Center' : 'Add Cost Center' }}</h5>
                @endif

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                     @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show material-shadow" role="alert">
                            <i class="ri-notification-off-line label-icon"></i>  {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                <form>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="name">Cost Center Name</label>
                            <input type="text" class="form-control" wire:model="name">
                            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="abv">Short Name <small></small></label>
                            <input type="text" class="form-control" wire:model="abv" max="5">
                            @error('abv') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="abv">Segments <small></small></label>
                                <select wire:model="segment_id" class="form-select" {{ $segment->type == 'Feed Mill' ? '' : 'disabled' }} required>
                                        <option value="">---Select Segment---</option>
                                        @foreach (App\Models\Company::all() as $group)
                                            <option value="{{ $group->id }}" >{{ $group->name }}</option>
                                        @endforeach
                                </select>
                                @error('segment_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                    </div>

                    <div class="row">



                        <div class="col-md-8 mb-3">
                            <label for="name">Address</label>
                            <input type="text" class="form-control" wire:model="address">
                            @error('address') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="name">Description</label>
                            <input type="text" class="form-control" wire:model="description">
                            @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-4 mb-3 d-none">
                            <label for="opening_date">Opening Date <small></small></label>
                            <input type="date" class="form-control" wire:model="opening_date" reqruied >
                            @error('opening_date') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-4 mb-3 d-none">
                            <label for="closing_date">Closing Date <small></small></label>
                            <input type="date" class="form-control" wire:model="closing_date" required >
                            @error('closing_date') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="name">Status</label>
                                <select wire:model="status" class="form-select" reqruied>
                                    <option value="">---Select Status---</option>
                                    <option value="1">Active</option>
                                    <option value="2">Inactive </option>
                                </select>
                        </div>



                    </div>


                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" wire:click="store">{{ $isEditMode ? 'Update' : 'Save' }}</button>
            </div>
        </div>
    </div>
</div>


    <!-- Modal Trigger -->
    <script>
        window.addEventListener('showModal', event => {
            var modal = new bootstrap.Modal(document.getElementById('companyModal'));
            modal.show();
        });

        window.addEventListener('hideModal', event => {
            var modal = bootstrap.Modal.getInstance(document.getElementById('companyModal'));
            modal.hide();
        });
    </script>
</div>
