<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryChallan extends Model
{
    protected $fillable = [
        'sales_order_id', 'challan_number', 'delivery_date', 'driver_name', 'vehicle_number', 'status', 'company_id', 'financial_year_id', 'created_by', 'updated_by'
    ];

    // Define the relationship with the sales order
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    // Define the relationship with the company
    public function company()
    {
        return $this->belongsTo(Company::class);
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
