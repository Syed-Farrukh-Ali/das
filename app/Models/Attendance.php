<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function student_class()
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function global_section()
    {
        return $this->belongsTo(GlobalSection::class);
    }

    public function attendance_status()
    {
        return $this->belongsTo(AttendanceStatus::class);
    }
}
