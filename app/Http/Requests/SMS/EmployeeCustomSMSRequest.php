<?php

namespace App\Http\Requests\SMS;

use App\Traits\ResponseMethodTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class EmployeeCustomSMSRequest extends FormRequest
{
    use ResponseMethodTrait;
    public function rules(): array
    {
        return [
            'employee_ids' => 'required|array|exists:employees,id',
            'employee_ids.*' => 'required|numeric|exists:employees,id',
            'message' => 'required|min:1|max:1000',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }
}
