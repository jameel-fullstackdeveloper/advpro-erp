<div>

<div class="row">
    <div class="col-lg-3 col-md-3 d-none">
        <div class="card">
            <div class="card-body">
                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm flex-shrink-0">
                                                        <span class="avatar-title bg-light text-primary rounded-circle fs-3 material-shadow">
                                                            <i class=" ri-hand-coin-line align-middle"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1"> Opening Balance </p>
                                                        <h5 class="mb-0">
                                                            <span class="counter-value" data-target="{{ $cashInHandBalanceBank }}">{{ number_format($cashInHandBalanceBank) }}</span>
                                                            <span style="font-size:12px;">Rs.</span>
                                                        </h5>

                                                    </div>
                                                    <div class="flex-shrink-0 align-self-end d-none">


                                                        <span class="badge bg-success-subtle text-success" title="sum of  accounts"><i class="ri-arrow-up-s-fill align-middle me-1"></i>
                                                        <span> Acc</span></span>
                                                    </div>
                                                </div>

            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-3">
        <div class="card">
            <div class="card-body">
            <div class="d-flex align-items-center">
                                                    <div class="avatar-sm flex-shrink-0">
                                                        <span class="avatar-title bg-light text-primary rounded-circle fs-3 material-shadow">
                                                            <i class="ri-bank-line align-middle"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="text-uppercase fw-semibold fs-12 text-success mb-1">Bank Deposits</p>
                                                        <h5 class="mb-0">
                                                            <span class="counter-value" data-target="{{ $debitTotalBank }}"> {{ number_format($debitTotalBank) }}</span>
                                                            <span style="font-size:12px;">Rs.</span>
                                                        </h5>
                                                    </div>
                                                    <div class="flex-shrink-0 align-self-end d-none">
                                                        <span class="badge bg-success-subtle text-success" title="sum of  accounts"><i class="ri-arrow-up-s-fill align-middle me-1"></i>
                                                        <span> Acc</span></span>
                                                    </div>
                                                </div>

            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm flex-shrink-0">
                                                        <span class="avatar-title bg-light text-primary rounded-circle fs-3 material-shadow">
                                                            <i class=" ri-bank-line align-middle"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="text-uppercase fw-semibold fs-12 text-danger mb-1">Bank Withdraws</p>
                                                        <h5 class="mb-0">
                                                            <span class="counter-value" data-target="{{ $creditTotalBank }}">{{ number_format($creditTotalBank) }}</span>
                                                            <span style="font-size:12px;">Rs.</span>
                                                        </h5>

                                                    </div>
                                                    <div class="flex-shrink-0 align-self-end d-none">
                                                        <span class="badge bg-success-subtle text-success" title="sum of accounts"><i class="ri-arrow-up-s-fill align-middle me-1"></i>
                                                        <span> Acc</span></span>
                                                    </div>
                                                </div>

            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-3 d-none" >
        <div class="card">
            <div class="card-body">
            <div class="d-flex align-items-center">
                                                    <div class="avatar-sm flex-shrink-0">
                                                        <span class="avatar-title bg-light text-primary rounded-circle fs-3 material-shadow">
                                                            <i class=" ri-hand-coin-line align-middle"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="text-uppercase fw-semibold fs-12 text-dark mb-1">Current Balacne</p>
                                                        <h5 class="mb-0">
                                                            <span class="counter-value" data-target="{{ $balanceBank }}"> {{ number_format($balanceBank); }}</span>
                                                            <span style="font-size:12px;">Rs.</span>
                                                        </h5>

                                                    </div>
                                                    <div class="flex-shrink-0 align-self-end">
                                                        <span class="badge bg-success-subtle text-success" title="sum of accounts"><i class="ri-arrow-up-s-fill align-middle me-1"></i>
                                                        <span> Acc</span></span>
                                                    </div>
                                                </div>

            </div>
        </div>
    </div>
</div>


</div>
