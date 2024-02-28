<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\BaseController;
use App\Models\DateSheet;
use App\Models\Result;

class DashboardController extends BaseController
{
    public function index()
    {
        $user = _user();
        $student = $user->student;

        $data = [
          'date_sheet' => DateSheet::where('student_class_id',$student->student_class_id)->count(),
          'fee_details' => $student->feeChallanDetails()->count(),
          'exam' => Result::where('student_id',$student->id)->count(),
          'notifications' => $student->campus->notifications()->count(),
          'attendance' => $student->attendances()->count(),
        ];

        return $this->sendResponse($data,'');
    }
}
