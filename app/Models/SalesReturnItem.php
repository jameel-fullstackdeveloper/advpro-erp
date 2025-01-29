<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturnItem extends Model
{
    use HasFactory;


    protected $fillable = [
        'sales_return_id',
        'product_id',
        'return_quantity',
        'unit_price',
        'return_amount',
    ];


    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class, 'sales_return_id');
    }
}
