<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoiceFarm extends Model
{

    protected $table = 'sales_invoice_farms';

    protected $fillable = [
        'sales_order_id', 'invoice_number', '',
        'customer_id', 'invoice_date', 'invoice_due_days', 'status', 'company_id', 'financial_year_id', 'created_by', 'updated_by'
        ,'freight_credit_to','is_weighbridge','broker_id','broker_rate','calculation_method','broker_amount','comments',
        'segment_id','cost_center_id','vehicle_no','farm_name','farm_address','farm_account'

    ];

    // Define the relationship with the sales order
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    // Define the relationship with invoice items
    public function items()
    {
        return $this->hasMany(SalesInvoiceFarmItem::class);
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

    public function customer()
    {
        return $this->belongsTo(ChartOfAccount::class, 'customer_id');
    }


}
