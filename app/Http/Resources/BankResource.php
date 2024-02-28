<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BankResource extends JsonResource
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
            'name' => $this->name,
            'account_title' => $this->account_title,
            'account_no' => $this->account_no,

        ];
    }
}
