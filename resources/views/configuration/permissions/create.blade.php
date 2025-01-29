@extends('layouts.master')
@section('title')
    Create Permission
@endsection

@section('css')

@endsection

@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h4 class="mb-sm-0">Create Permission</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ url('permissions') }}">Permissions</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>

        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3>Create Permission</h3>
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

                <form action="{{ route('permissions.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="module" class="form-label">Module:</label>
                        <select name="module" id="module" class="form-control" required>
                            <option value="" disabled selected>Select Module</option>
                            <option value="customers" {{ old('module') == 'customers' ? 'selected' : '' }}>Sales->Customers</option>
                            <option value="sales brokers" {{ old('module') == 'sales brokers' ? 'selected' : '' }}>Sales->Brokers</option>
                            <option value="sales orders" {{ old('module') == 'sales orders' ? 'selected' : '' }}>Sales->Orders</option>
                            <option value="sales invocies" {{ old('module') == 'sales invocies' ? 'selected' : '' }}>Sales->Invocies</option>
                            <option value="sales return" {{ old('module') == 'sales-return' ? 'selected' : '' }}>Sales->Return</option>

                            <option value="purchases vendors" {{ old('module') == 'purchases vendors' ? 'selected' : '' }}>Purchases->Vendors</option>
                            <option value="purchases brokers" {{ old('module') == 'purchases brokers' ? 'selected' : '' }}>Purchases->Brokers</option>
                            <option value="purchases orders" {{ old('module') == 'purchases orders' ? 'selected' : '' }}>Purchases->Orders</option>
                            <option value="purchases bills" {{ old('module') == 'purchases bills' ? 'selected' : '' }}>Purchases->Bills</option>
                            <option value="purchases return" {{ old('module') == 'purchases return' ? 'selected' : '' }}>Purchases->Return</option>


                            <option value="accounting chart of account" {{ old('module') == 'accounting chart of account' ? 'selected' : '' }}>Accounting->Chart of Account</option>
                            <option value="accounting cashbook" {{ old('module') == 'accounting cashbook' ? 'selected' : '' }}>Accounting->Cash Book</option>
                            <option value="accounting bankbook" {{ old('module') == 'accounting bankbook' ? 'selected' : '' }}>Accounting->Bank Book</option>
                            <option value="accounting journalvoucher" {{ old('module') == 'accounting journalvoucher' ? 'selected' : '' }}>Accounting->Journal Vocuher</option>
                            <option value="accounting ledgers" {{ old('module') == 'accounting ledgers' ? 'selected' : '' }}>Accounting->Ledgers</option>
                            <option value="accounting trialbalance" {{ old('module') == 'accounting trialbalance' ? 'selected' : '' }}>Accounting->Trial Balance</option>

                            <option value="inventory" {{ old('module') == 'inventory' ? 'selected' : '' }}>Inventory</option>
                            <option value="weighbridge" {{ old('module') == 'weighbridge' ? 'selected' : '' }}>Weighbridge</option>
                            <option value="reports" {{ old('module') == 'reports' ? 'selected' : '' }}>Reports</option>
                            <option value="users" {{ old('module') == 'users' ? 'selected' : '' }}>Configuration->Users</option>
                            <option value="roles" {{ old('module') == 'roles' ? 'selected' : '' }}>Configuration->Roles</option>
                            <option value="permissions" {{ old('module') == 'permissions' ? 'selected' : '' }}>Configuration->Permissions</option>
                            <!-- Add more categories as needed -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="permissions" class="form-label">Permissions:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="view" id="view" {{ is_array(old('permissions')) && in_array('view', old('permissions')) ? 'checked' : '' }}>
                            <label class="form-check-label" for="view">View</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="create" id="create" {{ is_array(old('permissions')) && in_array('create', old('permissions')) ? 'checked' : '' }}>
                            <label class="form-check-label" for="create">Create</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="edit" id="edit" {{ is_array(old('permissions')) && in_array('edit', old('permissions')) ? 'checked' : '' }}>
                            <label class="form-check-label" for="edit">Edit</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="delete" id="delete" {{ is_array(old('permissions')) && in_array('delete', old('permissions')) ? 'checked' : '' }}>
                            <label class="form-check-label" for="delete">Delete</label>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Create Permission</button>
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
