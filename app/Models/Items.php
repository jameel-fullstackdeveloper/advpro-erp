<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'items';

    // The primary key of the table
    protected $primaryKey = 'id';

    // Enable auto-increment for the primary key
    public $incrementing = true;

    // The attributes that are mass assignable
    protected $fillable = [
        'name',
        'item_type',
        'item_group_id',
        'purchase_price',
        'sale_price',
        'balance',
        'can_be_sale',
        'can_be_purchase',
        'company_id',
        'created_by',
        'updated_by',
    ];

    // The attributes that should be cast to native types
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Defining the relationship with the ItemGroup model
    public function itemGroup()
    {
        return $this->belongsTo(ItemGroup::class, 'item_group_id', 'id');
    }

    public function userCreated()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function userUpdated()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
