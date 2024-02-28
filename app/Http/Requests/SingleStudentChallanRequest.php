<?php

namespace App\Http\Requests;

use App\Traits\ResponseMethodTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class SingleStudentChallanRequest extends FormRequest
{
    use ResponseMethodTrait;
    public function rules(): array
    {
        return [
            'student_id'  => 'required|numeric|exists:students,id',
            'fee_status' => ['required', Rule::in([0, 1, 2])],
            'fee_month' => ['required', 'date_format:Y-m-d',
                function ($student, $fee_month, $fail) {
                    if (substr($fee_month, -2) != '01') {
                        $fail('Oops! something wrong with fee month');
                    }
                },
            ],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }
}
