<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeeChallanDetailResource extends JsonResource
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
            'fee_challan_id' => $this->fee_challan_id,
            'fees_type_id' => $this->fees_type_id,
            'fee_name' => $this->fee_name,
            'amount' => $this->amount,
            'fee_month' => $this->fee_month,
            'created_at' => $this->created_at,
            'created_at' => $this->created_at,

            'fee_challan' => new FeeChallanResourceCopy($this->whenLoaded('feeChallan')),
        ];
    }
}
