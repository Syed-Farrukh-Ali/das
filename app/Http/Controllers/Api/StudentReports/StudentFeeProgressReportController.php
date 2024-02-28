<?php

namespace App\Http\Controllers\Api\StudentReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StudentReport\StudentFeeProgressReportRequest;
use App\Http\Resources\StudentResource;
use App\Models\Campus;
use App\Models\Student;
use App\Models\StudentClass;
use Carbon\Carbon;

class StudentFeeProgressReportController extends BaseController
{
    public function __invoke(StudentFeeProgressReportRequest $request)
    {
        $month = Carbon::parse($request->month)->month;

        if ($request->session_wise)
        {
            $students = Student::with(['campus','globalSection','studentLiableFees','feeChallanDetails'=>
                function ($query) use ($month)
                {return $query
                    ->whereMonth('fee_month',$month)
                    ->where('fees_type_id',4)
                    ->first();
                }])
                ->where('session_id',$request->year_id)
                ->get();

            return StudentResource::collection($students);

        }

        if ($request->campus_wise)
        {
            $campuses = Campus::all()->toArray();

            $data = [];
            foreach ($campuses as $campus)
            {

                $students = Student::with(['campus','globalSection','studentLiableFees','feeChallanDetails'=>
                    function ($query) use ($month)
                    {return $query
                        ->whereMonth('fee_month',$month)
                        ->where('fees_type_id',4)
                        ->first();
                    }])
                    ->where('campus_id',$campus['id'])
                    ->get();

                $campusData = [
                    'Campus' => $campus['name'],
                    'report' => StudentResource::collection($students),
                ];

                $data[] = $campusData;
            }

            return $this->sendResponse($data, '',200);
        }

        if ($request->class_wise)
        {
                $data = [];

                $student_classes = StudentClass::get()->toArray();

                foreach ($student_classes as $class)
                {
                    $students = Student::with(['campus','globalSection','studentLiableFees','feeChallanDetails'=>
                        function ($query) use ($month)
                        {return $query
                            ->whereMonth('fee_month',$month)
                            ->where('fees_type_id',4)
                            ->first();
                        }])
                        ->where('student_class_id',$class['id'])
                        ->get();

                    $classData = [
                        'Class' => $class['name'],
                        'report' => StudentResource::collection($students),
                    ];

                    $data[] = $classData;
                }

            return $this->sendResponse($data, '',200);
        }
    }
}
