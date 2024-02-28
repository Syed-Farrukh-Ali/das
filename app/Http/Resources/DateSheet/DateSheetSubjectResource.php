<?php

namespace App\Http\Resources\DateSheet;

use App\Http\Resources\ExamResource;
use App\Http\Resources\StudentClassResource;
use App\Http\Resources\SubjectResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DateSheetSubjectResource extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'begin' => $this->begin,
            'subject' =>new SubjectResource($this->whenLoaded('subject')),
        ];
    }
}
