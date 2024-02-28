<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class StudentResourcePure extends JsonResource
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
            'user_id' => $this->user_id,
            'notification_id' => $this->notification_id,
            'session_id' => $this->session_id,
            'campus_id' => $this->campus_id,
            'concession_id' => $this->concession_id,
            'concession_remarks' => $this->concession_remarks,
            'hostel_id' => $this->hostel_id,
            'course_id' => $this->course_id,
            'student_class_id' => $this->student_class_id,
            'education_type' => $this->education_type,
            'admission_id' => $this->admission_id,
            'registration_id' => $this->registration_id,
            'global_section_id' => $this->global_section_id,
            'Joining_date' => $this->Joining_date,
            'vehicle_id' => $this->vehicle_id,
            'name' => $this->name,
            'father_name' => $this->father_name,
            'employee_id' => $this->employee_id,
            'dob' => $this->dob,
            'struck_off_date' => $this->struck_off_date,
            'religion' => $this->religion,
            'gender' => $this->gender,
            'mobile_no' => $this->mobile_no,
            'phone' => $this->phone,
            'address' => $this->address,
            'remarks' => $this->remarks,
            'previous_school' => $this->previous_school,
            'class_left' => $this->class_left,
            'leaving_date' => $this->leaving_date,
            'shift' => $this->shift,
            'cnic_no' => $this->cnic_no,
            'father_cnic' => $this->father_cnic,
            'status' => $this->status,
            'picture' => Storage::disk('student')->url($this->picture),

            'liable_fees' => StudentLiableFeeResource::collection($this->whenLoaded('studentLiableFees')),
            'fee_challans' => ChallanResource::collection($this->whenLoaded('feeChallans')),
            'fee_challan_details' => FeeChallanDetailResource::collection($this->whenLoaded('feeChallanDetails')),
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
