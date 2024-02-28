<?php

namespace App\Http\Requests\Account;

use App\Traits\ResponseMethodTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TrialBalanceRequest extends FormRequest
{
    use ResponseMethodTrait;

    public function rules(): array
    {
        return [
            'year_id' => 'required|numeric|exists:sessions,id',
            'date' => 'nullable|date|date_format:Y-m-d',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }
}
