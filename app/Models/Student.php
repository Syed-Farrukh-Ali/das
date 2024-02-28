<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'students';

    protected $guarded = [];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function globalSection()
    {
        return $this->belongsTo(GlobalSection::class);
    }

    public function registrationcard()
    {
        return $this->hasOne(RegistrationCard::class);
    }

    public function entrytestdetail()
    {
        return $this->hasOne(EntrytestDetail::class);
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function feeChallans()
    {
        return $this->hasMany(FeeChallan::class);
    }

    public function feeChallanDetails()
    {
        return $this->hasMany(FeeChallanDetail::class);
    }
public function feeChallanDetailsLast()
    {
        return $this->hasOne(FeeChallanDetail::class)->orderBy('fee_month', 'desc');
    }
     public function feeChallanDetailsmonthly()
    {
        return $this->hasMany(FeeChallanDetail::class)->where('fee_month','2023-05-01');
    }

    public function studentLiableFees()
    {
        return $this->hasMany(StudentLiableFee::class);
    }
 public function studentLiableFeesMonthly()
    {
       // return $this->hasOne(StudentLiableFee::class)->where('fees_type_id','4');
        return $this->hasMany(StudentLiableFee::class);
    }
    public function admission()
    {
        return $this->hasOne(Admission::class);
    }

    public function setLiableFees($data)
    {


        foreach ($data['fee_type_id'] as $key => $fee_type_id) {
            $this->studentLiableFees()->create([
                'fees_type_id' => $fee_type_id,
                'amount' => $data['fee_after_concession'][$key]  ?? 3300,
                'concession_amount' =>  $data['fee_amount'][$key] - $data['fee_after_concession'][$key],
                'remarks' => null,
            ]);
        }

       return true;
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function fee_return()
    {
        return $this->hasMany(FeeReturn::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
      public function attendances1()
    {
        return $this->hasMany(Attendance::class)->groupBy('student_id')->groupBy('attendance_status_id');
    }
    public function certificate()
    {
        return $this->hasMany(Certificate::class);
    }
    public function certificate_type()
    {
        return $this->hasMany(Certificate_type::class);
    }
}
