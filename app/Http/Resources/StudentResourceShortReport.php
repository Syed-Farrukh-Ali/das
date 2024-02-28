<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class StudentResourceShortReport extends JsonResource
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
            'id'=>$this->id,
            'cam'=>$this->campus->name,
            'cls' => $this->studentClass ? $this->studentClass->name : '',
            'sec' => $this->globalSection ? $this->globalSection->name : '',
             
              'dob'=>$this->dob,
            'admin' => $this->admission_id,
            'name' => $this->name,
            'fn' => $this->father_name,
            'cn' => $this->father_cnic,
            'gen' => $this->gender,
            'mob' => $this->mobile_no,
            'liable'=>StudentLiableFeezResource::collection($this->whenLoaded('studentLiableFeesMonthly')),
            //studentLiableFeesMonthly
        ];
    }
}
