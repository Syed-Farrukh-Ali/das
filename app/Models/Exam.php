<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    public function exam_type()
    {
        return $this->belongsTo(ExamType::class);
    }

    public function date_sheets()
    {
        return $this->hasMany(DateSheet::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function student_classes()
    {
        return $this->belongsToMany(StudentClass::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
}
