<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialTransfer extends Model
{

    protected $table = 'material_transfer_farms';

    protected $fillable = [
        'farm_account', 'transfer_date', 'reference_number',
        'vehicle_no', 'vehicle_fare', 'freight_credit_to', 'comments', 'status', 'company_id', 'financial_year_id', 'created_by'
        ,'updated_by','created_at','updated_at'

    ];


    // Define the relationship with invoice items
    public function items()
    {
        return $this->hasMany(MaterialTransferItem::class,'material_transfer_id');
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

    public function farm()
    {
        return $this->belongsTo(ChartOfAccount::class, 'farm_account');
    }


}
