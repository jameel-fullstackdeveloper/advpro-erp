<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesProductGroup extends Model
{
    use HasFactory;

    protected $table = 'sales_products_groups';

    protected $fillable = [
        'name',
        'company_id',
        'created_by',
        'updated_by',
    ];

    /**
     * A SalesProductGroup belongs to a company.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * A SalesProductGroup has many sales products.
     */
    public function salesProducts()
    {
        return $this->hasMany(SalesProduct::class, 'group_id');
    }

    /**
     * A SalesProductGroup is created by a user.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * A SalesProductGroup is updated by a user.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
