<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function account_groups()
    {
        return $this->hasMany(AccountGroup::class);
    }
}
