<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Concession extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'concessions';

    protected $fillable = [
        'title',
        'amount',
        'percentage',
        'is_used',
    ];
}
