<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesProduct extends Model
{
    use HasFactory;

    protected $table = 'sales_products';

    protected $fillable = [
        'product_name',
        'group_id',
        'quantity',
        'price',
        'balance',
        'company_id',
        'financial_year_id',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * A SalesProduct belongs to a sales product group.
     */
    public function group()
    {
        return $this->belongsTo(SalesProductGroup::class, 'group_id');
    }

    /**
     * A SalesProduct belongs to a company.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
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
