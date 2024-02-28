<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountChart extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function account_group()
    {
        return $this->belongsTo(AccountGroup::class);
    }

    public function sub_accounts()
    {
        return $this->hasMany(SubAccount::class);
    }

    public function general_ledgers()
    {
        return $this->hasMany(GeneralLedger::class);
    }
}
