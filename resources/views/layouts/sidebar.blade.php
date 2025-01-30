<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ url('/') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                    <h3 style="color:#fff" class="mt-4">QuickERP</h3>
                <!--<img src="{{ URL::asset('build/images/logo-dark.png') }}" alt="" height="17">-->
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ url('/') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">

                <h3 style="color:#fff" class="mt-4">QuickERP</h3>
               <!-- <img src="{{ URL::asset('build/images/logo-light.png') }}" alt="" height="17">-->
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div class="dropdown sidebar-user m-1 rounded">
        <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false">
            <span class="d-flex align-items-center gap-2">
                <img class="rounded header-profile-user" src="@if (Auth::user()->avatar != ''){{ URL::asset('images/' . Auth::user()->avatar) }}@else{{ URL::asset('build/images/users/avatar-1.jpg') }}@endif" alt="Header Avatar">
                <span class="text-start">
                    <span class="d-block fw-medium sidebar-user-name-text">{{Auth::user()->name}}</span>
                    <span class="d-block fs-14 sidebar-user-name-sub-text"><i
                            class="ri ri-circle-fill fs-10 text-success align-baseline"></i> <span
                            class="align-middle">Online</span></span>
                </span>
            </span>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <!-- item-->
            <h6 class="dropdown-header">Welcome {{Auth::user()->name}}!</h6>
            <a class="dropdown-item" href="javascript:void(0);"><i
                    class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span
                    class="align-middle">Profile</span></a>
            <a class="dropdown-item" href="javascript:void(0);"><i
                    class="mdi mdi-message-text-outline text-muted fs-16 align-middle me-1"></i> <span
                    class="align-middle">Messages</span></a>
            <a class="dropdown-item" href="javascript:void(0);"><i
                    class="mdi mdi-calendar-check-outline text-muted fs-16 align-middle me-1"></i> <span
                    class="align-middle">Taskboard</span></a>
            <a class="dropdown-item" href="javascript:void(0);"><i
                    class="mdi mdi-lifebuoy text-muted fs-16 align-middle me-1"></i> <span
                    class="align-middle">Help</span></a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="javascript:void(0);"><i
                    class="mdi mdi-wallet text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Balance :
                    <b>$5971.67</b></span></a>
            <a class="dropdown-item" href="javascript:void(0);"><span
                    class="badge bg-success-subtle text-success mt-1 float-end">New</span><i
                    class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> <span
                    class="align-middle">Settings</span></a>
            <a class="dropdown-item" href="auth-lockscreen-basic"><i
                    class="mdi mdi-lock text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Lock
                    screen</span></a>

            <a class="dropdown-item " href="javascript:void();"
                onclick="event.preventDefault(); document.getElementById('logout-form-side').submit();"><i
                    class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span
                    key="t-logout">@lang('translation.logout')</span></a>
            <form id="logout-form-side" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>

    @php
        use App\Models\Company;
        $segments = Company::find(session('company_id'));

    @endphp


    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span>@lang('translation.menu')</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ url('/') }}"  role="button"
                        aria-expanded="false" aria-controls="sidebarDashboards">
                        <i class="ri-dashboard-2-line"></i> <span>@lang('translation.dashboards')</span>
                    </a>
                </li> <!-- end Dashboard Menu -->

                <!--<li class="menu-title"><i class="ri-more-fill"></i> <span>@lang('translation.sale')</span></li>-->

                    @php
                        // Check if the current route matches any of the accounting pages
                        $isSalesActive = Request::is('sales*') || Request::is('sales*');
                        $isSalesCustomersActive = Request::is('sales/customers') || Request::is('sales/customers*');
                        $isSalesBrokersActive = Request::is('sales/brokers') || Request::is('sales/brokers*');
                        $isSalesOrdersActive = Request::is('sales/orders') || Request::is('sales/orders*');
                        $isSalesInvoicesActive = Request::is('sales/invoices');

                        $isSalesRetrunActive = Request::is('sales/return') || Request::is('sales/return*');
                        $isSalesReportsActive = Request::is('sales/sales-reports') || Request::is('sales/sales-reports*');
                    @endphp

                    @php
                        $hasCustomerAccess = auth()->user()->can('customers view');
                        $hasSalesBrokerAccess = auth()->user()->can('sales brokers view');
                        $hasSalesOrdersAccess = auth()->user()->can('sales orders view');
                        $hasSalesInvoicesAccess = auth()->user()->can('sales invocies view');
                        $hasSalesReturnAccess = auth()->user()->can('sales return view');
                    @endphp


            @if ($hasCustomerAccess || $hasSalesBrokerAccess || $hasSalesOrdersAccess || $hasSalesInvoicesAccess || $hasSalesReturnAccess)

            <li class="nav-item">
                    <a class="nav-link menu-link {{ $isSalesActive ? 'active' : '' }}" href="#sidebarSales" data-bs-toggle="collapse" role="button"
                        aria-expanded="{{ $isSalesActive ? 'true' : 'false' }}" aria-controls="sidebarSales">
                        <i class="ri-shopping-cart-line"></i> <span>@lang('translation.sale')</span>
                    </a>
                    <div class="collapse menu-dropdown {{ $isSalesActive ? 'show' : '' }}" id="sidebarSales">
                        <ul class="nav nav-sm flex-column">

                        @if($hasCustomerAccess)
                            <li class="nav-item">
                                <a class="nav-link {{ $isSalesCustomersActive ? 'active' : '' }}" href="{{ url('sales/customers') }}" >
                                    <!--<i class="ri-user-shared-line"></i>-->
                                    <span>@lang('translation.customers')</span>
                                </a>
                            </li> <!-- end custoemrs Menu -->





                        @endif

                        @if($hasSalesBrokerAccess)
                            <li class="nav-item">
                                <a href="{{ url('sales/brokers') }}"  class="nav-link {{ $isSalesBrokersActive ? 'active' : '' }}">
                                    Sales Agents</a>
                            </li>
                        @endif

                        @if($hasSalesOrdersAccess)
                            <li class="nav-item d-none">
                                <a class="nav-link {{ $isSalesOrdersActive ? 'active' : '' }}" href="{{ url('sales/orders') }}" >
                                    <!--<i class="ri-user-shared-line"></i>-->
                                    <span>Sales Orders</span>
                                </a>
                            </li>
                        @endif

                        @if($hasSalesInvoicesAccess)
                            <li class="nav-item">
                                 <a class="nav-link {{ $isSalesInvoicesActive ? 'active' : '' }}" href="{{ url('sales/invoices') }}" >
                                    <!--<i class="ri-user-shared-line"></i>-->
                                    <span>Sales (Mill)</span>
                                </a>
                            </li>



                        @endif

                        @if($hasSalesReturnAccess)
                            <li class="nav-item">
                                <a class="nav-link {{ $isSalesRetrunActive ? 'active' : '' }}" href="{{ url('sales/returns') }}" >
                                    <!--<i class="ri-user-shared-line"></i>-->
                                    <span>Sales Return</span>
                                </a>
                            </li>
                        @endif

                        @if($hasSalesReturnAccess)



                            <li class="nav-item">
                                <a class="nav-link {{ $isSalesReportsActive ? 'active' : '' }}" href="{{ url('sales/sales-reports') }}" >
                                    <!--<i class="ri-user-shared-line"></i>-->
                                    <span>Reports</span>
                                </a>
                            </li>

                        @endif

                        </ul>
                    </div>
                </li><!-- end Sales Menu -->
            @endif

                <!--<li class="menu-title"><i class="ri-more-fill"></i> <span>@lang('translation.purchase')</span></li>-->

                @php
                    $isPurchaseActive = Request::is('purchase*') || Request::is('purchase*');
                    $isPurchaseVendorsActive = Request::is('purchase/vendors') || Request::is('purchase/vendors*');
                    $isPurchaseBrokersActive = Request::is('purchase/brokers') || Request::is('purchase/brokers*');
                    $isPurchaseOrdersActive = Request::is('purchase/orders') || Request::is('purchase/orders*');
                    $isPurchaseBillActive = Request::is('purchase/bills');
                    $isPurchaseBillFarmsActive = Request::is('purchase/bills/farms') || Request::is('purchase/bills/farms*');
                    $isPurchaseReturnActive = Request::is('purchase/returns') || Request::is('purchase/returns*');
                    $isPurchaseReportsActive = Request::is('purchase/purchase-reports') || Request::is('purchase/purchase-reports*');
                @endphp


                    @php
                        $hasVendorAccess = auth()->user()->can('purchases vendors view');
                        $hasPurchaseBrokerAccess = auth()->user()->can('purchases brokers view');
                        $hasPurchaseOrdersAccess = auth()->user()->can('purchases orders view');
                        $hasPurchaseBillAccess = auth()->user()->can('purchases bills view');
                        $hasPurchaseReturnAccess = auth()->user()->can('purchases return view');
                    @endphp


        @if ($hasVendorAccess || $hasPurchaseBrokerAccess || $hasPurchaseOrdersAccess || $hasPurchaseBillAccess || $hasPurchaseReturnAccess)

            <li class="nav-item">
                    <a class="nav-link menu-link  {{ $isPurchaseActive ? 'active' : '' }}" href="#sidebarPurchase" data-bs-toggle="collapse" role="button"
                        aria-expanded="{{ $isPurchaseActive ? 'true' : 'false' }}" aria-controls="sidebarPurchase">
                        <i class="ri-truck-line"></i> <span>@lang('translation.purchase')</span>
                    </a>
                    <div class="collapse menu-dropdown {{ $isPurchaseActive ? 'show' : '' }}" id="sidebarPurchase">
                        <ul class="nav nav-sm flex-column">


                        @if($hasVendorAccess)
                            <li class="nav-item">
                                <a class="nav-link {{ $isPurchaseVendorsActive ? 'active' : '' }}" href="{{ url('purchase/vendors') }}"  role="button"
                                    aria-expanded="false" aria-controls="sidebarDashboards">
                                    <span>@lang('translation.vendors')</span>
                                </a>
                            </li> <!-- end vendors Menu -->
                        @endif

                        @if($hasPurchaseBrokerAccess)
                            <li class="nav-item">
                                <a href="{{ url('purchase/brokers') }}"  class="nav-link {{ $isPurchaseBrokersActive ? 'active' : '' }}">
                                    Purchase Brokers</a>
                            </li>
                        @endif

                        @if($hasPurchaseOrdersAccess)
                            <li class="nav-item">
                                <a href="{{ url('purchase/orders') }}"  class="nav-link {{ $isPurchaseOrdersActive ? 'active' : '' }}">
                                    Purchase Orders</a>
                            </li>
                        @endif

                        @if($hasPurchaseBillAccess)
                            <li class="nav-item">
                                <a href="{{ url('purchase/bills') }}" class="nav-link {{ $isPurchaseBillActive ? 'active' : '' }}">
                                Purchases (Mill)</a>
                            </li>



                        @endif

                        @if($hasPurchaseReturnAccess)

                            <li class="nav-item">
                                <a href="{{ url('purchase/returns') }} " class="nav-link {{ $isPurchaseReturnActive ? 'active' : '' }}">
                                    Purchase Return</a>
                            </li>

                        @endif

                        @if($hasPurchaseReturnAccess)

                            <li class="nav-item">
                                <a href="{{ url('purchases/purchase-reports') }} " class="nav-link {{ $isPurchaseReportsActive ? 'active' : '' }}">
                                    Reports</a>
                            </li>

                        @endif

                        </ul>
                    </div>
                </li><!-- end Purchase Menu -->
            @endif

                <!--<li class="menu-title"><i class="ri-more-fill"></i> <span>@lang('translation.accounting')</span></li>-->

                    @php
                        // Check if the current route matches any of the accounting pages
                        $isAccountingActive = Request::is('accounting*') || Request::is('accounting*');
                        $isAccountingCoaActive = Request::is('accounting/chartofaccount') || Request::is('accounting/chartofaccount*');
                        $isAccountingVocuherActive = Request::is('accounting/vouchers') || Request::is('accounting/vouchers*');
                        $isAccountingVocuherActiveC = Request::is('accounting/cashbook') || Request::is('accounting/cashbook*');
                        $isAccountingVocuherActiveB = Request::is('accounting/bankbook') || Request::is('accounting/bankbook*');
                        $isAccountingLedgerActive = Request::is('accounting/ledgers') || Request::is('accounting/ledgers*');
                        $isAccountingTrailbalanceActive = Request::is('accounting/trailbalance') || Request::is('accounting/trailbalance*');
                        $isAccountingReportsActive = Request::is('accounting/accounting-reports') || Request::is('accounting/accounting-reports*');
                    @endphp

                    @php
                        $hasAccountingChartofAccountAccess = auth()->user()->can('accounting chart of account view');
                        $hasAccountingCashbookAccess = auth()->user()->can('accounting cashbook view');
                        $hasAccountingBankbookAccess = auth()->user()->can('accounting bankbook view');
                        $hasAccountingJournalVoucherAccess = auth()->user()->can('accounting journalvoucher view');
                        $hasAccountingLedgerAccess = auth()->user()->can('accounting ledgers view');
                        $hasAccountingTrialBalanceAccess = auth()->user()->can('accounting trialbalance view');
                    @endphp


        @if ($hasAccountingChartofAccountAccess || $hasAccountingCashbookAccess || $hasAccountingBankbookAccess || $hasAccountingJournalVoucherAccess || $hasAccountingLedgerAccess || $hasAccountingTrialBalanceAccess)


                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $isAccountingActive ? 'active' : '' }}" href="#sidebarAccounting" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $isAccountingActive ? 'true' : 'false' }}" aria-controls="sidebarAccounting">
                            <i class="ri-calculator-line"></i> <span>@lang('translation.accounting')</span>
                        </a>

                    <div class="collapse menu-dropdown {{ $isAccountingActive ? 'show' : '' }}" id="sidebarAccounting">
                        <ul class="nav nav-sm flex-column">
                        @if($hasAccountingChartofAccountAccess)
                            <li class="nav-item">
                                <a href="{{ url('accounting/chartofaccount') }} " class="nav-link {{ $isAccountingCoaActive ? 'active' : '' }}">Chart of Account</a>
                            </li>
                        @endif

                        @if($hasAccountingCashbookAccess)
                            <li class="nav-item">
                                <a href="{{ url('accounting/cashbook') }} " class="nav-link {{ $isAccountingVocuherActiveC ? 'active' : '' }}">Cash Book</a>
                            </li>
                        @endif

                        @if($hasAccountingBankbookAccess)
                            <li class="nav-item">
                                <a href="{{ url('accounting/bankbook') }} " class="nav-link {{ $isAccountingVocuherActiveB ? 'active' : '' }}">Bank Book</a>
                            </li>
                        @endif

                        @if($hasAccountingJournalVoucherAccess)

                            <li class="nav-item">
                                <a href="{{ url('accounting/vouchers') }} " class="nav-link {{ $isAccountingVocuherActive ? 'active' : '' }}">Journal Voucher</a>
                            </li>
                        @endif

                        @if($hasAccountingLedgerAccess)
                            <li class="nav-item">
                                <a href="{{ url('accounting/ledgers') }}" class="nav-link {{ $isAccountingLedgerActive ? 'active' : '' }}">Ledger</a>
                            </li>
                        @endif

                            <!--<li class="nav-item">
                                <a href="{{ url('cheques') }}" class="nav-link">Cheques</a>
                            </li>-->

                            @if($hasAccountingTrialBalanceAccess)
                                <li class="nav-item">
                                            <a href="{{ url('accounting/trailbalance') }}" class="nav-link {{ $isAccountingTrailbalanceActive ? 'active' : '' }}">Trial Balance</a>
                                        </li>
                            @endif

                            <li class="nav-item">
                                <a href="{{ url('accounting/accounting-reports') }}" class="nav-link {{ $isAccountingReportsActive ? 'active' : '' }}">
                                    Reports
                                </a>
                            </li>


                        </ul>
                    </div>
                </li><!-- end Accounting Menu -->

            @endif

            @php
                        // Check if the current route matches any of the inventory pages
                        $isFarmsActive = Request::is('farms*') || Request::is('farms/farms') ||
                        Request::is('farms/invoicesfarms') || Request::is('farms/bills/farms') || Request::is('farms/expenses') || Request::is('farms/stocks');
                        $isFarmsListActive = Request::is('farms/farms');
                        $isFarmInvoicesActive = Request::is('farms/invoicesfarms');
                        $isFarmBillsActive = Request::is('farms/bills/farms');

                        $isFarmExpsActive = Request::is('farms/expenses');
                        $isFarmStocksActive = Request::is('farms/stock');


            @endphp


            <li class="nav-item">
                    <a class="nav-link menu-link  {{ $isFarmsActive ? 'active' : '' }}" href="#sidebarFarms" data-bs-toggle="collapse" role="button"
                        aria-expanded="{{ $isFarmsActive ? 'true' : 'false' }}" aria-controls="sidebarFarms">
                        <i class="bx bx-building-house"></i> <span>Farms</span>
                    </a>
                    <div class="collapse menu-dropdown {{ $isFarmsActive ? 'show' : '' }}" id="sidebarFarms">
                        <ul class="nav nav-sm flex-column">

                            <li class="nav-item">
                                <a class="nav-link {{ $isFarmsListActive ? 'active' : '' }}" href="{{ url('farms') }}" >
                                    <!--<i class="ri-user-shared-line"></i>-->
                                    <span>Farms</span>
                                </a>
                            </li> <!-- end custoemrs Menu -->


                            <li class="nav-item">
                                 <a class="nav-link {{ $isFarmInvoicesActive ? 'active' : '' }}" href="{{ url('farms/invoicesfarms') }}" >
                                    <!--<i class="ri-user-shared-line"></i>-->
                                    <span>Sales</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ url('farms/bills/farms') }}" class="nav-link {{ $isFarmBillsActive ? 'active' : '' }}">
                                <span>Purchases</span></a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ url('farms/expenses') }}" class="nav-link {{ $isFarmExpsActive ? 'active' : '' }}">
                                <span>Expenses</span></a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ url('farms/stock') }}" class="nav-link {{ $isFarmStocksActive ? 'active' : '' }}">
                                <span>Stock</span></a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ url('farms/reports') }}" class="nav-link {{ $isFarmStocksActive ? 'actived' : '' }}">
                                <span>Reports</span></a>
                            </li>

                        </ul>
                    </div>
                </li><!-- end Purchase Menu -->



            @php
                        $hasInventoryAccess = auth()->user()->can('inventory view');
            @endphp



            @php
            $userId = auth()->id(); // Get the logged-in user's ID
            @endphp


            @if($segments->type == 'Poultry Farm')
                 <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ url('farmcenters') }}"  role="button"
                        aria-expanded="false" aria-controls="sidebarDashboards">
                        <i class="ri-rocket-line"></i> <span>Farms <small>(Cost Centers)</small></span>
                    </a>
                </li> <!-- end Dashboard Menu -->
            @endif





        @php
                        // Check if the current route matches any of the inventory pages
                        $isInventoryActive = Request::is('inventory*') || Request::is('salesproducts*') || Request::is('purchaseitems*');
                        $isInventorySalesProductActive = Request::is('inventory/salesproducts') || Request::is('inventory/salesproducts*');
                        $isInventoryPurchaseItemsActive = Request::is('inventory/purchaseitems') || Request::is('inventory/purchaseitems*');
                        $isInventoryConsumptionActive = Request::is('inventory/productions') || Request::is('inventory/productions*');
                        $isInventoryStockActive = Request::is('inventory/stock') || Request::is('inventory/stock/*');
                        $isInventoryStockActiveGoods = Request::is('inventory/stockgoods') || Request::is('inventory/stockgoods*');
                        $isInventoryAdjustmentsActive = Request::is('inventory/stockadjustments') || Request::is('inventory/stockadjustments*');
            @endphp

            @php
                        $hasInventoryAccess = auth()->user()->can('inventory view');
            @endphp

        @if($hasInventoryAccess)

                <li class="nav-item">
                        <a class="nav-link menu-link {{ $isInventoryActive ? 'active' : '' }}" href="#sidebarInventory" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $isInventoryActive ? 'true' : 'false' }}" aria-controls="sidebarInventory">
                            <i class="bx bx-trending-up"></i> <span>Inventory</span>
                        </a>
                    <div class="collapse menu-dropdown {{ $isInventoryActive ? 'show' : '' }}" id="sidebarInventory">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ $isInventorySalesProductActive ? 'active' : '' }}" href="{{ url('inventory/salesproducts')}}"  role="button"
                                    aria-expanded="false" aria-controls="sidebarDashboards">
                                    <!--<i class=" ri-user-received-line"></i>--> <span>Sales Products</span>
                                </a>
                            </li> <!-- end vendors Menu -->
                            <li class="nav-item">
                                <a href="{{ url('inventory/purchaseitems')}}" class="nav-link {{ $isInventoryPurchaseItemsActive ? 'active' : '' }}">Purchase Items</a>
                            </li>

                            @if($segments->type == "Feed Mill")
                            <li class="nav-item">
                                <a href="{{ url('inventory/productions')}}" class="nav-link {{ $isInventoryConsumptionActive ? 'active' : '' }}">
                                    Consumption</a>
                            </li>


                            <li class="nav-item">
                                <a href="{{ url('inventory/stockadjustments')}}" class="nav-link {{ $isInventoryAdjustmentsActive ? 'active' : '' }}">
                                    Adjustments</a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ url('inventory/stock')}}" class="nav-link {{ $isInventoryStockActive ? 'active' : '' }}">
                                    Stock Statement</a>
                            </li>

                            <li class="nav-item d-none">
                                <a href="{{ url('inventory/stockgoods')}}" class="nav-link {{ $isInventoryStockActiveGoods ? 'active' : '' }}">
                                    Stock (Finished Goods)</a>
                            </li>

                            <li class="nav-item d-none">
                                <a href="{{ url('inventory/consumption')}}" class="nav-link {{ $isInventoryPurchaseItemsActive ? 'dactive' : '' }}">
                                    Price List</a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ url('inventory/stockreports')}}" class="nav-link {{ $isInventoryPurchaseItemsActive ? 'dactive' : '' }}">
                                    Reports</a>
                            </li>
                            @endif

                        </ul>
                    </div>
                </li><!-- end Purchase Menu -->

            @endif

                @php
                        // Check if the current route matches any of the inventory pages
                        $isWeighbridgeActive = Request::is('weighbridge*') || Request::is('inwards*') || Request::is('outwards*');
                        $isWeighbridgeInwardsActive = Request::is('weighbridge/inwards');
                        $isWeighbridgeOutwardsActive = Request::is('weighbridge/outwards');
                @endphp

                @php
                        $hasWeighbridgeAccess = auth()->user()->can('weighbridge view');
            @endphp


        @if($hasWeighbridgeAccess)

            @if($segments->type == "Feed Mill")
                <li class="nav-item d-none">
                    <a class="nav-link menu-link {{ $isWeighbridgeActive ? 'active' : '' }}" href="#sidebarWeighbride" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ $isWeighbridgeActive ? 'true' : 'false' }}" aria-controls="sidebarWeighbride">
                                <i class="ri-scales-line"></i> <span>Weighbridge</span>
                            </a>
                        <div class="collapse menu-dropdown {{ $isWeighbridgeActive ? 'show' : '' }}" id="sidebarWeighbride">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a class="nav-link {{ $isWeighbridgeInwardsActive ? 'active' : '' }}"
                                    href="{{ url('weighbridge/inwards')}}"  role="button"
                                        aria-expanded="false" aria-controls="sidebarWeighbride">
                                        <!--<i class=" ri-user-received-line"></i>--> <span>Inwards</span>
                                    </a>
                                </li> <!-- end vendors Menu -->
                                <li class="nav-item">
                                    <a href="{{ url('weighbridge/outwards')}}" class="nav-link {{ $isWeighbridgeOutwardsActive ? 'active' : '' }}">Outwards</a>
                                </li>
                            </ul>
                        </div>
                </li> <!-- end custoemrs Menu -->
            @endif
        @endif

                <!--<li class="menu-title"><i class="ri-more-fill"></i> <span>@lang('translation.configuration')</span></li>-->

                @php
                    $hasConfigurationAccess = auth()->user()->can('users view') || auth()->user()->can('roles view') || auth()->user()->can('permissions view');
                @endphp

                @php
                    // Check if the current route matches any of the configuration pages
                    $isConfigurationActive = Request::is('users*') || Request::is('roles*') || Request::is('permissions*')
                    || Request::is('segments*') || Request::is('cost-centers*');
                @endphp

                @if($hasConfigurationAccess)
                    @if($segments->type == "Feed Mill")
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $isConfigurationActive ? 'active' : '' }}" href="#sidebarConfiguration" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $isConfigurationActive ? 'true' : 'false' }}" aria-controls="sidebarConfiguration">
                            <i class="ri-settings-4-line"></i> <span>@lang('translation.configuration')</span>
                        </a>
                        <div class="collapse menu-dropdown {{ $isConfigurationActive ? 'show' : '' }}" id="sidebarConfiguration">
                            <ul class="nav nav-sm flex-column">

                                @role('Super Admin|Administrator' )
                                    @if(env('ALLOW_SEGMENTS', true))
                                        <li class="nav-item">
                                                <a href="{{ url('segments') }}" class="nav-link {{ Request::is('segments*') ? 'active' : '' }}">
                                                    Segments</a>
                                        </li>

                                        <li class="nav-item">
                                                <a href="{{ url('cost-centers') }}" class="nav-link {{ Request::is('cost-centers*') ? 'active' : '' }}">
                                                    Cost Centers</a>
                                        </li>
                                    @endif

                                @endrole



                                @can('users view')
                                    <li class="nav-item">
                                        <a href="{{ url('users') }}" class="nav-link {{ Request::is('users*') ? 'active' : '' }}">@lang('translation.users')</a>
                                    </li>
                                @endcan

                                @can('roles view')
                                    <li class="nav-item">
                                        <a href="{{ url('roles') }}" class="nav-link {{ Request::is('roles*') ? 'active' : '' }}">@lang('translation.roles')</a>
                                    </li>
                                @endcan

                                @can('permissions view')
                                    <li class="nav-item">
                                        <a href="{{ url('permissions') }}" class="nav-link {{ Request::is('permissions*') ? 'active' : '' }}">@lang('translation.permissions')</a>
                                    </li>
                                @endcan



                            </ul>
                        </div>
                    </li><!-- end configuration Menu -->
                    @endif
                @endif



                <!--<li class="menu-title"><i class="ri-more-fill"></i> <span>@lang('translation.sales')</span></li>

                <li class="menu-title"><i class="ri-more-fill"></i> <span>@lang('translation.purchase')</span></li>

                <li class="menu-title"><i class="ri-more-fill"></i> <span>@lang('translation.accouting')</span></li>
                -->





            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
