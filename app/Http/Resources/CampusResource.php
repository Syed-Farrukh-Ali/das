<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CampusResource extends JsonResource
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
            'head_office_id' => $this->head_office_id,
            'welfare_account_id' => $this->welfare_account_id,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'area' => $this->area,
            'city' => $this->city,
            'province' => $this->province,
            'contact' => $this->contact,
            'status' => $this->status,
            'email' => $this->user->email,
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
            'print_account_no' => PrintAccountNoResource::collection($this->whenLoaded('printAccountNos')),
        ];
    }
}
