<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpWhitelist extends Model
{
    use HasFactory;

    protected $table = 'ip_whitelists'; // Specify the table name if it's different from the plural form of the model name
    protected $fillable = ['ip_address'];  // Specify the fillable columns
}
