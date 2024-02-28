<?php

namespace App\Http\Resources\Accounts;

use App\Http\Resources\CampusResource;
use App\Http\Resources\SessionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherResource extends JsonResource
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
            'date' => $this->date,
            'voucher_type_id' => $this->voucher_type_id,
            'remarks' => $this->remarks,
            'check_no' => $this->check_no,
            'voucher_no' => $this->voucher_no,
            'session_id' => $this->session_id,
            'resolved' => $this->resolved,
            'campus_id' => $this->campus_id,
            'total_debit' => round($this->total_debit),
            'total_credit' => round($this->total_credit),

            'campus' => new CampusResource($this->whenLoaded('campus')),
            'session' => new SessionResource($this->whenLoaded('session')),
            'voucher_type' => new VoucherTypeResource($this->whenLoaded('voucher_type')),
            'general_ledgers' => GeneralLedgerResource::collection($this->whenLoaded('general_ledgers')),
        ];
    }
}
