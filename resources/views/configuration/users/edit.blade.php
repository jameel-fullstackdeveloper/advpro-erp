@extends('layouts.master')
@section('title')
    Edit User
@endsection

@section('css')

@endsection

@section('content')

<div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                                <h4 class="mb-sm-0">Edit User</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ url('users') }}">Users</a></li>
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
                <h3>Edit User</h3>
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

                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Name:</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" name="password" id="password" class="form-control">
                        <small class="form-text text-muted">Leave blank to keep the current password</small>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password:</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                    </div>

                    <div class="mb-4">
                    <label for="companies" class="form-label">Assign Companies:</label>
                    <div class="row">
                        @foreach ($companies as $company)
                            <div class="col-auto">
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" name="companies[]" value="{{ $company->id }}" class="form-check-input" id="company-{{ $company->id }}"
                                        {{ in_array($company->id, $userCompanies) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="company-{{ $company->id }}">
                                        {{ $company->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>


                    <div class="mb-4">
                        <label for="roles" class="form-label">Assign Roles:</label>
                        <div class="d-flex flex-wrap">
                            @foreach ($roles as $role)
                                <div class="form-check me-3">
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="form-check-input"
                                        id="role-{{ $role->id }}" {{ in_array($role->name, $userRoles) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="role-{{ $role->id }}">
                                        {{ $role->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">

    </div>
</div>


@endsection

@section('script')
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
