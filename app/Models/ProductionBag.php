<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionBag extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_id',
        'product_id',
        'quantity',
    ];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }
}
