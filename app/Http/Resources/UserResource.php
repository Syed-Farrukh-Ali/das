<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'campus_id' => $this->campus_id,
            'role' => $this->roles()->pluck('name'),
            'campus' => $this->campus()->exists() ? [
                'id' => $this->campus->id,
                'name' => $this->campus->name,
                'code' => $this->campus->code,
                'area' => $this->campus->area,
                'city' => $this->campus->city,
                'province' => $this->campus->province,
                'contact' => $this->campus->contact,
                'stutus' => $this->campus->stutus,
            ] : ($this->staff_member()->exists() ? [
                'id' => $this->staff_member->campus->id,
                'name' => $this->staff_member->campus->name,
                'code' => $this->staff_member->campus->code,
                'area' => $this->staff_member->campus->area,
                'city' => $this->staff_member->campus->city,
                'province' => $this->staff_member->campus->province,
                'contact' => $this->staff_member->campus->contact,
                'stutus' => $this->staff_member->campus->stutus,
            ] : null),

            'head_office' => $this->head_office ? [
                'id' => $this->head_office->id,
                'user_id' => $this->head_office->user_id,
                'address' => $this->head_office->address,
                'city' => $this->head_office->city,
                'province' => $this->head_office->province,
                'longitude' => $this->head_office->longitude,
                'latitude' => $this->head_office->latitude,

            ] : 'head_office not exist for this user',

            'student' => new StudentResource($this->whenLoaded('student')),

        ];
    }
}
