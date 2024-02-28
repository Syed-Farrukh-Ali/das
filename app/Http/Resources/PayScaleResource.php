<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PayScaleResource extends JsonResource
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
            'payscale' => $this->payscale,
            'basic' => $this->basic,
            'increment' => $this->increment,
            'maximum' => $this->maximum,
            'gp_fund' => $this->gp_fund,
            'welfare_fund' => $this->welfare_fund,

        ];
    }
}
