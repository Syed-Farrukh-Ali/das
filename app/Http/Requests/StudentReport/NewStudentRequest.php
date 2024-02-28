<?php

namespace App\Http\Requests\StudentReport;

use App\Traits\ResponseMethodTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class NewStudentRequest extends FormRequest
{
    use ResponseMethodTrait;

    public function rules(): array
    {
        return [
            'year_id' => 'nullable|numeric|exists:sessions,id',
            'campus_id' => 'nullable|numeric|exists:campuses,id',
            'start_date'  => 'required|date|date_format:Y-m-d',
            'end_date'  => 'required|date|after_or_equal:start_date|date_format:Y-m-d',
            'gender' => 'required|string',
            'status' => 'required|integer'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }
}
