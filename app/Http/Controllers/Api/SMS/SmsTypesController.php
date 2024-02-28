<?php

namespace App\Http\Controllers\Api\SMS;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\SMS\SmsTypeResource;
use App\Models\SMS\SmsType;

class SmsTypesController extends BaseController
{
    public function index()
    {
        $sms_types = SmsType::all();
        $resource = SmsTypeResource::collection($sms_types);

        return $this->sendResponse($resource,[]);
    }

}
