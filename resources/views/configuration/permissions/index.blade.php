@extends('layouts.master')
@section('title')
    Permissions
@endsection

@section('css')
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h4 class="mb-sm-0">Permissions</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ url('permissions') }}">Permissions</a></li>
                    <li class="breadcrumb-item active">List</li>
                </ol>
            </div>
        </div>
    </div>
</div>

@if (session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show material-shadow" role="alert">
        <i class="ri-notification-off-line label-icon"></i>  {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show material-shadow" role="alert">
        <i class="ri-notification-off-line label-icon"></i>  {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">All Permissions</h4>
                <div class="flex-shrink-0">
                    <a href="{{ route('permissions.create') }}" class="btn btn-primary add-btn">
                        Create New Permission
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div id="permissionList">
                    <div class="row g-4 mb-3">
                        <div class="col-sm-auto d-flex align-items-center">
                            <label for="perPageSelect" class="me-2">Show:</label>
                            <div>
                                <select id="perPageSelect" class="form-select">
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="150">150</option>
                                    <option value="200">200</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm">
                            <div class="d-flex justify-content-sm-end">
                                <div class="search-box ms-2">
                                    <input type="text" class="form-control search" placeholder="Search..." id="search" name="search">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                         <table class="table table-centered align-middle table-nowrap mb-0" id="permissionTable">
                            <thead class="table-light">
                            <tr>
                                <th class="sort" data-sort="id">#</th>
                                <th class="sort" data-sort="module">Module</th>
                                <th class="sort" data-sort="permissions">Permissions</th>
                                <th class="sort" data-sort="created_at">Created at</th>
                                <th class="sort" data-sort="actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                        @php $id= 1 ; @endphp
                            @foreach ($groupedPermissions as $module => $permissions)

                            <tr>
                                <td class="id"> {{ $id }}</td>
                                <td class="module">
                                    <strong>{{ ucwords($module) }}</strong></td>
                                <td class="permissions">
                                    @foreach ($permissions as $permission)
                                        @php
                                            // Split the permission name by spaces and get the last word
                                            $lastWord = ucfirst(last(explode(' ', $permission->name)));
                                        @endphp
                                        <span class="badge bg-success-subtle text-success text-uppercase" style="margin-right:5px;">
                                            {{ $lastWord }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="created_at">{{ $permission->created_at->format('d-m-Y h:i A') }}</td>
                                <td>
                                    @php
                                        // Get the first permission ID for editing and deleting the whole group
                                        $firstPermissionId = $permissions->first()->id;
                                    @endphp
                                    <a href="{{ route('permissions.edit', $firstPermissionId) }}" class="btn btn-sm btn-success edit-item-btn">Edit</a>
                                    <form id="delete-form-{{ $firstPermissionId }}" action="{{ route('permissions.destroy', $firstPermissionId) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDeletion({{ $firstPermissionId }})" class="btn btn-sm btn-danger remove-item-btn">Delete</button>
                                    </form>
                                    <script>
                                        function confirmDeletion(permissionId) {
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
                                                    document.getElementById('delete-form-' + permissionId).submit();
                                                }
                                            });
                                        }
                                    </script>
                                </td>
                            </tr>
                            @php $id ++; @endphp
                            @endforeach
                        </tbody>
                    </table>
            </div>

                    <div class="d-flex justify-content-end">
                        <div class="pagination-wrap hstack gap-2">
                            <a class="page-item pagination-prev disabled" href="javascript:void(0);">
                                Previous
                            </a>
                            <ul class="pagination listjs-pagination mb-0"></ul>
                            <a class="page-item pagination-next" href="javascript:void(0);">
                                Next
                            </a>
                        </div>
                    </div>
                    <div class="noresult" style="display: none;">No results found</div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>

<script>

    var perPage = parseInt(document.getElementById("perPageSelect").value); // Set the initial perPage value based on the dropdown

    var options = {
        valueNames: [
            "module",
            "permissions",
        ],
        page: perPage,
        pagination: true,
        plugins: [
            ListPagination({
                left: 2,
                right: 2
            })
        ]
    };

    var permissionList = new List("permissionList", options).on("updated", function (list) {
        if (list.matchingItems.length === 0) {
            document.querySelector(".noresult").style.display = "block";
        } else {
            document.querySelector(".noresult").style.display = "none";
        }

        // Handle pagination controls
        var isFirstPage = list.i === 1;
        var isLastPage = list.i + list.page >= list.matchingItems.length;

        var prevButton = document.querySelector(".pagination-prev");
        var nextButton = document.querySelector(".pagination-next");

        if (prevButton) prevButton.classList.toggle("disabled", isFirstPage);
        if (nextButton) nextButton.classList.toggle("disabled", isLastPage);

        if (list.matchingItems.length <= perPage) {
            document.querySelector(".pagination-wrap").style.display = "none";
        } else {
            document.querySelector(".pagination-wrap").style.display = "flex";
        }

        // Highlight the current page number
        updatePaginationHighlight(list.i);
    });

    // Initial highlight on load
    document.addEventListener("DOMContentLoaded", function() {
        updatePaginationHighlight(1); // Highlight the first page on load
    });

    // Handle perPage selection from dropdown
    document.getElementById("perPageSelect").addEventListener("change", function() {
        perPage = parseInt(this.value);
        permissionList.page = perPage;
        permissionList.update();
    });

    // Handle previous button
    document.querySelector(".pagination-prev").addEventListener("click", function() {
        if (!this.classList.contains("disabled")) {
            permissionList.show(permissionList.i - 1, perPage);
            updatePaginationHighlight(permissionList.i);
        }
    });

    // Handle next button
    document.querySelector(".pagination-next").addEventListener("click", function() {
        if (!this.classList.contains("disabled")) {
            permissionList.show(permissionList.i + 1, perPage);
            updatePaginationHighlight(permissionList.i);
        }
    });

    // Function to update pagination highlight
    function updatePaginationHighlight(currentPageIndex) {
        var paginationLinks = document.querySelectorAll(".pagination.listjs-pagination li");
        if (paginationLinks.length > 0) {
            paginationLinks.forEach(function (link) {
                link.classList.remove("active"); // Remove active class from all
            });

            // Check if the active link index exists
            if (paginationLinks[currentPageIndex - 1]) {
                paginationLinks[currentPageIndex - 1].classList.add("active"); // Add active class to the current page link
            }
        }
    }


</script>

<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
