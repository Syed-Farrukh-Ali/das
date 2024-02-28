<?php

namespace App\Http\Requests\StudentReport;

use App\Traits\ResponseMethodTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StudentStrengthRequest extends FormRequest
{
    use ResponseMethodTrait;
    public function rules(): array
    {
        return [
            'year_id'  => 'required|exists:sessions,id',
            'summary' => 'required|boolean',
            'education_type' => 'required|integer',
            'campus_ids' => 'nullable|array|exists:campuses,id',
            'campus_ids.*' => 'nullable|numeric|exists:campuses,id',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }
}
