<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeChallanDetail extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];
 public function feeChallan()
    {
        return $this->belongsTo(FeeChallan::class);
    }
 public function feeChallanreport()
    {
        return $this->belongsTo(FeeChallan::class,'fee_challan_id','id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
     public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
 public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }
     public function studentClassreport()
    {
        return $this->belongsTo(StudentClass::class,'student_class_id','id');
    }
}
