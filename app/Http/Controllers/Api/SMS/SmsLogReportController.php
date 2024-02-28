<?php

namespace App\Http\Controllers\Api\SMS;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\SMS\DateWiseLogReportRequest;
use App\Http\Resources\SMS\MessageResource;
use App\Models\SMS\SMSLog;

class SmsLogReportController extends BaseController
{
    public function index()
    {
        $messages_report = SMSLog::latest()->get();
        $resource = MessageResource::collection($messages_report);

        return $this->sendResponse($resource,'',200);
    }

    public function dateWiseLogReport(DateWiseLogReportRequest $request)
    {
        $messages_report = SMSLog::whereDate('date_time','>=',$request->start_date)
            ->whereDate('date_time','<=',$request->end_date)
            ->latest()
            ->get();

        $resource = MessageResource::collection($messages_report);

        return $this->sendResponse($resource,'',200);
    }
}
