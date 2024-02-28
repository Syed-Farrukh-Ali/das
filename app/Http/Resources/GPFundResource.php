<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GPFundResource extends JsonResource
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
            'collected_amount' => $this->collected_amount,
        ];
    }
}
