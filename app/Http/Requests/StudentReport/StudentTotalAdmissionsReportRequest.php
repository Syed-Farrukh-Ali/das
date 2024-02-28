<?php

namespace App\Http\Requests\StudentReport;

use App\Traits\ResponseMethodTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StudentTotalAdmissionsReportRequest extends FormRequest
{
    use ResponseMethodTrait;
    public function rules(): array
    {
        return [
            'start_date'  => 'required|date|date_format:Y-m-d',
            'end_date'  => 'required|date|date_format:Y-m-d',
            'session_wise' => 'nullable|boolean',
            'campus_wise' => 'nullable|boolean',
            'campus_class_wise' => 'nullable|boolean',
            'class_wise' => 'nullable|boolean',
            'monthly_fees_wise' => 'nullable|boolean',
            'inactive' => 'nullable|boolean',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }
}
