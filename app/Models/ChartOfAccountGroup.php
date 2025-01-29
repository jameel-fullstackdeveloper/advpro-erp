<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccountGroup extends Model
{
    use HasFactory;

    protected $table = 'chart_of_accounts_groups';

    protected $fillable = [
        'name',
        'type_id',
        'company_id',
        'is_customer_vendor',
        'created_by',
        'updated_by',
    ];

    // A group belongs to a type
    public function chartOfAccountsType()
    {
        return $this->belongsTo(ChartOfAccountsType::class, 'type_id');
    }

    // A group has many accounts
    public function chartOfAccounts()
    {
        return $this->hasMany(ChartOfAccount::class, 'group_id');
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

