<?php

namespace App\Http\Resources;

use App\Http\Resources\Accounts\VoucherResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SalaryResource extends JsonResource
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
            'account_no' => $this->account_no,
            'bank_account_id' => $this->bank_account_id,
            'cheque_number' => $this->cheque_number,
            'salary_month' => $this->salary_month,
            'net_pay' => $this->net_pay,
            'basic_pay' => $this->basic_pay,
            'gross_salary' => $this->gross_salary,

            'hifz' => $this->hifz,
            'hostel' => $this->hostel,
            'college' => $this->college,
            'additional_allowance' => $this->additional_allowance,
            'increment' => $this->increment,
            'second_shift' => $this->second_shift,
            'ugs' => $this->ugs,
            'other_allowance' => $this->other_allowance,
            'hod' => $this->hod,
            'science' => $this->science,
            'extra_period' => $this->extra_period,
            'extra_coaching' => $this->extra_coaching,
            'convance' => $this->convance,
            'eobi_payments' => $this->eobi_payments,
            'gpf_return' => $this->gpf_return,

            'eobi' => $this->eobi,
            'income_tax' => $this->income_tax,
            'insurance' => $this->insurance,
            'van_charge' => $this->van_charge,
            'other_deduction' => $this->other_deduction,
            'child_fee_deduction' => $this->child_fee_deduction,

            'gp_fund' => $this->gp_fund,
            'welfare_fund' => $this->welfare_fund,
            'loan_refund' => $this->loan_refund,
            'status' => $this->status,
            'days' => $this->days,

            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'voucher' => new VoucherResource($this->whenLoaded('voucher')),
            'bank_account' => new BankAccountResourceShort($this->whenLoaded('bank_account')),
        ];
    }
}
