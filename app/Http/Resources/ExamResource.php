<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'session_id' => $this->session_id,
            'exam_type_id' => $this->exam_type_id,
            'date_sheet_status' => $this->date_sheet_status,
            'exam_name' => $this->exam_name,


            'session' => new SessionResource($this->whenLoaded('session')),
            'exam_type' => new ExamTypeResource($this->whenLoaded('exam_type')),
            'campus' => new CampusResourceSimple($this->whenLoaded('campus')),
            'student_classes' => StudentClassResource::collection($this->whenLoaded('student_classes')),
            'date_sheets' => StudentClassResource::collection($this->whenLoaded('date_sheets')),
            'results' => ResultResource::collection($this->whenLoaded('results')),

        ];
    }
}
