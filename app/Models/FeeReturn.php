<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeReturn extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function sub_account()
    {
        return $this->belongsTo(SubAccount::class);
    }

    public function fee_sub_account()
    {
        return $this->belongsTo(SubAccount::class, 'fee_sub_account_id', 'id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function feesType()
    {
        return $this->belongsTo(FeesType::class);
    }
}
