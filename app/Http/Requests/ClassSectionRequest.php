<?php

namespace App\Http\Requests;

use App\Traits\ResponseMethodTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClassSectionRequest extends FormRequest
{
    use ResponseMethodTrait;

    public function rules(): array
    {
        return [
            'campus_id' => 'required|numeric|exists:campuses,id',
            'student_class_id' => 'required|numeric',
            'global_section_id' => 'required|numeric',
            'education_type' => 'required|numeric|max:2|min:1',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }
}
