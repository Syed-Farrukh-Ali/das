<?php

namespace App\Http\Requests\Hostel;

use App\Traits\ResponseMethodTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AssignHostelRequest extends FormRequest
{
    use ResponseMethodTrait;
    public function rules(): array
    {
        return [
            'hostel_id' => 'required|numeric|exists:hostels,id',
            'student_id' => 'required|numeric|exists:students,id',
            'issue_date' => 'nullable|date|date_format:Y-m-d',
            'due_date' => 'nullable|date|date_format:Y-m-d',
            'admission_fee' => 'required|numeric',
            'fee_months' => 'required|array',
            'fee_type_id' => 'required|array|exists:fees_types,id',
            'fee_amount' => 'required|array',
            'concession_amount' => 'required|array',
            'fee_after_concession' => 'required|array',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), [], 422));
    }
}
