<div>
    <style>


small
{
    font-size:12px;
}
     /* Spinner styles */
        .spinner {
            display: none; /* Hide by default */
            position: fixed;
            top: 50%;
            left: 50%;
            width: 50px;
            height: 50px;
            border: 6px solid #ccc;
            border-top-color: #2a9d8f;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            z-index: 9999; /* Ensure it's on top of everything */
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .blinking {
        animation: blink 1s linear infinite;
    }

    @keyframes blink {
        0% {
            opacity: 1;
        }
        50% {
            opacity: 0;
        }
        100% {
            opacity: 1;
        }
    }

    .ri-shopping-cart-line:hover {
    color: #2a9d8f;
    transition: color 0.3s ease;
}

.card-header {
    padding-top:4px !important;
    padding-bottom:4px !important;
}

.card-header h6 {
    font-size: 14px;
    font-weight: 600;
}

.text-dark {
    font-weight: 500;
}

.card {
    background-color: #ffffff;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    transition: transform 0.3s ease;
}

.card-body {
    background-color: #ffffff;
}

.card:hover {
    transform: translateY(-5px);
}


    </style>


@auth


    @if(auth()->user()->hasRole(['Administrator', 'Super Admin']))


<!-- right offcanvas  Sales Overview-->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRightSales" aria-labelledby="offcanvasRightSalesLabel">
    <div class="offcanvas-header bg-gradient bg-primary">
        <h5 id="offcanvasRightSalesLabel" class="mb-0 text-white"><i class="ri-shopping-cart-line fs-20"></i> Sales Invoices Summary</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-4 bg-white">
        <div class="">
            <div class="">
                <div class="summary-item">
                    <h5 class="text-dark fs-20 mb-1">{{ number_format($totalInvoices) }} <small class="text-muted  fs-12">Invoices</small></h5>

                </div>
                <hr/>
                <div class="summary-item">
                    <h5 class="text-dark fs-20 mb-1">{{ number_format($totalBagsSold) }}    <small class="text-muted fs-12">Bags</small></h5>

                </div>

                <div class="summary-item d-none">
                    <h5 class="text-danger fs-20 mb-1">{{ number_format($totalSaleDiscount, 2) }} <small class="text-muted fs-12">Discount</small></h5>

                </div>
                <hr/>
                <div class="summary-item">
                    <h5 class="text-dark fs-20 mb-1">{{ number_format($totalSaleAmountExclTax, 2) }} <small class="text-muted fs-12">Amount <span>(Excluding taxes)</span></small></h5>

                </div>
                <hr/>
                <div class="summary-item">
                    <h5 class="text-dark fs-20 mb-1">{{ number_format($totalSaleTaxes, 2) }} <small class="text-muted fs-12">Sales Taxes</small></h5>

                </div>
                <hr/>
                <div class="summary-item">
                    <h5 class="text-dark fs-20 mb-1">{{ number_format($totalSaleCommission, 2) }} <small class="text-muted fs-12">Further Tax</small></h5>

                </div>
                <hr/>
                <div class="summary-item">
                    <h5 class="text-dark fs-20 mb-1">{{ number_format($totalWHT, 2) }} <small class="text-muted fs-12">With Holding Tax</small></h5>

                </div>
                <hr/>
                <div class="summary-item">
                    <h5 class="text-dark fs-20 mb-1">{{ number_format($totalSaleAmount, 2) }} <small class="text-muted fs-12">Amount <span>(Including taxes)</span></small></h5>

                </div>
                <hr/>
                <div class="summary-item">
                    <h5 class="text-danger fs-20 mb-1">{{ number_format($totalSaleFreight, 2) }}
                        <small class="text-muted fs-12">Freight</h5>
                </div>
                <hr/>
                <div class="summary-item">
                    <h5 class="text-danger fs-20 mb-1">{{ number_format($totalSaleBrokery, 2) }}
                        <small class="text-muted fs-12">Brokerage</h5>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- right offcanvas  Purchase Overview-->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRightPurchases" aria-labelledby="offcanvasRightPurchasesLabel">
    <div class="offcanvas-header bg-gradient bg-info">
        <h5 id="offcanvasRightPurchasesLabel" class="mb-0 text-white"><i class="ri-truck-line fs-20"></i> Purchase Bills Summary</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-4 bg-white">
        <div class="">
            <div class="">
                <div class="summary-item">
                    <h5 class="text-dark fs-20 mb-1">{{ $totalbills }} <small class="text-muted  fs-12">Bills</small></h5>

                </div>
                <hr/>
                <div class="summary-item">
                    <h5 class="text-dark fs-20 mb-1">{{ number_format($totalPurchaseAmountExclTax,2) }}    <small class="text-muted fs-12">Amount (Excluding Taxes)</small></h5>

                </div>
                <hr/>

                <div class="summary-item">
                    <h5 class="text-dark fs-20 mb-1">{{ number_format($totalPurchaseSaleTaxes, 2) }} <small class="text-muted fs-12">Sales Tax</small></h5>

                </div>
                <hr/>
                <div class="summary-item">
                    <h5 class="text-dark fs-20 mb-1">{{ number_format($totalPurchaseAmount, 2) }} <small class="text-muted fs-12">Amount (Including Taxes)</small></h5>

                </div>
                <hr/>
                <div class="summary-item">
                    <h5 class="text-danger fs-20 mb-1">{{ number_format($totalPurchaseBrokrage, 2) }} <small class="text-muted fs-12">Brokerage</small></h5>

                </div>
                <hr/>
                <div class="summary-item">
                    <h5 class="text-dark fs-20 mb-1">{{ number_format($totalPurchaseWHT, 2) }} <small class="text-muted fs-12">With Holding Tax</small></h5>

                </div>

            </div>
        </div>
    </div>
</div>



<!-- left offcanvas  amount to receive-->
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasLeftSaleDueAmount" aria-labelledby="offcanvasLeftSaleDueAmountLabel">
    <div class="offcanvas-header bg-gradient bg-success">
        <h5 id="offcanvasLeftSaleDueAmountLabel text-white" style="color:#fff;"> <i class="ri-shopping-cart-line fs-20"></i> Sales Invoices Due </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
    <div class="table-responsive">
        <table class="table table-centered align-middle table-sm table-nowrap mb-0 table-bordered">
                <thead class="table-info">
                <tr>
                    <th class="text-center">#</th>
                    <th>Customer</th>
                    <th class="text-end">Due Amount</th>
                </tr>
            </thead>
                    <tbody>
                                    @php
                                        $totalSaleDueAmount1 = 0;  // Initialize variable for total due amount
                                        $sno =1;
                                    @endphp



                        @foreach($customersDueAmount as $customer => $dueAmount)
                                        @php
                                            $totalSaleDueAmount1 += $dueAmount;  // Accumulate the due amounts
                                        @endphp
                            <tr>
                                <td class="text-center">{{ $sno }}</td>
                                <td class="text-start" ><span class="" title="{{ $customer }}" style="font-weight:600;text-transform:uppercase">{{ Str::words($customer ?? 'N/A', 3, ' ...') }} </span></td>
                                <td class="text-end">{{ number_format($dueAmount) }}</td>
                            </tr>

                                @php $sno += 1;  @endphp
                        @endforeach
                    </tbody>
                    <tfoot  class="table-light">
                        <th></th>
                        <th class="text-center">Total</th>
                        <th class="text-end"> {{ number_format($totalSaleDueAmount1)}}</th>
                    </tfoot>
                </table>

                </div>
    </div>
</div>


<!-- left offcanvas  amount to pay-->
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasLeftPurchaseDueAmount" aria-labelledby="offcanvasLeftPurchaseDueAmountLabel">
    <div class="offcanvas-header bg-gradient bg-danger">
        <h5 id="offcanvasLeftPurchaseDueAmountLabel text-white" style="color:#fff;!important"><i class="ri-truck-line fs-20"></i> Purchase Bills Dues </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
    <div class="table-responsive">
        <table class="table table-centered align-middle table-sm table-nowrap mb-0 table-bordered">
                <thead class="table-info">
                <tr>
                    <th class="text-center">#</th>
                    <th>Vendor</th>
                    <th class="text-end">Due Amount</th>
                </tr>
            </thead>
                    <tbody>
                                    @php
                                        $totalPurchaseDueAmount1 = 0;
                                        $sno =1;
                                    @endphp



                                    @foreach($vendorsDueAmount as $vendor => $dueAmount)
                                        @php
                                            $totalPurchaseDueAmount1 += $dueAmount;  // Accumulate the due amounts
                                        @endphp
                            <tr>
                                <td class="text-center">{{ $sno }}</td>
                                <td><span class="" title="{{ $vendor }}" style="font-weight:600;text-transform:uppercase">{{ Str::words($vendor ?? 'N/A', 3, ' ...') }} </span></td>
                                <td class="text-end">{{ number_format($dueAmount) }}</td>
                            </tr>

                                @php $sno += 1;  @endphp
                        @endforeach
                    </tbody>
                    <tfoot  class="table-light">
                        <th></th>
                        <th class="text-center">Total</th>
                        <th class="text-end"> {{ number_format($totalPurchaseDueAmount1)}}</th>
                    </tfoot>
                </table>

                </div>
    </div>
</div>



    <!-- Spinner element -->
    <div wire:loading wire:target="filter_date" class="spinner"></div>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0 card-title">Dashboards</h4>
                <div class="page-title-right">
                    <select class="form-select-sm" wire:model.live="filter_date" id="filter_date" style="padding:6px;">
                        <option value="Today">Today</option>
                        <option value="CurrentMonth">Current Month</option>
                        <option value="CurrentYear">Current Year</option>
                        <option value="LastMonth">Last Month</option>
                        <option value="LastQuarter">Last Quarter</option>
                        <option value="LastYear">Last Year</option>
                        <option value="Last30Days">Last 30Days</option>
                        <option value="Last60Days">Last 60Days</option>
                        <option value="Last90Days">Last 90Days</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-3 ">
            <div class="card bg-light shadow-lg">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 text-white">Sales Invoices</h6>
                    <i class="ri-shopping-cart-line fs-20"></i>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="text-center">
                            <!--<h5 class="text-dark">{{ number_format($totalBagsSold) }} </h5>
                                <small> Bags</small>-->
                                <h5 class="text-dark"> {{ number_format($totalInvoices) }} </h5> <small>Invoices</small>
                        </div>

                        <div class="text-center">
                            <h5 class="text-dark">{{ number_format($totalSaleAmount) }} <span style="font-size:12px;">Rs.</span> </h5>
                            <small>Amount</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between p-1">
                    <a href="{{ url('sales/invoices') }}" class="btn btn-sm btn-link text-dark dark:text-light">
                         <i class="ri-arrow-right-line align-bottom fs-14"></i>
                    </a>
                    <a class="btn btn-sm btn-link text-dark dark:text-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRightSales" aria-controls="offcanvasRightSales">
                     <i class="ri-link-m align-bottom fs-14"></i>
                    </a>
                </div>

            </div>

        </div>

        <div class="col-12 col-md-3 mb-1">
            <div class="card bg-light shadow-lg">
                <div class="card-header bg-info text-white d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 text-white">Purchase Bills</h6>
                    <i class="ri-truck-line fs-20"></i>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="text-center">
                            <h5 class="text-dark">{{ number_format($totalbills) }}</h5>
                            <small>Bills</small>
                        </div>

                        <div class="text-center">
                            <h5 class="text-dark">{{ number_format($totalPurchaseAmount) }} <span style="font-size:12px;">Rs.</span> </h5>
                            <small>Amount</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between p-1">
                    <a href="{{ url('purchase/bills') }}" class="btn btn-sm  btn-link text-dark">
                         <i class="ri-arrow-right-line align-bottom fs-14"></i>
                    </a>
                    <a class="btn btn-sm btn-link text-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRightPurchases" aria-controls="offcanvasRightPurchases">
                     <i class="ri-link-m align-bottom fs-14"></i>
                    </a>
                </div>


            </div>
        </div>

        <div class="col-12 col-md-3 mb-1">
            <div class="card bg-light shadow-lg">
                <div class="card-header bg-danger text-white d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 text-white">Due Amount</h6>
                    <i class="ri-shopping-cart-line fs-20"></i>
                </div>
                <div class="card-body">
                            @php
                                $totalSaleDueAmount = 0;  // Initialize variable for total due amount
                                $totalSaleDueRecords = count($customersDueAmount);  // Get the total number of records (customers)
                            @endphp

                            @foreach($customersDueAmount as $customer => $dueAmount)
                                @php
                                    $totalSaleDueAmount += $dueAmount;  // Accumulate the due amounts
                                @endphp
                            @endforeach


                            @php
                                $totalPurchaseDueAmount = 0;  // Initialize variable for total due amount
                                $totalPurchaseDueRecords = count($vendorsDueAmount);  // Get the total number of records (customers)
                            @endphp

                            @foreach($vendorsDueAmount as $vendor => $dueAmount)
                                @php
                                    $totalPurchaseDueAmount += $dueAmount;
                                @endphp
                            @endforeach

                        <div class="d-flex justify-content-between">
                            <div class="text-center">
                                <h5 class="text-dark">{{ number_format($totalSaleDueAmount) }} <span style="font-size:12px;">Rs.</span></h5>
                                <small>Sales Invoices</small>
                            </div>

                            <div class="text-center">
                                <h5 class="text-danger">{{ number_format($totalPurchaseDueAmount) }} <span style="font-size:12px;">Rs.</span> </h5>
                                <small>Purchase Bills</small>
                            </div>
                       </div>
                </div>

                <div class="card-footer d-flex justify-content-between p-1">
                    <a class="btn btn-sm btn-link text-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasLeftSaleDueAmount" aria-controls="offcanvasLeftSaleDueAmount">
                     <i class="ri-link-m align-bottom fs-14"></i>
                    </a>


                    <a class="btn btn-sm btn-link text-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasLeftPurchaseDueAmount" aria-controls="offcanvasLeftPurchaseDueAmount">
                     <i class="ri-link-m align-bottom fs-14"></i>
                    </a>
                </div>

            </div>
        </div>

        <div class="col-12 col-md-3 mb-1">
            <div class="card bg-light shadow-lg">
                <div class="card-header bg-success text-white d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 text-white">Cash & Bank</h6>
                    <i class="ri-bank-line fs-20"></i>
                </div>
                <div class="card-body">

                        <div class="d-flex justify-content-between">
                            <div class="text-center">
                                <h5 class="text-dark">{{ number_format($cashInHand) }} <span style="font-size:12px;">Rs.</span></h5>
                                <small>Cash</small>
                            </div>

                            <div class="text-center">
                                <h5 class="text-dark">{{ number_format($bankAmount) }} <span style="font-size:12px;">Rs.</span> </h5>
                                <small>Bank</small>
                            </div>
                       </div>
                </div>

                <div class="card-footer d-flex justify-content-between p-1">
                    <a class="btn btn-sm btn-link text-dark" href="{{ url('accounting/cashbook') }}" aria-controls="offcanvasLeftSaleDueAmount">
                        <i class="ri-arrow-right-line align-bottom fs-14"></i>
                    </a>


                    <a class="btn btn-sm btn-link text-dark" href="{{ url('accounting/cashbook') }}" aria-controls="offcanvasLeftPurchaseDueAmount">
                         <i class="ri-arrow-right-line align-bottom fs-14"></i>
                    </a>
                </div>

            </div>
        </div>

        <div class="col-12 col-md-3 mb-1 d-none">
            <div class="card bg-light shadow-lg">
                <div class="card-header bg-success text-white d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 text-white">Customers & Vendors</h6>
                    <i class="ri-user-line fs-20"></i>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="text-center">
                            <h5 class="text-dark">{{ number_format($totalCustomers) }}</h5>
                            <small>Customers</small>
                        </div>

                        <div class="text-center">
                            <h5 class="text-dark">{{ number_format($totalVendors) }}</h5>
                            <small>Vendors</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between p-1">
                    <a href="{{ url('sales/customers') }}" class="btn btn-sm  btn-link text-dark">
                        View <i class="ri-arrow-right-line align-bottom fs-12"></i>
                    </a>

                    <a href="{{ url('purchase/vendors') }}" class="btn btn-sm  btn-link text-dark">
                        View <i class="ri-arrow-right-line align-bottom fs-12"></i>
                    </a>

                </div>


            </div>
        </div>


        </div>


        <div class="row d-none">
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm flex-shrink-0">
                                                        <span class="avatar-title bg-light text-primary rounded-circle fs-3 material-shadow">
                                                            <i class=" ri-hand-coin-line align-middle"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1"> Cash in Hand</p>
                                                        <h5 class="mb-0">
                                                            <span class="counter-value" data-target="{{ $cashInHand }}"
                                                                @if($cashInHand < 0) style="color: red;" @endif>
                                                                {{ number_format($cashInHand) }}
                                                            </span>
                                                            <span style="font-size:12px;">Rs.</span>
                                                        </h5>
                                                    </div>
                                                    <div class="flex-shrink-0 align-self-end d-none">
                                                        <span class="badge bg-success-subtle text-success"><i class="ri-arrow-up-s-fill align-middle me-1"></i>6.24 %<span> </span></span>
                                                    </div>
                                                </div>
                                            </div><!-- end card body -->
                                        </div><!-- end card -->
                                    </div><!-- end col -->
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm flex-shrink-0">
                                                        <span class="avatar-title bg-light text-info rounded-circle fs-3">
                                                            <i class="ri-bank-line align-middle"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1"> Bank</p>
                                                        <h5 class=" mb-0"><span class="counter-value" data-target="{{ $bankAmount }}">  {{ number_format($bankAmount) }}

                                                        </span> <span style="font-size:12px;">Rs.</span></h5>
                                                    </div>
                                                    <div class="flex-shrink-0 align-self-end d-none">
                                                        <span class="badge bg-success-subtle text-success"><i class="ri-arrow-up-s-fill align-middle me-1"></i>3.67 %<span> </span></span>
                                                    </div>

                                                </div>
                                            </div><!-- end card body -->
                                        </div><!-- end card -->
                                    </div><!-- end col -->
                                    <div class="col-lg-3 col-md-3 d-none">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm flex-shrink-0">
                                                        <span class="avatar-title bg-light text-danger rounded-circle fs-3 material-shadow">
                                                            <i class="ri-lightbulb-line align-middle"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Expenses</p>
                                                        <h5 class=" mb-0"><span class="counter-value" data-target="{{ $expenses }}">
                                                            {{ number_format($expenses) }}
                                                        </span>
                                                        <span style="font-size:12px;">Rs.</span></h5>
                                                    </div>
                                                    <div class="flex-shrink-0 align-self-end d-none">
                                                        <span class="badge bg-danger-subtle text-danger"><i class="ri-arrow-down-s-fill align-middle me-1"></i>4.80 %<span> </span></span>
                                                    </div>
                                                </div>
                                            </div><!-- end card body -->
                                        </div><!-- end card -->
                                    </div><!-- end col -->

                                    <div class="col-lg-3 col-md-3 d-none">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm flex-shrink-0">
                                                        <span class="avatar-title bg-light text-primary rounded-circle fs-3 material-shadow">
                                                            <i class="bx bxs-factory align-middle"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Stock</p>
                                                        <h5 class=" mb-0"><span class="counter-value" data-target="{{ $expenses }}">
                                                            {{ number_format($expenses,2) }}
                                                        </span>
                                                        <span style="font-size:12px;">Rs.</span></h5>
                                                    </div>
                                                    <div class="flex-shrink-0 align-self-end d-none">
                                                        <span class="badge bg-danger-subtle text-danger"><i class="ri-arrow-down-s-fill align-middle me-1"></i>4.80 %<span> </span></span>
                                                    </div>
                                                </div>
                                            </div><!-- end card body -->
                                        </div><!-- end card -->
                                    </div><!-- end col -->



                                </div>

                                @else
        <p>You do not have permission to view dashboard.</p>
    @endif
@endauth



</div><!-- root div -->










