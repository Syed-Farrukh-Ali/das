<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HostelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'campus_id' => $this->campus_id,
            'name' => $this->name,
            'address_1' => $this->address_1,
            'address_2' => $this->address_2,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,

        ];
    }
}
