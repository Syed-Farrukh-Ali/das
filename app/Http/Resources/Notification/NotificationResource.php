<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'notification_type' => new NotificationTypeResource($this->whenLoaded('notificationType')),
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'date_time' => $this->date_time,
            'campus_ids' => $this->campus_ids,
        ];
    }
}
