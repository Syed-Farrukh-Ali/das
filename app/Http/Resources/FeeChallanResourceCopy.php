<?php

namespace App\Http\Resources;

use App\Http\Resources\Accounts\VoucherResource;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeChallanResourceCopy extends JsonResource
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
            'student_id' => $this->student_id,
            'bank_account_id' => $this->bank_account_id,

            'challan_no' => $this->challan_no,
            'payable' => $this->payable,
            'paid' => $this->paid,
            'status' => $this->status,
            'issue_date' => $this->issue_date,
            'received_date' => $this->received_date,
            'due_date' => $this->due_date,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at,

            'fee_challan_details' => FeeChallanDetailResource::collection($this->whenLoaded('feeChallanDetails')),
            'bank_account' => new BankAccountResource($this->whenLoaded('bank_account')),
            'voucher' => new VoucherResource($this->whenLoaded('voucher')),
            'student' => new StudentResourcePure($this->whenLoaded('student')),
            // 'fee_challan_details' => $this->feeChallanDetails,
            // 'student' => new StudentResource($this->student),
        ];
    }
}
