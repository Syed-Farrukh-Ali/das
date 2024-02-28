<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamType extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
