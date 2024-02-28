<?php

namespace App\Http\Requests\SMS;

use App\Traits\ResponseMethodTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CreateUnitRequest extends FormRequest
{
    use ResponseMethodTrait;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'unit_name' => 'required|string|max:255|min:3',
            'assign_sms' => 'required|int|max:100000|min:1000',
            'application_url' => 'required|url:http,https',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }
}
