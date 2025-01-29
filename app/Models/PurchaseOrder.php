<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_date',
        'order_number',
        'vendor_id',
        'price',
        'total_quantity',
        'remaining_quantity',
        'status',
        'comments',
        'company_id',
        'financial_year_id',
        'delivery_mode',
        'broker_id',
        'credit_days',
        'created_by',
        'updated_by',
    ];

    // Define the relationship with the ChartOfAccount (vendor)
    public function vendor()
    {
        return $this->belongsTo(ChartOfAccount::class, 'vendor_id');
    }
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
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

     public function purchaseBills()
    {
        return $this->hasMany(PurchaseBill::class, 'order_id');
    }

}
