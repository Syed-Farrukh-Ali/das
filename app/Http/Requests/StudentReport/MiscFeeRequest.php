<?php

namespace App\Http\Requests\StudentReport;

use Illuminate\Foundation\Http\FormRequest;

class MiscFeeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'fees_type_id'  => 'required|exists:fees_types,id',
            'campus_id'  => 'nullable|exists:campuses,id',
            'year_id'  => 'required|exists:sessions,id',
        ];
    }
}
