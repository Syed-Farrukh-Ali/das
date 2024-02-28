<?php

namespace App\Http\Resources\SMS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SmsTypeResource extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
