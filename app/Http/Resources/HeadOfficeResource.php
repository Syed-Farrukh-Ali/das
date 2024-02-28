<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HeadOfficeResource extends JsonResource
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
            'user_id' => $this->user_id,
            'title' => $this->title,
            'address' => $this->address,
            'city' => $this->city,
            'province' => $this->province,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,

            'user' => $this->user ? [
                'email' => $this->user->email,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,

            ] : 'does not have user',
        ];
    }
}
