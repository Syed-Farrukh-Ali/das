<?php

namespace App\Http\Requests\Hostel;

use App\Traits\ResponseMethodTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class HostelFilterRequest extends FormRequest
{
    use ResponseMethodTrait;

    public function rules(): array
    {
        return [
            'search_keyword' => 'required|string|max:191',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }

}
