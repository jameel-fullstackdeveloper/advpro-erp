<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeighbridgeInwardOrder extends Model
{
    use HasFactory;

    protected $table = 'weighbridge_inward_orders';

    protected $fillable = [
        'weighbridge_inward_id',
        'purchase_order_id',
        'order_weight',
    ];

}
