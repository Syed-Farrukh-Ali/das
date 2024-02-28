<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function base_account()
    {
        return $this->belongsTo(BaseAccount::class);
    }

    public function account_charts()
    {
        return $this->hasMany(AccountChart::class);
    }
}
