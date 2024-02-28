<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'late_fee_fine' => $this->late_fee_fine,
            'unit_name' => $this->unit_name,
            'gp_fund_years' => $this->gp_fund_years,
            'director_number' => $this->director_number,
            'alphanumeric_adm_no' => $this->alphanumeric_adm_no,
            'director_sign' => $this->director_sign,
            'send_message' => $this->send_message,
            'sms_api_login' => $this->sms_api_login,
            'sms_api_password' => $this->sms_api_password,
            'logo_file' => Storage::disk('logo')->url($this->logo_file),
            'start_logo_file' => Storage::disk('logo')->url($this->start_logo_file),
        ];
    }
}
