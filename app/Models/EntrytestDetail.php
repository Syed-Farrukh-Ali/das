<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EntrytestDetail extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'entrytest_details';

    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
