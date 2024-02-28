<?php

namespace App\Models\SMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsType extends Model
{
    protected $guarded = ['id'];

    public function messages(): HasMany
    {
        return $this->hasMany(SMSLog::class);
    }
}
