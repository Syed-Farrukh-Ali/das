<?php

namespace App\Http\Controllers\Api\StudentReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StudentReport\FeeReceivedMonthWiseRequest;
use App\Http\Resources\StudentResourcePure;
use App\Models\FeeChallan;
use App\Models\Student;
use Carbon\Carbon;

class FeeReceivedMonthWiseController extends BaseController
{
    public function report(FeeReceivedMonthWiseRequest $request)
    {
        $data = [];

        $month = Carbon::parse($request->date)->month;

        $students = Student::with(['campus','globalSection','studentClass','studentLiableFees'=>
            function ($query) use($request)
            {   return $query
                ->where('fees_type_id',$request->fees_type_id)
                ->first();
            }])
            ->get();

        foreach ($students as $student)
        {
            $fee_challans = FeeChallan::whereMonth('received_date',$month)
               ->where('status',2)
               ->where('student_id',$student->id)
               ->get();

            $fee_challan_detail = $fee_challans->where('student_id', $student->id)->pluck('feeChallanDetails')->flatten();
            $amount = $fee_challan_detail->where('fees_type_id', $request->fees_type_id)->sum('amount');

            if ($student->studentClass)
                $class = $student->studentClass->name;

            if ($student->globalSection)
                $section = $student->globalSection->name;

            if ($student->campus)
                $campus_name = $student->campus->name;

            if ($amount <= 0)
                continue;

            $data[] = [
                'admission_id' => $student->admission_id,
                'name' => $student->name,
                'class' => $class ?? '',
                'section' => $section ?? '',
                'campus' => $campus_name ?? '',
                'amount' => $amount,
            ];
        }

        return $this->sendResponse($data, '',200);
    }
}
