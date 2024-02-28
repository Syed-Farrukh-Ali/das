<?php

namespace App\Http\Resources\Accounts;

use App\Http\Resources\CampusResource;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneralLedgerResource extends JsonResource
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
            'voucher_id' => $this->voucher_id,
            'sub_account_id' => $this->sub_account_id,
            'account_chart_id' => $this->account_chart_id,
            'session_id' => $this->session_id,
            'debit' => round($this->debit),
            'credit' => round($this->credit),
            'remarks' => $this->remarks,
            'transaction_at' => $this->transaction_at,
            'campus' => new CampusResource($this->whenLoaded('campus')),
            'sub_account' => new SubAccountResource($this->whenLoaded('sub_account')),
            'account_chart' => new AccountChartResource($this->whenLoaded('account_chart')),
            'voucher' => new VoucherResource($this->whenLoaded('voucher')),
        ];
    }
}
