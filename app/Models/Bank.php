<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'banks';

    protected $guarded = [];

    public function headoffice()
    {
        return $this->belongsTo(HeadOffice::class);
    }
}
