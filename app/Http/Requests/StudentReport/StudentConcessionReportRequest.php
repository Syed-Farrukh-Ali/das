<?php

namespace App\Http\Requests\StudentReport;

use App\Traits\ResponseMethodTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StudentConcessionReportRequest extends FormRequest
{
    use ResponseMethodTrait;
    public function rules(): array
    {
        return [
            'fees_type_id'  => 'required|exists:fees_types,id',
            'year_id'  => 'nullable|exists:sessions,id',
            'concession_id'  => 'nullable|exists:concessions,id',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }
}
