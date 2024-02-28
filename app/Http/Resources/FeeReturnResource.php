<?php

namespace App\Http\Resources;

use App\Http\Resources\Accounts\SubAccountResource;
use App\Http\Resources\Accounts\VoucherResource;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeReturnResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'fee_sub_account_id' => $this->fee_sub_account_id,
            'fee_return_amount' => $this->fee_return_amount,
            'sub_account_id' => $this->sub_account_id,
            'voucher_id' => $this->voucher_id,
            'date' => $this->date,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'campus_id' => $this->campus_id,

            'student' => new StudentResourcePure($this->whenLoaded('student')),
            'sub_account' => new SubAccountResource($this->whenLoaded('sub_account')),
            'fee_sub_account' => new SubAccountResource($this->whenLoaded('fee_sub_account')),
            'voucher' => new VoucherResource($this->whenLoaded('voucher')),
            // 'feesType' => new FeesTypeResource($this->whenLoaded('feesType')),

        ];
    }
}
