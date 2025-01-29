<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItemGroup extends Model
{
    use HasFactory;

    protected $table = 'purchase_items_groups';

    protected $fillable = [
        'name',
        'company_id',
        'created_by',
        'updated_by',
    ];

    /**
     * A PurchaseItemGroup belongs to a company.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * A PurchaseItemGroup has many purchase items.
     */
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class, 'group_id');
    }

    /**
     * A PurchaseItemGroup is created by a user.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * A PurchaseItemGroup is updated by a user.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
