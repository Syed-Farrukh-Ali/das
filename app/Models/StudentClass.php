<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentClass extends Model
{
    use HasFactory,SoftDeletes;

    protected $visible = ['id','name'];

    protected $guarded = [];

    protected $table = 'student_classes';

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function emp_lectures()
    {
        return $this->hasMany(EmpLecture::class);
    }

    public function exam()
    {
        return $this->belongsToMany(Exam::class);
    }

    public function date_sheet()
    {
        return $this->hasMany(DateSheet::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'student_class_subject');
    }
}
