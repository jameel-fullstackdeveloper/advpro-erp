<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'price',
        'total_price',
    ];

     // Define the relationship with the PurchaseItem (Product)
     public function product()
     {
         return $this->belongsTo(Items::class, 'product_id');
     }


}
