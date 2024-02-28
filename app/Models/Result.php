<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Result extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->orderBy('admission_id', 'DESC');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function student_class()
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function global_section()
    {
        return $this->belongsTo(GlobalSection::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
}
