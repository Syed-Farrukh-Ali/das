<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SupportResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'phone' => $this->phone,
            'file' => Storage::disk('campus_support')->url($this->file),
            'campus' => new CampusResource($this->whenLoaded('campus')),
        ];
    }
}
