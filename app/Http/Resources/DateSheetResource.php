<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DateSheetResource extends JsonResource
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
            'student_class_id' => $this->student_class_id,
            'subject_id' => $this->subject_id,
            'date' => $this->date,
            'time' => $this->time,

            'student_class' => new StudentClassResource($this->whenLoaded('student_class')),
            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'exam' => new ExamResource($this->whenLoaded('exam')),
        ];
    }
}
