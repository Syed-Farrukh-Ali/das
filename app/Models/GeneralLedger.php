<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralLedger extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function account_chart()
    {
        return $this->belongsTo(AccountChart::class);
    }

    public function sub_account()
    {
        return $this->belongsTo(SubAccount::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
}
