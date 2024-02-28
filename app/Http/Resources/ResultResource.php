<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResultResource extends JsonResource
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
            'exam_id' => $this->exam_id,
            'student_id' => $this->student_id,
            'status' => $this->status,
            'subject_id' => $this->subject_id,
            'full_marks' => $this->full_marks,
            'gain_marks' => $this->gain_marks,
            'practical_marks' => $this->practical_marks,
            'percentage' => $this->percentage,
            'grade' => $this->grade,

            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'student' => new StudentResource($this->whenLoaded('student')),
        ];
    }
}
