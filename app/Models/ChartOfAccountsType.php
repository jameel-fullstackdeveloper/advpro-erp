<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccountsType extends Model
{
    use HasFactory;

    protected $table = 'chart_of_accounts_types';

    protected $fillable = ['name', 'category', 'company_id', 'created_by', 'updated_by'];

    // A type has many groups
    public function chartOfAccountGroups()
    {
        return $this->hasMany(ChartOfAccountGroup::class, 'type_id');
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

