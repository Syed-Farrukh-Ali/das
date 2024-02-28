<?php

namespace App\Http\Requests\StudentReport;

use App\Traits\ResponseMethodTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExamReportRequest extends FormRequest
{
    use ResponseMethodTrait;

    public function rules(): array
    {
        return [
            'year_id' => 'required|integer|exists:sessions,id',
            'student_class_id' => 'required|integer|exists:student_classes,id',
            'subject_id' => 'nullable|integer|exists:subjects,id'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }
}
