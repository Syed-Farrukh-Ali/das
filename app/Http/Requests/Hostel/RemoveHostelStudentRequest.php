<?php

namespace App\Http\Requests\Hostel;

use App\Traits\ResponseMethodTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RemoveHostelStudentRequest extends FormRequest
{
    use ResponseMethodTrait;
    public function rules(): array
    {
        return [
            'student_id' => 'required|numeric|exists:students,id',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }
}
