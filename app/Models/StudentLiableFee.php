<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentLiableFee extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    // protected $visible = ['id','amount','fees_type_id','duration_type'];
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function feesType()
    {
        return $this->belongsTo(FeesType::class);
    }
}
