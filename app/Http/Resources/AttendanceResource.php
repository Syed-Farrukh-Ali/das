<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return[
            'id' => $this->id,
            'status_name' => $this->status_name,
            'student_id' => $this->student_id,
            'date' => $this->date,
            'attendance_status_id' => $this->attendance_status_id,

            'student' => new StudentResourcePure($this->whenLoaded('student')),
            'campus' => new CampusResource($this->whenLoaded('campus')),
            'student_class' => new StudentClassResource($this->whenLoaded('student_class')),
            'global_section' => new GlobalSectionResource($this->whenLoaded('global_section')),
            'atedance_status' => new AttendanceStatusResource($this->whenLoaded('atedance_status')),
        ];
    }
}
