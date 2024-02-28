<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeStructure extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }
}
