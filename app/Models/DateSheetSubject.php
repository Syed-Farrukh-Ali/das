<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DateSheetSubject extends Model
{
    protected $guarded = [];

    protected $with = ['subject'];

    public function dateSheet(): BelongsTo
    {
        return $this->belongsTo(DateSheet::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
