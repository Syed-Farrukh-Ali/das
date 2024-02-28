<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class StudentResourceShort extends JsonResource
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
            'session_id' => $this->session_id,
            'notification_id' => $this->notification_id,
            'campus_id' => $this->campus_id,
            'student_class_id' => $this->student_class_id,
            'admission_id' => $this->admission_id,
            'registration_id' => $this->registration_id,
            'global_section_id' => $this->global_section_id,
            'name' => $this->name,
            'father_name' => $this->father_name,
            'gender' => $this->gender,
            'mobile_no' => $this->mobile_no,
            'struck_off_date' => $this->struck_off_date,
            'picture' => Storage::disk('student')->url($this->picture),
            'liable_fees' => StudentLiableFeeResource::collection($this->whenLoaded('studentLiableFees')),
            'fee_challans' => ChallanResource::collection($this->whenLoaded('feeChallans')),
            'subjects' => SubjectResource::collection($this->whenLoaded('subjects')),
            'session' => new SessionResource($this->whenLoaded('session')),
            'student_class' => new StudentClassResource($this->whenLoaded('studentClass')),
            'campus' => new CampusResource($this->whenLoaded('campus')),
            'global_section' => new GlobalSectionResource($this->whenLoaded('globalSection')),
            'registraion_card' => new RegistrationCardResource($this->whenLoaded('registrationcard')),
            'hostel' => new HostelResource($this->whenLoaded('hostel')),

        ];
    }
}
