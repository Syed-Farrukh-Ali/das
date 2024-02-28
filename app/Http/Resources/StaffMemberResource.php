<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StaffMemberResource extends JsonResource
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
            'applied_for' => $this->applied_for,
            'full_name' => $this->full_name,
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

            'user' => $this->user ? [
                'id' => $this->user->id,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'email' => $this->user->email,

            ] : 'does not have user',
        ];
    }
}
