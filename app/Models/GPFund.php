<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GPFund extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function employees()
    {
        return $this->belongsTo(Employee::class);
    }
}
