<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMaterialAdjustment extends Model
{

    use HasFactory;

    // Define the table associated with the model
    protected $table = 'stock_material_adjustments';

    // Disable the timestamps if not needed (Laravel automatically manages created_at and updated_at)
    public $timestamps = true;

    // Define the fillable properties for mass assignment
    protected $fillable = [
        'adj_date',
        'material_id',
        'shortage',
        'exccess',
        'created_by',
        'updated_by',
    ];

    // Define the relationships for created_by and updated_by if they reference a User model
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function purchaseItem()
    {
        return $this->belongsTo(Items::class, 'material_id');
    }

}
