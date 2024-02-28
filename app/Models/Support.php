<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Support extends Model
{
    protected $guarded = [];

    protected $with = ['campus'];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }
}
