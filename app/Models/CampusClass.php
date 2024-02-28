<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampusClass extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    protected $table = 'campus_classes';

    public function campus()
    {
        return $this->hasMany(Campus::class,'student_class_id');
    }
 public function student()
    {
        return $this->belongsTo(StudentClass::class,'student_class_id');
    }
     public function section()
    {
        return $this->belongsTo(GlobalSection::class,'global_section_id');
    }
}
