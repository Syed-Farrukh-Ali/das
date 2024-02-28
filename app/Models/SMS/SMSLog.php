<?php

namespace App\Models\SMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SMSLog extends Model
{
    protected $guarded = ['id'];

    protected $with = ['smsType'];

    protected $casts = [
        'user' => 'array'
    ];

    public function smsType(): BelongsTo
    {
        return $this->belongsTo(SmsType::class);
    }
}
