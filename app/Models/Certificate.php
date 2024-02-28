<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificate extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function certificate_type()
    {
        return $this->belongsTo(Certificate_type::class);
    }
}
