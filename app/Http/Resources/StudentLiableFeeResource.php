<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentLiableFeeResource extends JsonResource
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
            'student_id' => $this->student_id,
            'fees_type_id' => $this->fees_type_id,
            'amount' => $this->amount,
            'concession_amount' => $this->concession_amount,
            'remarks' => $this->remarks,

            'student' => new StudentResource($this->whenLoaded('student')),
            'fee_type' => new FeesTypeResource($this->whenLoaded('feesType')),

            // 'duration_type' => $this->duration_type,
            // 'status' => $this->status,
        ];
    }
}
