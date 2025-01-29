<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseFarmBillItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_farm_bill_items';


    protected $fillable = [
        'purchase_farm_bill_id',
        'product_id',
        'quantity',
        'deduction',
        'net_quantity',
        'price',
        'gross_amount',
        'sales_tax_rate',
        'sales_tax_amount',
        'withholding_tax_rate',
        'withholding_tax_amount',
        'net_amount'
    ];

    public function bill()
{
    return $this->belongsTo(PurchaseFarmBill::class, 'purchase_farm_bill_id');
}

    public function product()
    {
        return $this->belongsTo(Items::class, 'product_id');
    }
}
