<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_type', 'date', 'reference_number', 'total_amount', 'description', 'status', 'created_by','updated_by','company_id','financial_year_id','image_path',
        'segment_id','cost_center_id','farm_account','exp_to'

    ];

    public function voucherDetails()
    {
        return $this->hasMany(VoucherDetail::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Relationship to the CostCenter model
    public function costCenter()
    {
        return $this->belongsTo(Costcenter::class, 'cost_center_id'); // Define the foreign key as cost_center_id
    }


}
