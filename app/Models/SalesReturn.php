<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    use HasFactory;
    protected $fillable = [
        'return_number',
        'return_date',
        'customer_id',
        'status',
        'company_id',
        'financial_year_id',
        'created_by',
        'updated_by'
    ];

    public function items()
    {
        return $this->hasMany(SalesReturnItem::class, 'sales_return_id');
    }

    public function customer()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    // Define the relationship with users for created by
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Define the relationship with users for updated by
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

}
