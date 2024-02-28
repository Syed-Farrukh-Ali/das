<?php

namespace App\Http\Resources\Accounts;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseAccountResource extends JsonResource
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
            'title' => $this->title,
            'acode' => $this->acode,
            'torise_debit' => $this->torise_debit,
            'rise_with' => $this->torise_debit ? 'Debit' : 'Credit',
            'account_groups' => AccountGroupResource::collection($this->whenLoaded('account_groups')),

        ];
    }
}
