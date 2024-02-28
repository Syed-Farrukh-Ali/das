<?php

namespace App\Http\Requests;

use App\Traits\ResponseMethodTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExamRequest extends FormRequest
{
    use ResponseMethodTrait;

    public function rules(): array
    {
        return [
            'student_class_ids.*' => 'required|integer|exists:student_classes,id',
            'year_id' => 'required|integer|exists:sessions,id',
            'exam_type_id' => 'required|integer|exists:exam_types,id',
            'campus_id' => 'required|integer|exists:campuses,id',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }
}
