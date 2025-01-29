<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemGroup extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'item_groups';

    // The primary key of the table
    protected $primaryKey = 'id';

    // Enable auto-increment for the primary key
    public $incrementing = true;

    // The attributes that are mass assignable
    protected $fillable = [
        'name',
        'description',
        'created_by',
        'updated_by',
    ];

    // The attributes that should be cast to native types
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Defining the relationship with the Item model
    public function items()
    {
        return $this->hasMany(Items::class, 'item_group_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ItemGroup model
    public function purchaseItems()
    {
        return $this->hasMany(Items::class, 'item_group_id', 'id')->where('item_type', 'purchase');
    }
}
