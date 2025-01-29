@extends('layouts.master')
@section('title')
    Create Production & Consumption
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .form-control {
            border-radius: 0px !important;
            padding: 5px !important;
            font-size: 12px !important;
        }
        .form-select-sm {
            border-radius: 0px !important;
            padding: 5px !important;
            font-size: 10px !important;
            border-color:#ced4da !important;
        }

        /* Section Heading
        .section-heading {
            font-size: 14px;
            font-weight: 500;
            color: #007bff;
            position: relative;
            margin-bottom: 6px;
            padding-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .section-heading::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 1px;
            background-color: #FF9F43;
            border-radius: 5px;
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .section-heading:hover::after {
            transform: scaleX(1);
        }  */

        /* Section Blocks
        .section-block {
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 8px;
            border: 1px solid #ddd;
        }

        .section-block .row {
            margin-bottom: 5px;
        } */

        /* Buttons */
        .btn-submit {
            background-color: #007bff;
            color: white;
            font-size: 12px;
            font-weight: 500;
            padding: 8px 18px;
            border-radius: 5px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #0056b3;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .col-md-2,
        .col-md-7,
        .col-2,
        .col-1,
        .col-4 {
            margin-bottom: 12px;
        }

        /* Table Styling */
        .table {
            width: 100%;
            margin-top: 0px;
            border-collapse: separate;
            border-spacing: 0 6px;
        }

        .table td {
            vertical-align: middle;
            padding: 10px;
        }

        .table input.form-control {
            width: 100%;
            max-width: 100px;
            margin: 0 auto;
            border-radius: 5px;
            padding: 6px;
        }

    </style>
@endsection

@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h4 class="mb-sm-0 card-title">Material Consumption and Bags Prodcued</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Production</a></li>
                    <li class="breadcrumb-item active">Create Consumption</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <!-- Invoices Table -->
        <div class="card">
            <div class="card-body">

            @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif


 <!-- Show error for finished_products if none is selected -->
        @error('finished_products')
            <div class="alert alert-danger mt-2">
                {{ $message }}
            </div>
        @enderror


        @error('raw_products')
                        <div class="alert alert-danger mt-2">
                            {{ $message }}
                        </div>
        @enderror

<form action="{{ route('productions.store') }}" method="POST">
    @csrf

    <!-- Date and Description Section -->
    <div class="section-block mb-4">
        <div class="row mb-3">

        <h5 class="section-heading text-success mb-2"> <i class="ri-add-box-line"></i> Create Consumtion </h5>

            <div class="col-md-4">
                <label for="production_date" class="form-label">Date</label>
                <input type="date" id="production_date" name="production_date" class="form-control" value="{{ old('production_date', $today) }}" required />

                @error('production_date')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-8">
                <label for="comments" class="form-label">Description</label>
                <input type="text" id="comments" name="comments" class="form-control" placeholder="Enter Consumption Description"
       value="{{ old('comments') }}"  />

                @error('comments')
                    <div class="text-danger">{{ $message }}</div>
                @enderror

            </div>
        </div>
    </div>

    <!-- Finished Products Section -->
    <div class="section-block mb-4">
        <h5 class="section-heading text-success">
        <i class="ri-add-box-line"></i>  Finished Products <span class="text-info fs-12">
        (Products Produced)</span></h5>




        <table class="table table-bordered text-center">
            <tbody>
                @php $count = 0; @endphp
                <tr>
                    @foreach ($finishedProducts as $product)
                        <td>
                            <label for="finished_product_{{ $product->id }}" class="form-label d-block" style="font-size:11px;">{{ $product->product_name }}</label>

                            <input type="number" id="finished_product_{{ $product->id }}" name="finished_products[{{ $product->id }}][quantity]" class="form-control text-center" step="0.01" value="{{ old('finished_products.' . $product->id . '.quantity', 0) }}">

                            @error('finished_products.' . $product->id . '.quantity')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                        @php $count++; @endphp

                        @if ($count % 8 == 0)
                            </tr><tr>
                        @endif
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>





     <!-- Raw Materials Section -->
     @foreach ($purchaseItemGroups as $group)
        @if ($group->purchaseItems->isNotEmpty())
            <div class="section-block">
            <h5 class="section-heading text-success"><i class="ri-add-box-line"></i>  {{ $group->name }} <span class="text-info fs-12">
            (Material Used)</span></h5>
                  <!-- Show error for finished_products if none is selected -->


                <table class="table table-bordered text-center">
                    <tbody>
                        @php $count = 0; @endphp
                        <tr>
                            @foreach ($group->purchaseItems as $item)
                                <td>
                                    <label for="material_{{ $item->id }}" class="form-label d-block" style="font-size:11px;">{{ $item->item_name }}</label>
                                    <input type="number" id="material_{{ $item->id }}" name="raw_materials[{{ $item->id }}][quantity_used]" class="form-control text-center" step="0.01" value="{{ old('raw_materials.' . $item->id . '.quantity_used', 0) }}">



                                    @error('raw_materials.' . $item->id . '.quantity_used')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </td>
                                @php $count++; @endphp
                                @if ($count % 8 == 0)
                                    </tr><tr>
                                @endif
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    @endforeach


    <button type="submit" class="btn btn-submit mt-3">Save Production</button>

</form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/sweetalerts.init.js') }}"></script>
<script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
<script src="https://cdn.lordicon.com/libs/mssddfmo/lord-icon-2.1.0.js"></script>
<script src="{{ URL::asset('build/js/pages/modal.init.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
