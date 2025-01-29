@extends('layouts.master')
@section('title')
     Create Production
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .form-control {
            border-radius: 0px !important;
            padding: 8px !important;
            font-size: 12px !important;
        }
        .form-select-sm {
            border-radius: 0px !important;
            padding: 5px !important;
            font-size: 10px !important;
            border-color:#ced4da !important;
        }

        // Section Heading
        .section-heading {
            font-size: 14px;
            font-weight: 500;
            color: #007bff;
            position: relative;
            margin-bottom: 6px;
            padding-bottom: 6px;
            text-transform:uppercase;
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
            text-transform:uppercase;
        }

        .section-heading:hover::after {
            transform: scaleX(1);
        }

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
        }

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




    </style>
@endsection

@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h4 class="mb-sm-0 card-title">Create Production</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Production</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <!-- Invoices Table -->


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
     <div class="card">
        <div class="card-body">
       <div class="row">

        <h5 class="mb-3 mt-2 text-uppercase">
            <i class=" ri-shape-2-line" ></i> Batch Detail <span class="text-info fs-12"></h5>
            <div class="col-md-2 mb-3">
                <label for="production_date" class="form-label">Date</label>
                <input type="date" id="production_date" name="production_date" class="form-control" value="{{ old('production_date', $today) }}" required />
                @error('production_date')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-5 mb-3">
                <label for="finished_product" class="form-label">Select Product Produced</label>

                <select id="finished_product" name="finished_product" class="form-select" required>
                    <option value="">--- Select a Product---</option>
                    @foreach ($finishedProducts as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-5 mb-3">
            <label for="comments" class="form-label">Description</label></td>
            <td>
                <input type="text" id="comments" name="comments" class="form-control" placeholder=""
                    value="{{ old('comments') }}" />
                @error('comments')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

        </div>




        <table class="table table-sm table-borderless">
     <tbody>
        <tr>
            <td><label for="lots" class="form-label fs-12">Batch Excecuted</label>
                <input type="number" id="lots" name="lots" class="form-control text-center" placeholder=""
                    value="{{ old('lots') }}" required />
                @error('lots')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </td>

            <td><label for="perlots" class="form-label fs-12">Per Batch <small>(Bags)</small></label>
                <input type="number" id="perlots" name="perlots" class="form-control text-center" placeholder=""
                    value="{{ old('perlots') }}" required />
                @error('perlots')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </td>

            <td><label for="defaultbags_perlot" class="form-label fs-12">Expected Production <small>(Bags)</small></label>
                <input type="number" id="defaultbags_perlot" name="defaultbags_perlot" class="form-control text-center" placeholder=""
                    value="{{ old('defaultbags_perlot') }}" readonly required />
                @error('defaultbags_perlot')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </td>

            <td><label for="short_perlot" class="form-label text-danger fs-12">Shortage <small>(Bags)</small></label>
                <input type="number" id="short_perlot" name="short_perlot" class="form-control text-center" placeholder=""
                    value="{{ old('short_perlot') }}" />
                @error('short_perlot')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </td>

            <td><label for="excess_perlot" class="form-label text-success fs-12">Excess <small>(Bags)</small></label>
                <input type="number" id="excess_perlot" name="excess_perlot" class="form-control text-center" placeholder=""
                    value="{{ old('excess_perlot') }}" />
                @error('excess_perlot')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </td>

            <td> <label for="actual_produced" class="form-label fs-12">Actual Production <small>(Bags)</small></label>
                <input type="number" id="actual_produced" name="actual_produced" class="form-control text-center" placeholder=""
                    value="{{ old('actual_produced') }}" readonly required/>
                @error('actual_produced')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </td>
        </tr>
    </tbody>
</table>


</div>
    </div>



      <!-- Raw Materials Section -->
@foreach ($purchaseItemGroups as $group)
    @if ($group->purchaseItems && $group->purchaseItems->isNotEmpty())
        <div class="card">
            <div class="card-body">
                <h5 class="mt-2 text-uppercase"><i class=" ri-shape-2-line"></i>
                 {{ $group->name }} <span class="text-info fs-12">
                    (Used)</span></h5>

                <!-- Show error for finished_products if none is selected -->
                <table class="table table-sm table-borderless">
                    <tbody>
                        @php $count = 0; @endphp
                        <tr>
                            @foreach ($group->purchaseItems as $item)
                                <td> <!-- Add text-center here for centering content -->
                                    <label for="material_{{ $item->id }}" class="form-label d-block fs-12">{{ $item->name }}</label>

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
        </div>
    @endif
@endforeach



            </div>
            </div>



    <div class="row mb-4">

        <div class="col-md-12">
        <button type="submit" class="btn btn-success btn-submit mt-3">Save Production</button>
        </div>


    </div>



</form>


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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const lotsField = document.getElementById('lots');
        const perlotField = document.getElementById('perlots');
        const shortField = document.getElementById('short_perlot');
        const excessField = document.getElementById('excess_perlot');
        const defaultbagsField = document.getElementById('defaultbags_perlot');
        const actualProducedField = document.getElementById('actual_produced');

        // Function to calculate Expected and Actual Production
        function calculateProduction() {
            const lots = parseFloat(lotsField.value) || 0;
            const perlot = parseFloat(perlotField.value) || 0;
            const short = parseFloat(shortField.value) || 0;
            const excess = parseFloat(excessField.value) || 0;

            // Calculate Expected Production (Bags)
            const expectedProduction = lots * perlot;
            defaultbagsField.value = expectedProduction;

            // Calculate Actual Production (Bags)
            const actualProduction = expectedProduction - short + excess;
            actualProducedField.value = actualProduction;
        }

        // Event listeners for input fields
        lotsField.addEventListener('input', calculateProduction);
        perlotField.addEventListener('input', calculateProduction);
        shortField.addEventListener('input', calculateProduction);
        excessField.addEventListener('input', calculateProduction);

        // Initial calculation if values are pre-filled
        calculateProduction();
    });

</script>
@endsection
