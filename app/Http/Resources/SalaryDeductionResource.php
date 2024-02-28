<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SalaryDeductionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'eobi' => $this->eobi,
            'income_tax' => $this->income_tax,
            'insurance' => $this->insurance,
            'van_charge' => $this->van_charge,
            'other' => $this->other,

            'all_deduction_total' => $this->eobi +
            $this->income_tax +
            $this->insurance +
            $this->van_charge +
            $this->other,
        ];
    }
}
