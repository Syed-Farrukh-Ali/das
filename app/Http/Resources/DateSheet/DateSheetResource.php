<?php

namespace App\Http\Resources\DateSheet;

use App\Http\Resources\ExamResource;
use App\Http\Resources\StudentClassResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DateSheetResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'note' => $this->note,
            'student_class' => new StudentClassResource($this->whenLoaded('student_class')),
            'subjects' => DateSheetSubjectResource::collection($this->whenLoaded('dateSheetSubjects')),
            'exam' => new ExamResource($this->whenLoaded('exam')),
        ];
    }
}
