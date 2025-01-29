<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Costcenter extends Model
{
    use HasFactory;

    protected $table = 'cost_centers';

    protected $fillable = [
        'segment_id',
        'name',
        'abv',
        'address',
        'description',
        'opening_date',
        'closing_date',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the company that owns the cost center (i.e., the segment).
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'segment_id');

    }


    /**
     * Get the user who created the cost center.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by'); // Cost center creator
    }

    /**
     * Get the user who last updated the cost center.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by'); // Cost center updater
    }
}
