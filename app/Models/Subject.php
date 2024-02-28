<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    public function Students()
    {
        return $this->belongsToMany(Student::class)->orderBy('admission_id', 'DESC');
    }

    public function results()
    {
        return $this->hasMany(Result::class)->orderBy('admission_id', 'DESC');
    }

    public function studentClasses()
    {
        return $this->belongsToMany(StudentClass::class, 'student_class_subject');
    }
}
