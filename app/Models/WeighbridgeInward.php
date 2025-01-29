<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeighbridgeInward extends Model
{
    use HasFactory;



    protected $fillable = [
        'truck_number',
        'billty_number',
        'freight',
        'total_bags',
        'party_gross_weight',
        'party_tare_weight',
        'party_net_weight',
        'first_weight',
        'second_weight',
        'net_weight',
        'driveroption',
        'comments',
        'first_weight',
        'company_id',
        'financial_year_id',
        'first_weight_datetime',
        'second_weight_datetime',
        'created_by',
        'updated_by',
    ];

    /**
     * Relationship with sales orders (many-to-many via pivot table).
     */
    public function salesOrders()
    {
        return $this->belongsToMany(SalesOrder::class, 'weighbridge_outward_order')
                    ->withPivot('order_weight') // Include the order_weight from the pivot table
                    ->withTimestamps();
    }

    /**
     * Automatically calculate net weight.
     */
    public function calculateNetWeight()
    {
        if ($this->first_weight && $this->second_weight) {
            $this->net_weight = $this->second_weight - $this->first_weight;
        }
    }

}
