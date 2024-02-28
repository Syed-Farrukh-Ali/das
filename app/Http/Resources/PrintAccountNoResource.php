<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PrintAccountNoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'bank_account_id' => $this->bank_account_id,
            'campus_id' => $this->campus_id,
            'bank_name' => $this->bank_name,
            'account_number' => $this->account_number,
            'campus' => new CampusResource($this->whenLoaded('campus')),
        ];
    }
}
