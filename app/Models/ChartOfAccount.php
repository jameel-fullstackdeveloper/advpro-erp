<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'name',
        'group_id',
        'balance',
        'drcr',
        'is_customer_vendor',
        'is_farm',
        'company_id',
        'cost_center_id',
        'created_by',
        'updated_by',
    ];

    //for voucher system
    public function vouchers()
    {
        return $this->hasMany(VoucherDetail::class);
    }

    // Correct relationship to the parent group
    public function chartOfAccountGroup()
    {
        return $this->belongsTo(ChartOfAccountGroup::class, 'group_id');
    }

    public function userCreated()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function userUpdated()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }


    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // Relationship to the CostCenter model
    public function costCenter()
    {
        return $this->belongsTo(Costcenter::class, 'cost_center_id'); // Define the foreign key as cost_center_id
    }

    public function customerGroup()
    {
        return $this->belongsTo(ChartOfAccountGroup::class, 'group_id'); // Assuming the foreign key is 'group_id'
    }

}

