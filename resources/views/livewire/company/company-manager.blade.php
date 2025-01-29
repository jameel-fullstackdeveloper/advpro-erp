<div>
    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    <!-- Search and Add New Company -->
    <div class="row mb-3">
        <div class="col-md-6">
            <input type="text" wire:model="searchTerm" class="form-control" placeholder="Search Segments...">
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-success" wire:click="create">Add New Segment</button>
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
                <th>Logo</th>
                <th>Name</th>
                <th>STRN # / NTN #</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($companies as $company)
                <tr>
                    <td>{{ $company->id }}</td>
                    <td>
                        <img src="{{ $company->avatar ? Storage::disk('spaces')->url($company->avatar) : asset('images/logo-sm-1.png') }}"
                         alt="Avatar" class="image avatar-xs rounded-circle" style="width:60px;height:60px">

                    </td>
                    <td>
                    <span class="text-uppercase fs-12 fw-bold">
                        {{ $company->name }}</span> <br/>
                        <span class="text-primary" style="font-weight:600;">{{ $company->abv }}<span> </td>

                        <td>
                        STRN : {{ $company->strn }}<br/>
                        NTN : <br/>
                        {{ $company->ntn }}
                    </td>
                    <td>{{ $company->email }}</td>
                    <td>{{ $company->phone }}</td>


                    <td class="text-primary fs-12 text-uppercase fw-bold" >
                        {{ $company->type }}

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


    {{ $companies->links() }}
    </div>

    <!-- Modal for Add/Edit Company -->
<div wire:ignore.self class="modal fade" id="companyModal" tabindex="-1" aria-labelledby="companyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $isEditMode ? 'Edit Company' : 'Add Company' }}</h5>
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
                        <div class="col-md-6 mb-3">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" wire:model="name">
                            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="abv">Short Name <small>(Use for Vocuhers)</small></label>
                            <input type="text" class="form-control" wire:model="abv" max="3">
                            @error('abv') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" wire:model="email">
                            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone">Phone</label>
                            <input type="text" class="form-control" wire:model="phone">
                            @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="address">Address</label>
                            <input type="text" class="form-control" wire:model="address">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="strn">STRN</label>
                            <input type="text" class="form-control" wire:model="strn">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ntn">NTN</label>
                            <input type="text" class="form-control" wire:model="ntn">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="type">Type</label>
                            <select class="form-select" wire:model="type" id="type" required>
                                <option value="">Select Type</option>
                                <option value="Feed Mill">Mill</option>
                                <option value="Poultry Farm">Farm</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="avatar">Avatar</label>
                            <input type="file" class="form-control" wire:model="avatar">
                            @if ($avatar)
                                <img src="{{ $avatar->temporaryUrl() }}" alt="Avatar Preview" class="img-thumbnail mt-2" style="width: 100px; height: 100px;">
                            @endif
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
