<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Facades\Excel;

class CustomerDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'account_id',
        'email',
        'phone',
        'address',
        'cnic',
        'strn',
        'ntn',
        'discount',
        'bonus',
        'credit_limit',
        'payment_terms',
        'financial_year_id',
        'company_id',
        'broker_rate',
        'created_by',
        'updated_by',
        'avatar',
    ];



    // Relationship to ChartOfAccount
    public function coaTitle()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    // Relationship for the ChartOfAccount group
    public function coaGroupTitle()
    {
        return $this->hasOneThrough(
            ChartOfAccountGroup::class,
            ChartOfAccount::class,
            'id', // Foreign key on ChartOfAccount table...
            'id', // Foreign key on ChartOfAccountGroup table...
            'account_id', // Local key on CustomerDetail table...
            'group_id' // Local key on ChartOfAccount table...
        );
    }

    // Relationship for the user who created the customer
    public function userCreated()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship for the user who last updated the customer
    public function userUpdated()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

}

