<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoiceItem extends Model
{


    protected $fillable = [
        'sales_invoice_id',
        'product_id',
        'quantity',
        'unit_price',
        'net_amount',
        'discount_rate',
        'discount_type',
        'discount_amount',
        'discount_per_bag_rate',
        'discount_per_bag_amount',
        'amount_excl_tax',
        'sales_tax_rate',
        'sales_tax_type',
        'sales_tax_amount',
        'further_sales_tax_rate',
        'further_sales_tax_amount',
        'advance_wht_rate',
        'advance_wht_amount',
        'amount_incl_tax',
        'created_by',
        'updated_by'
    ];
    // Define the relationship with the sales invoice
    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    // Define the relationship with the product
    public function product()
    {
        return $this->belongsTo(Items::class);
    }
}
