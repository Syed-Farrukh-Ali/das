<?php

namespace App\Http\Resources;

use App\Models\FeesType;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeStructureResource extends JsonResource
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
            'fee_type_id' => $this->fee_type_id,
            'student_class_id' => $this->student_class_id,
            'class_name' => $this->studentClass->name,
            'fee_name' => FeesType::find($this->fee_type_id)->name,
            'amount' => $this->amount,
            'session_id' => $this->session_id,
            'session' => new SessionResource($this->whenLoaded('session')),
            'fees_type' => new FeesTypeResource($this->whenLoaded('feesType')),
            // 'course_id' => $this->course_id,
            // 'shift' => $this->shift,
        ];
    }
}
