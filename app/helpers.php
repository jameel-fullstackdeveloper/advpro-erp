<?php

use App\Models\FinancialYear;

if (!function_exists('getCurrentFinancialYear')) {
    function getCurrentFinancialYear()
    {
        $today = now();
        return FinancialYear::where('start_date', '<=', $today)
                            ->where('end_date', '>=', $today)
                            ->first();
    }
}
