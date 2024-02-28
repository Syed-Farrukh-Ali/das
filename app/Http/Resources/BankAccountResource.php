<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return
        [
            'id' => $this->id,
            'sub_account_id' => $this->sub_account_id,
            'bank_account_category_id' => $this->bank_account_category_id,
            'bank_name' => $this->bank_name,
            'bank_branch' => $this->bank_branch,
            'account_title' => $this->account_title,
            'account_head' => $this->account_head,
            'account_number' => $this->account_number,

            'bank_account_category' => new BankAccountCategoryResource($this->whenLoaded('bank_account_category')),

        ];
    }
}
