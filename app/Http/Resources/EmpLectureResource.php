<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmpLectureResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'campus_id' => $this->campus_id,
            'session_id' => $this->session_id,
            'student_class_id' => $this->student_class_id,
            'global_section_id' => $this->global_section_id,
            'subject_id' => $this->subject_id,

            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'campus' => new CampusResource($this->whenLoaded('campus')),
            'session' => new SessionResource($this->whenLoaded('session')),
            'student_class' => new StudentClassResource($this->whenLoaded('student_class')),
            'global_section' => new GlobalSectionResource($this->whenLoaded('global_section')),
            'subject' => new SubjectResource($this->whenLoaded('subject')),

        ];
    }
}
