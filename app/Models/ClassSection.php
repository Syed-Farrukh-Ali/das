<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassSection extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
public function student()
    {
        return $this->belongsTo(StudentClass::class,'student_class_id')->select(['id', 'name']);
    }
     public function section()
    {
        return $this->belongsTo(GlobalSection::class,'global_section_id')->select(['id', 'name']);
    }
}
