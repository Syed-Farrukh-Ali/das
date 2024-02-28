<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeesType extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    protected $table = 'fees_types';

    public function studentLiableFees()
    {
        return $this->hasMany(StudentLiableFee::class);
    }

    public function fee_return()
    {
        return $this->hasMany(FeeReturn::class);
    }
}
