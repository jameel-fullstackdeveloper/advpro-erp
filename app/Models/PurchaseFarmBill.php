<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseFarmBill extends Model
{
    use HasFactory;

    protected $table = 'purchase_farm_bills';

    protected $fillable = [
        'bill_number',
        'vendor_id',
        'order_id',
        'bill_date',
        'bill_due_days',
        'delivery_mode',
        'vehicle_no',
        'freight',
        'status',
        'farm_account',
        'comments',
        'company_id',
        'created_by',
        'broker_id',
        'broker_rate',
        'broker_amount',
        'broker_wht_rate',
        'calculation_method',
        'broker_wht_amount',
        'broker_amount_with_wht',
        'is_weighbridge',
        'updated_by',
        'segment_id',
        'cost_center_id'
    ];

    public function items()
    {
        return $this->hasMany(PurchaseFarmBillItem::class);
    }

    public function vendor()
    {
        return $this->belongsTo(ChartOfAccount::class, 'vendor_id');
    }

    public function order()
    {
        return $this->belongsTo(PurchaseOrder::class, 'order_id');
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
