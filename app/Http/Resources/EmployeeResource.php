<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'bank_account_id' => $this->bank_account_id,
            'designation_id' => $this->designation_id,
            'job_status_name' => $this->jobStatus ? 'React developer please use job_status.name' : 'please use job_status.name',
            'status' => $this->status,
            'registration_status' => $this->status == 1 ? 'registered only' : 'appointed',
            'reg_code' => $this->reg_code,
            'emp_code' => $this->emp_code,
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

            'social_security_number' => $this->social_security_number,
            'eobi_no' => $this->eobi_no,
            'field_of_interest' => $this->field_of_interest,
            'distinctions' => $this->distinctions,
            'objectives' => $this->objectives,
            'duties_assigned' => $this->duties_assigned,

            'account_no' => $this->account_no,
            'pay_scale_id' => $this->pay_scale_id,
            'payment_type' => $this->payment_type,
            'salery_days' => $this->salery_days,
            'joining_date' => $this->joining_date,
            'collected_gp_fund' => $this->collected_gp_fund,
            'auto_clear_deduction' => $this->auto_clear_deduction,

            'designation' => new DesignationResource($this->whenLoaded('designation')),
            'campus' => new CampusResource($this->whenLoaded('campus')),
            'job_status' => new JobStatusResource($this->whenLoaded('jobStatus')),
            'pay_scale' => new PayScaleResource($this->whenLoaded('payScale')),
            'bank_account' => new BankAccountResource($this->whenLoaded('bankAccount')),
            'salaryAlowance' => new SalaryAllowanceResource($this->whenLoaded('salaryAllowance')),
            'salaryDeduction' => new SalaryDeductionResource($this->whenLoaded('salaryDeduction')),
            'gp_collected_fund' => new GPFundResource($this->whenLoaded('GPFund')),
            'employee_salary' => SalaryResource::collection($this->whenLoaded('employeeSalaries')),
            'loans' => LoanResource::collection($this->whenLoaded('loans')),
            'students' => StudentResourceShort::collection($this->whenLoaded('students')),
        ];
    }
}
