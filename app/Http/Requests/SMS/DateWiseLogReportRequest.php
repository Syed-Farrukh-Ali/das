<?php

namespace App\Http\Requests\SMS;

use App\Traits\ResponseMethodTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DateWiseLogReportRequest extends FormRequest
{
    use ResponseMethodTrait;
    public function rules(): array
    {
        return [
            'start_date'  => 'required|date|date_format:Y-m-d',
            'end_date'  => 'required|date|date_format:Y-m-d',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }
}
