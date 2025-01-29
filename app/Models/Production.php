<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_date',
        'product_id',
        'lots',
        'perlots',
        'defaultbags_perlot',
        'short_perlot',
        'excess_perlot',
        'actual_produced',
        'comments',
        'company_id',
        'financial_year_id',
        'created_by',
        'updated_by',
    ];

    /*public function finishedProduct()
    {
        return $this->belongsTo(SalesProduct::class, 'finished_product_id');
    }*/

    // In the Production model
    public function finishedProduct()
    {
        return $this->belongsToMany(Items::class, 'production_bags', 'production_id', 'product_id')
                    ->withPivot('quantity')
                    ->withTimestamps(); // Assuming the pivot table has timestamp columns
    }

    public function product()
    {
        return $this->belongsTo(Items::class, 'product_id');
    }


    public function details()
    {
        return $this->hasMany(ProductionDetail::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function bags()
    {
        return $this->hasMany(ProductionBag::class);
    }



}
