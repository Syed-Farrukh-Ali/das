<?php

namespace App\Http\Resources\Accounts;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountChartResource extends JsonResource
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
            'account_group_id' => $this->account_group_id,
            'title' => $this->title,
            'acode' => $this->acode,
            'torise_debit' => $this->torise_debit,
            'rise_with' => $this->torise_debit ? 'Debit' : 'credit',
            'sub_accounts' => SubAccountResource::collection($this->whenLoaded('sub_accounts')),

        ];
    }
}
