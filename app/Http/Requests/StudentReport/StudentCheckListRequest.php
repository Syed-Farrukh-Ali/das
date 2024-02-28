<?php

namespace App\Http\Requests\StudentReport;

use App\Traits\ResponseMethodTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StudentCheckListRequest extends FormRequest
{
    use ResponseMethodTrait;

    public function rules(): array
    {
        return [
            'year_id' => 'required|numeric|exists:sessions,id',
            'campus_id' => 'required|numeric|exists:campuses,id',
            'class_id' => 'nullable|numeric|exists:student_classes,id',
            'education_type' => 'nullable|numeric',
            'section_id' => 'nullable|numeric|exists:global_sections,id',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }
}
