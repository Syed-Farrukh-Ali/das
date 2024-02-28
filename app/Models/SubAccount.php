<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'sub_accounts';

    public function account_chart()
    {
        return $this->belongsTo(AccountChart::class);
    }

    public function general_ledgers()
    {
        return $this->hasMany(GeneralLedger::class);
    }
    public function tempgeneral_ledgers()
    {
        return $this->hasMany(TempGeneralLedger::class);
    }
    public function loans()
    {
        return $this->hasMany(loan::class);
    }

    public function fee_returns()
    {
        return $this->hasMany(FeeReturn::class);
    }

    public function fee_return_account()
    {
        return $this->hasMany(FeeReturn::class, 'id', 'fee_sub_account_id');
    }
}
