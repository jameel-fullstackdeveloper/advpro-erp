@extends('layouts.master')
@section('title')
    Edit Permission
@endsection

@section('css')

@endsection

@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h4 class="mb-sm-0">Edit Permission</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ url('permissions') }}">Permissions</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>

        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3>Edit Permission</h3>
            </div>

            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('permissions.update', $permission->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="module" class="form-label">Module:</label>
                        <select name="module" id="module" class="form-control" required>
                                <option value="" disabled>Select Module</option>
                                <option value="customers" {{ $module == 'customers' ? 'selected' : '' }}>Sales->Customers</option>
                                <option value="sales brokers" {{ $module == 'sales brokers' ? 'selected' : '' }}>Sales->Brokers</option>
                                <option value="sales orders" {{ $module == 'sales orders' ? 'selected' : '' }}>Sales->Orders</option>
                                <option value="sales invocies" {{ $module == 'sales invocies' ? 'selected' : '' }}>Sales->Invocies</option>
                                <option value="sales return" {{ $module == 'sales-return' ? 'selected' : '' }}>Sales->Return</option>

                                <option value="purchases vendors" {{ $module == 'purchases vendors' ? 'selected' : '' }}>Purchases->Vendors</option>
                                <option value="purchases brokers" {{ $module == 'purchases brokers' ? 'selected' : '' }}>Purchases->Brokers</option>
                                <option value="purchases orders" {{ $module == 'purchases orders' ? 'selected' : '' }}>Purchases->Orders</option>
                                <option value="purchases bills" {{ $module == 'purchases bills' ? 'selected' : '' }}>Purchases->Bills</option>
                                <option value="purchases return" {{ $module == 'purchases return' ? 'selected' : '' }}>Purchases->Return</option>

                                <option value="accounting chart of account" {{ $module == 'accounting chart of account' ? 'selected' : '' }}>Accounting->Chart of Account</option>
                                <option value="accounting cashbook" {{ $module == 'accounting cashbook' ? 'selected' : '' }}>Accounting->Cash Book</option>
                                <option value="accounting bankbook" {{ $module == 'accounting bankbook' ? 'selected' : '' }}>Accounting->Bank Book</option>
                                <option value="accounting journalvoucher" {{ $module == 'accounting journalvoucher' ? 'selected' : '' }}>Accounting->Journal Voucher</option>
                                <option value="accounting ledgers" {{ $module == 'accounting ledgers' ? 'selected' : '' }}>Accounting->Ledgers</option>
                                <option value="accounting trialbalance" {{ $module == 'accounting trialbalance' ? 'selected' : '' }}>Accounting->Trial Balance</option>

                                <option value="inventory" {{ $module == 'inventory' ? 'selected' : '' }}>Inventory</option>
                                <option value="weighbridge" {{ $module == 'weighbridge' ? 'selected' : '' }}>Weighbridge</option>
                                <option value="reports" {{ $module == 'reports' ? 'selected' : '' }}>Reports</option>
                                <option value="users" {{ $module == 'users' ? 'selected' : '' }}>Configuration->Users</option>
                                <option value="roles" {{ $module == 'roles' ? 'selected' : '' }}>Configuration->Roles</option>
                                <option value="permissions" {{ $module == 'permissions' ? 'selected' : '' }}>Configuration->Permissions</option>
                                <!-- Add more categories as needed -->
                            </select>

                    </div>

                    <div class="mb-3">
                        <label for="permissions" class="form-label">Permissions:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="view" id="view" {{ in_array('view', $selectedPermissions) ? 'checked' : '' }}>
                            <label class="form-check-label" for="view">View</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="create" id="create" {{ in_array('create', $selectedPermissions) ? 'checked' : '' }}>
                            <label class="form-check-label" for="create">Create</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="edit" id="edit" {{ in_array('edit', $selectedPermissions) ? 'checked' : '' }}>
                            <label class="form-check-label" for="edit">Edit</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="delete" id="delete" {{ in_array('delete', $selectedPermissions) ? 'checked' : '' }}>
                            <label class="form-check-label" for="delete">Delete</label>
                        </div>
                    </div>


                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Update Permission</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-4">
    </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
