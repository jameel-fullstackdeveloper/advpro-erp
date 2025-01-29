<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_items';

    protected $fillable = [
        'item_name',
        'group_id',
        'price',
        'balance',
        'can_be_sold',
        'company_id',
        'financial_year_id',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * A PurchaseItem belongs to a purchase item group.
     */
    public function group()
    {
        return $this->belongsTo(PurchaseItemGroup::class, 'group_id');
    }

    /**
     * A PurchaseItem belongs to a company.
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
