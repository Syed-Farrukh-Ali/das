<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChallanResource extends JsonResource
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

            'challan_no' => $this->challan_no,
            'payable' => $this->payable,
            'paid' => $this->paid,
            'status' => $this->status,
            'issue_date' => $this->issue_date,
            'received_date' => $this->received_date,
            'due_date' => $this->due_date,
            'created_at' => $this->created_at,

            'fee_challan_details' => FeeChallanDetailResource::collection($this->whenLoaded('feeChallanDetails')),
            // 'fee_challan_details' => $this->feeChallanDetails,
            // 'student' => new StudentResource($this->student),
        ];
    }
}
