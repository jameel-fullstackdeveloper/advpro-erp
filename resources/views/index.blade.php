@extends('layouts.master')
@section('title')
    @lang('translation.dashboards')
@endsection

@section('css')
<style>
.card-titled {
    padding: 0px;
    margin: 0px;
    border-left: 0px;
    min-height: 0px;
    font-size: 13px !important;
    text-transform: uppercase;
    letter-spacing:1px;
}

.card-header {
    padding:10px !important;
}
.table td {
    border-right: 1px solid #ddd;
}
.table td:last-child {
    border-right: none;
}

</style>

@endsection

@section('content')


@php
    use App\Models\Company;
    $company = Company::find(session('company_id'));
@endphp


@if($company->type=="Feed Mill")
    <livewire:dashboard />

    @if(auth()->user()->hasRole(['Administrator', 'Super Admin','Accounts Manager','Jr. Accountant']))
    <div class="row">
            <div class="col-xl-6">
                <div class="card">
                <div class="card-header border-0 align-items-center d-flex">
                                        <h4 class="card-title mb-0 flex-grow-1 text-capitalize" style="border-left:none !important;">
                                        <i class="text-success  ri-shopping-cart-line fs-20"></i> Sales Metrics</h4>

                                        <div>
                                            <span class="text-muted fs-12 mt-1">Year of {{ date('Y') }}</span>
                                            <!--<button type="button" class="btn btn-soft-secondary btn-sm material-shadow-none">
                                                ALL
                                            </button>
                                            <button type="button" class="btn btn-soft-secondary btn-sm material-shadow-none">
                                                1M
                                            </button>
                                            <button type="button" class="btn btn-soft-secondary btn-sm material-shadow-none">
                                                6M
                                            </button>
                                            <button type="button" class="btn btn-soft-primary btn-sm material-shadow-none">
                                                1Y
                                            </button>-->
                                        </div>
                                    </div>

                    <div class="card-body">
                            <div id="column_chart" class="apex-charts" dir="ltr"></div>

                    </div><!-- end card-body -->
                </div><!-- end card -->
            </div>
            <!-- end col -->

            <div class="col-xl-6">

            </div>

        </div>

    </div>
    </div>

    @endif


@endif
<?php //var_dump ($salesData) ?>

@endsection

@section('script')
<script src="{{ URL::asset('build/js/pages/modal.init.js') }}"></script>
<script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>

<script>

    document.addEventListener('DOMContentLoaded', function () {

        // Pass the sales and purchase data from the controller to JavaScript
        var salesData = @json($salesData);
        //var salesData = [15000, 2000, 18450, 20520, 25550, 35500, 3550, 4050, 5450, 500, 55810, 600];
        var options = {
            chart: {
                height: 350,
                type: 'bar',
                toolbar: {
                    show: false, // Hide the toolbar, which removes the chart menu (three dots)
                },

            },
            series: [{
                name: 'Sales',
                data: salesData,
            }
            /*, {
                name: 'Purchases',
                data: purchaseData,
            }*/
                ],
            plotOptions: {
                bar: {
                    columnWidth: '60%',  // Decrease the bar width (default is 70%)
                    dataLabels: {
                        position: 'top', // top, center, bottom
                     },
                },

            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                /*title: {
                    text: 'Sales (Bags Sold)', // Caption for the x-axis
                    style: {
                        fontSize: '11px',
                        color: '#878a99', // Set caption color
                    },
                },*/
            },
            colors: ['#0ab39c', '#495057'], // Colors for Sales and Purchases

            dataLabels: {
                    enabled: true, // Enable the display of data labels on the bars
                    offsetY: -15,  // Position the label slightly above the bar
                    style: {
                        fontSize: '11px',
                        colors: ['#495057'] // Set text color to black
                    },
                    formatter: function(value) {
                            return value === 0 ? '' : value; // Hide 0 values
                    },
                },
        };

        var chart = new ApexCharts(document.querySelector("#column_chart"), options);
        chart.render();
    });
</script>


@endsection
