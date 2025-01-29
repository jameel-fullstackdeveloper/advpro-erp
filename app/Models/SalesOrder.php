<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $fillable = [
        'customer_id', 'order_number', 'order_date', 'status', 'company_id', 'financial_year_id', 'created_by', 'updated_by','order_comments','farm_name','farm_address','farm_supervisor_mobile','vehicle_no','vehicle_fare','created'
    ];

    // Cast order_date as a date
    protected $casts = [
        'order_date' => 'date',
    ];

    // Define the relationship with the sales order items
    public function items()
    {
        return $this->hasMany(SalesOrderItem::class,'sales_order_id');
    }

    // Define the relationship with the company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

     // Relationship for the user who created the customer
     public function userCreated()
     {
         return $this->belongsTo(User::class, 'created_by');
     }

     // Relationship for the user who last updated the customer
     public function userUpdated()
     {
         return $this->belongsTo(User::class, 'updated_by');
     }

    // Define the relationship with invoices
    public function invoices()
    {
        return $this->hasMany(SalesInvoice::class);
    }

    // Define the relationship with delivery challans
    public function deliveryChallans()
    {
        return $this->hasMany(DeliveryChallan::class);
    }

    public function customer()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    /**
     * Relationship with WeighbridgeOutward (many-to-many).
     */
    public function weighbridgeOutwards()
    {
        return $this->belongsToMany(WeighbridgeOutward::class, 'weighbridge_outward_order')
                    ->withPivot('order_weight') // Include the order_weight from the pivot table
                    ->withTimestamps();
    }


}
