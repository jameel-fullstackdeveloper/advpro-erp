@extends('layouts.master')
@section('title')
    Users
@endsection

@section('css')
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<style>

</style>
@endsection

@section('content')

<div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                                <h4 class="mb-sm-0">Users</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ url('users') }}">Users</a></li>
                                        <li class="breadcrumb-item active">List</li>
                                    </ol>
                                </div>

                            </div>
    </div>
</div>

@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif


<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">All Users</h4>
                                    <div class="flex-shrink-0">
                                    <a href="{{ route('users.create') }}" class="btn btn-primary add-btn">
                                    Create New User</a>
                                    </div>
                                </div>



<div class="card-body">
    <div id="customerList">
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

        <table class="table align-middle table-nowrap" id="customerTable">
            <thead class="table-light">
                <tr>
                    <th class="sort" data-sort="id">ID</th>
                    <th>Image</th>
                    <th class="sort" data-sort="name">Name</th>
                    <th class="sort" data-sort="email">Email</th>
                    <th>Segments</th>
                    <th>Roles</th>
                    <th class="sort" data-sort="created_at">Created at</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="list">
                @foreach ($users as $user)
                <tr>
                    <th scope="row" class="id">
                        <a href="#" class="fw-semibold">{{ $user->id }}</a>
                    </th>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="{{ $user->avatar ? asset('images/' . $user->avatar) : 'https://via.placeholder.com/50' }}"
                                alt="Avatar" class="image avatar-xs rounded-circle" width="50" height="50">
                        </div>
                    </td>
                    <td class="name"> <strong> {{ $user->name }} </strong></td>
                    <td class="email">{{ $user->email }}</td>
                    <td>
                        @foreach ($user->companies as $company)
                            <span class="badge bg-secondary">{{ $company->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($user->roles as $role)
                        <span class="badge bg-success-subtle text-success text-uppercase" style="margin-right:5px;">
                            {{ $role->name }}
                        </span>
                        @endforeach
                    </td>
                    <td class="created_at">{{ $user->created_at->format('d-m-Y h:i A') }}</td>
                    <td>
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-success edit-item-btn">Edit</a>
                            {{-- Prevent user from deleting themselves --}}
                            @if (auth()->user()->id !== $user->id)
                                <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDeletion({{ $user->id }})" class="btn btn-sm btn-danger remove-item-btn">Delete</button>
                                </form>
                            @else
                                <button type="button" class="btn btn-sm btn-secondary" disabled>Cannot Delete Yourself</button>
                            @endif

                        <script>
                            function confirmDeletion(userId) {
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
                                        document.getElementById('delete-form-' + userId).submit();
                                    }
                                });
                            }
                    </script>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

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

<div class="col-lg-4">
</div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>

<script>

    var perPage = parseInt(document.getElementById("perPageSelect").value); // Set the initial perPage value based on the dropdown


    var options = {
        valueNames: [
            "id",
            "name",
            "email",
            "created_at",
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

    var customerList = new List("customerList", options).on("updated", function (list) {
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
        customerList.page = perPage;
        customerList.update();
    });

    // Handle previous button
    document.querySelector(".pagination-prev").addEventListener("click", function() {
        if (!this.classList.contains("disabled")) {
            customerList.show(customerList.i - 1, perPage);
            updatePaginationHighlight(customerList.i);
        }
    });

    // Handle next button
    document.querySelector(".pagination-next").addEventListener("click", function() {
        if (!this.classList.contains("disabled")) {
            customerList.show(customerList.i + 1, perPage);
            updatePaginationHighlight(customerList.i);
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

<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection



