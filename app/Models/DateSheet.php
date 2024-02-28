<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DateSheet extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student_class(): BelongsTo
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function dateSheetSubjects(): HasMany
    {
        return $this->hasMany(DateSheetSubject::class);
    }
}
