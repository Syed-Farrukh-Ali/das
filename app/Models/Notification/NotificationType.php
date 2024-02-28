<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationType extends Model
{
    protected $guarded = ['id'];

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
