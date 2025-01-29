@extends('layouts.master')
@section('title')
    Create User
@endsection

@section('css')

@endsection

@section('content')

<div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                                <h4 class="mb-sm-0">Create User</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ url('users') }}">Users</a></li>
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
                <h3>Create User</h3>
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

                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name:</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password:</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    </div>

                    <div class="mb-4">
                        <label for="companies" class="form-label">Assign Companies: <span class="fs-10 text-info"> (select at least one)</span></label>
                        <div class="row">
                            @foreach ($companies as $company)
                                <div class="col-auto">
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" name="companies[]" value="{{ $company->id }}" class="form-check-input" id="company-{{ $company->id }}">
                                        <label class="form-check-label" for="company-{{ $company->id }}">
                                            {{ $company->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if ($errors->has('companies'))
                            <div class="text-danger">
                                Please select at least one company.
                            </div>
                        @endif
                    </div>


                    <div class="mb-4">
    <label for="roles" class="form-label">Assign Roles: <span class="fs-10 text-info"> (select at least one)</span></label>
    <div class="row">
        @foreach ($roles as $role)
            <div class="col-auto">
                <div class="form-check form-check-inline">
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="form-check-input" id="role-{{ $role->id }}">
                    <label class="form-check-label" for="role-{{ $role->id }}">
                        {{ $role->name }}
                    </label>
                </div>
            </div>
        @endforeach
    </div>
    @if ($errors->has('roles'))
                            <div class="text-danger">
                                Please select at least one role.
                            </div>
                        @endif
</div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Create User</button>
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
