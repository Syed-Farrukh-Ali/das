<?php

namespace App\Http\Requests\Account;

use App\Traits\ResponseMethodTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PaySheetDetailsRequest extends FormRequest
{
    use ResponseMethodTrait;

    public function rules(): array
    {
        return [
            'date' => 'required|date|date_format:Y-m-d',
            'campus_id' => 'required|numeric|exists:campuses,id',
            'bank_id' => 'required|numeric',
            'designation_id' => 'required|numeric',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }
}
