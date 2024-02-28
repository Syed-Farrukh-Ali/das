<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AppointedEmployeeResource extends JsonResource
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
            'job_status_id' => $this->job_status_id,
            'job_status_name' => $this->jobStatus ? $this->jobStatus->name : '',
            'status' => $this->status,
            'registration_status' => $this->status == 1 ? 'registered only' : 'appointed',
            'reg_code' => $this->reg_code,
            'designation_id' => $this->designation_id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'father_name' => $this->father_name,
            'nationality' => $this->nationality,
            'religion' => $this->religion,
            'experience' => $this->experience,
            'cnic_no' => $this->cnic_no,
            'qualification' => $this->qualification,
            'gender' => $this->gender,
            'marital_status' => $this->marital_status,
            'dob' => $this->dob,
            'remarks' => $this->remarks,
            'mobile_no' => $this->mobile_no,
            'phone' => $this->phone,
            'address' => $this->address,
            'experience' => $this->experience,

            'social_security_number' => $this->social_security_number,
            'emp_code' => $this->emp_code,
            'field_of_interest' => $this->field_of_interest,
            'distinctions' => $this->distinctions,
            'objectives' => $this->objectives,
            'duties_assigned' => $this->duties_assigned,

            'global_bank_id' => $this->global_bank_id,
            'account_no' => $this->account_no,
            'pay_scale_id' => $this->pay_scale_id,
            'payment_type' => $this->payment_type,
            'salery_days' => $this->salery_days,
            'joining_date' => $this->joining_date,
            'account_head' => $this->account_head,

        ];
    }
}
