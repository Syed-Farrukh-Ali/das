<?php

namespace App\Http\Resources;

use App\Http\Resources\Accounts\SubAccountResource;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
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
            'sub_account_id' => $this->sub_account_id,
            'loan_taken' => $this->loan_taken,
            'monthly_loan_installment' => $this->monthly_loan_installment,
            'balance' => $this->balance,
            'status' => $this->status,
            'loan_taken_date' => $this->loan_taken_date,
            'remaining_amount' => $this->remaining_amount,

            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'subAccount' => new SubAccountResource($this->whenLoaded('subAccount')),
        ];
    }
}
