<div>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Purchase Items</h4>
                    <button type="button" wire:click="create()" class="btn btn-success">Add New Item</button>
                </div>

                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success">
                            {{ session('message') }}
                        </div>
                    @endif

                    @if (session()->has('error'))
                                    <div class="alert alert-danger alert-dismissible fade show material-shadow" role="alert">
                                        <i class="ri-notification-off-line label-icon"></i>  {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                    @endif


                    <div class="row mb-3">
                    <div class="col-md-2 d-flex align-items-center">
                        <select wire:model="itemsPerPage" class="form-select" style="width:100px;">
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="150">150</option>
                            <option value="200">200</option>
                        </select>
                    </div>

                    <div class="col-md-3 ms-auto">
                            <div class="search-box">
                                            <input type="text" class="form-control" placeholder="Search..." wire:model.live="searchTerm" />
                                            <i class="ri-search-line search-icon"></i>
                        </div>
                    </div>
                </div>


                <div class="table-responsive">

<table class="table table-centered align-middle table-nowrap mb-0">
<thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Item Name</th>
                                <th>Group Name</th>
                                <th>Price</th>
                                <th>Opening Stock</th>
                                <th>Can Sale?</th>
                                <th>Created at</th>
                                <th>Updated at</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php $sno=1 ; @endphp
                            @foreach ($products as $product)
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td class="text-uppercase" style="word-wrap: break-word; white-space: normal;"><strong>{{ $product->name }}</strong></td>
                                    <td style="word-wrap: break-word; white-space: normal;">
                                    <span class="badge bg-info-subtle text-info text-uppercase fs-12" style="margin-right:5px;">
                                        {{ $product->itemGroup ? $product->itemGroup->name : 'No Group' }}</span></td>
                                    <td>{{ $product->purchase_price }}</td>
                                    <td>{{ number_format($product->balance,2) }}</td>
                                    <td>
                                        @if($product->can_be_sale == 1)
                                        <span class="badge bg-success-subtle text-success text-uppercase fs-12"> Yes</span>
                                        @else
                                        <span class="badge bg-danger-subtle text-danger text-uppercase fs-12"> No</span>
                                        @endif

                                    </td>

                                    <td>{{ $product->userCreated->name }} <br/>
                                    <small>{{ $product->created_at->format('d-m-Y h: i A') }}</small></td>
                                    <td>
                                        @if($product->userUpdated != NULL)
                                           {{ $product->userUpdated->name }}<br/>
                                            <small>{{ $product->updated_at->format('d-m-Y h: i A') }}</small>
                                        @endif
                                    </td>

                                    <td>

                                    <div class="d-flex justify-content-center gap-2">
                                            @can('inventory edit')
                                                <a wire:click="edit({{ $product->id }})" href="javascript:void(0);" class="link-info"><i class="ri-edit-2-line  fs-16"></i></a>
                                            @endcan

                                        @can('inventory delete')
                                            <a wire:click="confirmDeletion({{ $product->id }})" href="javascript:void(0);" href="javascript:void(0);" class="link-danger">
                                                <i class="ri-delete-bin-line  fs-16"></i></a>
                                        @endcan

                                </div>

                                       <!-- <button wire:click="edit({{ $product->id }})" class="btn btn-primary">Edit</button>
                                        <button wire:click="deleteProduct({{ $product->id }})" class="btn btn-danger">Delete</button>-->
                                    </td>
                                </tr>


                                @php  $sno = $sno + 1; @endphp


                            @endforeach
                        </tbody>
                    </table>
                                                </div>

                    <div class="d-flex justify-content-between mt-3">
                        <div>Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} results</div>
                        <div>{{ $products->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Add/Edit Product -->
    <div wire:ignore.self class="modal fade" id="myModal_item" tabindex="-1" aria-labelledby="myModal_itemLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModal_itemLabel">{{ $isEditMode ? 'Edit Item' : 'Add Item' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">


                    <form>
                        <div class="mb-3">
                            <label for="product_name">Item Name</label>
                            <input type="text" wire:model="product_name" class="form-control" id="product_name">
                            @error('product_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="group_id">Product Group</label>
                            <select wire:model="group_id" class="form-select">
                                <option value="">Select Group</option>
                                @foreach (App\Models\ItemGroup::all() as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                            @error('group_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="price">Price</label>
                            <input type="number" wire:model="price" class="form-control" id="price">
                            @error('price') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="balance">Opening Stock</label>
                            <input type="number" wire:model="balance" class="form-control" id="balance">
                            @error('balance') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="can_be_sale">Can be Sale?</label>
                           <select class="form-select" wire:model.live="can_be_sale">
                                    <option value="0"> No </option>
                                    <option value="1"> Yes </option>
                            </select>`

                            @error('can_be_sale') <span class="text-danger">{{ $message }}</span> @enderror
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
                @this.call('deleteProdcut', voucherId);  // Call the delete method with the voucher ID
            }
        });
    });

    window.addEventListener('showModal_item', event => {
        var myModal_item = new bootstrap.Modal(document.getElementById('myModal_item'));
        myModal_item.show();
    });

    window.addEventListener('hideModal_item', event => {
        var myModal_item = bootstrap.Modal.getInstance(document.getElementById('myModal_item'));
        if (myModal_item) {
            myModal_item.hide();
        }
    });
</script>
@endscript
