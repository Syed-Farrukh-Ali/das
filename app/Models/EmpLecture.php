<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmpLecture extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function student_class()
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function global_section()
    {
        return $this->belongsTo(GlobalSection::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
