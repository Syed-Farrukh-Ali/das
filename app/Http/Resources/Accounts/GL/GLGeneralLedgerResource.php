<?php

namespace App\Http\Resources\Accounts\GL;

use App\Http\Resources\Accounts\AccountChartResource;
use App\Http\Resources\Accounts\SubAccountResource;
use App\Http\Resources\Accounts\VoucherResource;
use Illuminate\Http\Resources\Json\JsonResource;

class GLGeneralLedgerResource extends JsonResource
{
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
            'sub_account' => new SubAccountResource($this->whenLoaded('sub_account')),
            'account_chart' => new AccountChartResource($this->whenLoaded('account_chart')),
            'voucher' => new GLVoucherResource($this->whenLoaded('voucher')),
        ];
    }
}
