<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HeadOffice extends Model
{
    use HasFactory, SoftDeletes;
    // protected $table = 'head_offices';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
