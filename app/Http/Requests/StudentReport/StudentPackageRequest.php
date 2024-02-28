<?php

namespace App\Http\Requests\StudentReport;

use App\Traits\ResponseMethodTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StudentPackageRequest extends FormRequest
{
    use ResponseMethodTrait;

    public function rules(): array
    {
        return [
            'year_id' => 'required|numeric|exists:sessions,id',
            'campus_id' => 'nullable|numeric|exists:campuses,id',
            'class_id' => 'nullable|numeric|exists:student_classes,id',
            'section_id' => 'nullable|numeric|exists:global_sections,id',
            'session_wise' => 'nullable|boolean',
            'campus_wise' => 'nullable|boolean',
            'class_wise' => 'nullable|boolean',
            'section_wise' => 'nullable|boolean',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }
}
