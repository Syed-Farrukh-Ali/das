<?php

namespace App\Http\Requests\StaffReport;

use App\Traits\ResponseMethodTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StaffMonthlySalarySlipRequest extends FormRequest
{
    use ResponseMethodTrait;

    public function rules(): array
    {
        return [
            'employee_code'  => 'nullable|exists:employees,emp_code',
            'campus_id'  => 'nullable|exists:campuses,id',
            'date' => 'required|date|date_format:Y-m-d',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }
}
