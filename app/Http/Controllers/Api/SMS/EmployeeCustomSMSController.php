<?php

namespace App\Http\Controllers\Api\SMS;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\SMS\EmployeeCustomSMSRequest;
use App\Jobs\SendMessageJob;
use App\Models\Employee;
use Illuminate\Support\Facades\Config;

class EmployeeCustomSMSController extends BaseController
{
    public function employeeCustomSms(EmployeeCustomSMSRequest $request)
    {
        $employees = Employee::whereIn('id', $request->employee_ids)
            ->get();

        foreach ($employees as $employee) {
            // if (_campusAllowedMessages($employee->campus_id) > 0){
            SendMessageJob::dispatch(5, $employee->mobile_no, $request->message);
            // _campusMessageDecrement($employee->campus_id);
            // }else{
            // return $this->sendError('You have insufficient no of messages');
            // }
        }

        return $this->sendResponse([], 'Message sent successfully');
    }
}
