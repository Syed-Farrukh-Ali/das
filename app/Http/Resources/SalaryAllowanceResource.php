<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SalaryAllowanceResource extends JsonResource
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
            'hifz' => $this->hifz,
            'hostel' => $this->hostel,
            'college' => $this->college,
            'additional_allowance' => $this->additional_allowance,
            'increment' => $this->increment,
            'second_shift' => $this->second_shift,
            'ugs' => $this->ugs,
            'other' => $this->other,
            'hod' => $this->hod,
            'science' => $this->science,
            'extra_period' => $this->extra_period,
            'extra_coaching' => $this->extra_coaching,
            'convance' => $this->convance,

            'all_allowance_total' => $this->hifz +
            $this->hostel +
            $this->college +
            $this->additional_allowance +
            $this->increment +
            $this->second_shift +
            $this->ugs +
            $this->other +
            $this->hod +
            $this->science +
            $this->extra_period +
            $this->extra_coaching +
            $this->convance,
        ];
    }
}
