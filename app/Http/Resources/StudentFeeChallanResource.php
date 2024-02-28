<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentFeeChallanResource extends JsonResource
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
            'concession_id' => $this->concession_id,
            'hostel_id' => $this->hostel_id,
            'course_id' => $this->course_id,
            'student_class_id' => $this->student_class_id,
            'student_class_name' => $this->studentClass->name,
            'admission_id' => $this->admission_id,
            'section_id' => $this->section_id,
            'name' => $this->name,
            'father_name' => $this->father_name,
            'dob' => $this->dob,
            'religion' => $this->religion,
            'gender' => $this->gender,
            'mobile_no' => $this->mobile_no,
            'phone' => $this->phone,
            'qualification' => $this->qualification,
            'address' => $this->address,
            'remarks' => $this->remarks,
            'previous_school' => $this->previous_school,
            'class_left' => $this->class_left,
            'leaving_date' => $this->leaving_date,
            'shift' => $this->shift,
            'cnic_no' => $this->cnic_no,
            'father_cnic' => $this->father_cnic,
            'medium' => $this->medium,
            'status' => $this->status,

            'Fee_challans' => FeeChallanResource::collection($this->feeChallans),

            // 'issue_date'    => $this->issue_date,
            // 'due_date'      => $this->due_date,

        ];
    }
}
