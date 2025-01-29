<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeighbridgeOutwardOrder extends Model
{
    use HasFactory;

    protected $table = 'weighbridge_outward_order';

    protected $fillable = ['weighbridge_outward_id', 'sales_order_id', 'order_weight'];

}
