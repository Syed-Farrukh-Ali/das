<?php

namespace App\Models\Notification;

use App\Models\Campus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notification extends Model
{
    protected $guarded = ['id'];

    protected $with = ['campuses','notificationType'];

    protected $casts = [
        'campus_ids' => 'array'
    ];

    public function campuses(): BelongsToMany
    {
        return $this->belongsToMany(Campus::class,);
    }

    public function notificationType(): BelongsTo
    {
        return $this->belongsTo(NotificationType::class);
    }
}
