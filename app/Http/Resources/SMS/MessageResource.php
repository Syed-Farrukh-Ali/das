<?php

namespace App\Http\Resources\SMS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'sms_type' => new SmsTypeResource($this->whenLoaded('smsType')),
            'date_time' => $this->date_time,
            'user' => $this->user,
            'number' => $this->number,
            'message' => $this->message,
        ];
    }
}
