<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationCardResource extends JsonResource
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
            'student_id' => $this->student_id,
            'issue_at' => $this->issue_at,
            'test_date' => $this->test_date,
            'test_time' => $this->test_time,
            'interview_date' => $this->interview_date,
            'status' => $this->status,
            'student' => new StudentResource($this->whenLoaded('student')),
            'campus' => new CampusResource($this->whenLoaded('campus')),

        ];
    }
}
