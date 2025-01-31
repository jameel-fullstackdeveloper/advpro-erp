<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialTransferItem extends Model
{
    protected $table = 'material_transfer_farms_detail';

    protected $fillable = [
        'material_transfer_id',
        'item_id',
        'quantity',
        'unit_price',
        'gross_amount',
        'discount',
        'net_amount',
        'created_at',
        'created_by',
        'updated_at',

    ];
    // Define the relationship with the sales invoice
    public function salesInvoice()
    {
        return $this->belongsTo(MaterialTransfer::class);
    }

    // Define the relationship with the product
    public function product()
    {
        return $this->belongsTo(Items::class,'item_id');
    }
}
