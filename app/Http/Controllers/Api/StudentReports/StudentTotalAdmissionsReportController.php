<?php

namespace App\Http\Controllers\Api\StudentReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StudentReport\StudentTotalAdmissionsReportRequest;
use App\Models\Campus;
use App\Models\CampusClass;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentLiableFee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StudentTotalAdmissionsReportController extends BaseController
{
    public function totalAdmissionReport(StudentTotalAdmissionsReportRequest $request)
    {
        if ($request->session_wise)
        {
            $session_ids = Student::where('session_id','!=',null)
                ->whereDate('Joining_date','>=',$request->start_date)
                ->whereDate('Joining_date','<=',$request->end_date)
                ->pluck('session_id')
                ->unique()
                ->toArray();

            foreach ($session_ids as $session_id)
            {
                $dates = Student::select('Joining_date')->where('session_id',$session_id)
                    ->whereDate('Joining_date','>=',$request->start_date)
                    ->whereDate('Joining_date','<=',$request->end_date)
                    ->orderBy('Joining_date','asc')
                    ->selectRaw('DATE_FORMAT(Joining_date, "%Y-%m") AS month_year')
                    ->distinct()
                    ->pluck('month_year')
                    ->unique()
                    ->toArray();

                foreach ($dates as $date)
                {
                    $month  = Carbon::parse($date)->month;
                    $year  = Carbon::parse($date)->year;

                    $male = Student::where('session_id',$session_id)
                        ->whereMonth('Joining_date',$month)
                        ->whereYear('Joining_date',$year)
                        ->where('gender','Male')
                        ->count();

                    $female = Student::where('session_id',$session_id)
                        ->whereMonth('Joining_date',$month)
                        ->whereYear('Joining_date',$year)
                        ->where('gender','Female')
                        ->count();

                    $month_name = Carbon::parse($date)->format('F');

                    $data[] = [
                        'month_year' => $month_name.' '.$year,
                        'male' => $male,
                        'female' => $female,
                        'total_admissions' => $male+$female,
                    ];

                }

                if (isset($data)) {
                    $month_data = $data;
                }
            }

            if (isset($month_data)) {
                return $this->sendResponse($month_data, '',200);
            } else {
                return $this->sendResponse([], 'Not found',404);
            }
        }

        if ($request->campus_wise)
        {
            $campuses = Campus::all()->toArray();

            $data = [];
            foreach ($campuses as $campus)
            {
                $campusData = [
                    'Campus' => $campus['name'],
                    'report' => []
                ];

                $dates = Student::select('Joining_date')
                    ->where('campus_id',$campus['id'])
                    ->whereDate('Joining_date','>=',$request->start_date)
                    ->whereDate('Joining_date','<=',$request->end_date)
                    ->orderBy('Joining_date','asc')
                    ->selectRaw('DATE_FORMAT(Joining_date, "%Y-%m") AS month_year')
                    ->distinct()
                    ->pluck('month_year')
                    ->unique()
                    ->toArray();

                foreach ($dates as $date)
                {
                    $month  = Carbon::parse($date)->month;
                    $year  = Carbon::parse($date)->year;

                    $male = Student::where('campus_id',$campus['id'])
                        ->whereMonth('Joining_date',$month)
                        ->whereYear('Joining_date',$year)
                        ->where('gender','Male')
                        ->count();

                    $female = Student::where('campus_id',$campus['id'])
                        ->whereMonth('Joining_date',$month)
                        ->whereYear('Joining_date',$year)
                        ->where('gender','Female')
                        ->count();

                    $month_name = Carbon::parse($date)->format('F');


                    $campusData['report'][] = [
                        'month_year' => $month_name.' '.$year,
                        'male' => $male,
                        'female' => $female,
                        'total_admissions' => $male+$female,
                    ];

                }

                $data[] = $campusData;
            }

            return $this->sendResponse($data, '',200);
        }

        if ($request->campus_class_wise)
        {
            $campuses = Campus::all()->toArray();

            $data = [];
            foreach ($campuses as $campus)
            {
                $campusData = [
                    'Campus' => $campus['name'],
                    'Classes' => []
                ];

                $class_ids = CampusClass::where('campus_id', $campus['id'])->pluck('student_class_id')->toArray();
                $classes = StudentClass::find($class_ids)->toArray();

                foreach ($classes as $class)
                {

                    $classData = [
                        'Class' => StudentClass::where('id',$class['id'])->get(['name'])->first(),
                        'report' => []
                    ];

                    $male = Student::where('campus_id',$campus['id'])
                        ->where('student_class_id',$class['id'])
                        ->whereDate('Joining_date','>=',$request->start_date)
                        ->whereDate('Joining_date','<=',$request->end_date)
                        ->where('gender','Male')
                        ->count();

                    $female = Student::where('campus_id',$campus['id'])
                        ->where('student_class_id',$class['id'])
                        ->whereDate('Joining_date','>=',$request->start_date)
                        ->whereDate('Joining_date','<=',$request->end_date)
                        ->where('gender','Female')
                        ->count();

                    $class_name = StudentClass::where('id',$class['id'])->get(['name'])->first();

                    $classData['report'][] = [
                        'male' => $male,
                        'female' => $female,
                        'total_admissions' => $male+$female,
                    ];

                    $campusData['Classes'][] = $classData;
                }

                $data[] = $campusData;
            }

            return $this->sendResponse($data, '',200);
        }

        if ($request->class_wise)
        {
            $dates = Student::select('Joining_date')
                ->whereDate('Joining_date','>=',$request->start_date)
                ->whereDate('Joining_date','<=',$request->end_date)
                ->orderBy('Joining_date','asc')
                ->selectRaw('DATE_FORMAT(Joining_date, "%Y-%m") AS month_year')
                ->distinct()
                ->pluck('month_year')
                ->unique()
                ->toArray();

            foreach ($dates as $date)
            {
                $month  = Carbon::parse($date)->month;
                $year  = Carbon::parse($date)->year;

                $monthData = [
                    'month_year' => Carbon::parse($date)->format('F').' '.$year,
                    'Classes' => []
                ];

                $classes = StudentClass::get()->toArray();

                foreach ($classes as $class)
                {
                    $total_students = Student::where('student_class_id',$class['id'])
                        ->whereMonth('Joining_date',$month)
                        ->whereYear('Joining_date',$year)
                        ->count();

                    $classReport = [
                        'Class' => StudentClass::where('id',$class['id'])->get(['name'])->first(),
                        'total_admissions' => $total_students,
                    ];

                    $monthData['Classes'][] = $classReport;
                }

                $data[] = $monthData;
            }

            if (isset($data)) {
                return $this->sendResponse($data, '',200);
            }else {
                return $this->sendResponse([], 'Not found',404);
            }
        }

        if ($request->monthly_fees_wise)
        {
            $dates = Student::select('Joining_date')
                ->whereDate('Joining_date','>=',$request->start_date)
                ->whereDate('Joining_date','<=',$request->end_date)
                ->orderBy('Joining_date','asc')
                ->selectRaw('DATE_FORMAT(Joining_date, "%Y-%m") AS month_year')
                ->distinct()
                ->pluck('month_year')
                ->unique()
                ->toArray();

            foreach ($dates as $date)
            {
                $month  = Carbon::parse($date)->month;
                $year  = Carbon::parse($date)->year;

                $student_ids = Student::where('status',2)
                    ->whereMonth('Joining_date',$month)
                    ->whereYear('Joining_date',$year)
                    ->pluck('id')
                    ->toArray();

                $feesCounts = StudentLiableFee::whereIn('student_id',$student_ids)
                    ->where('fees_type_id',4)
                    ->select('amount', DB::raw('count(*) as count'))
                    ->groupBy('amount')
                    ->get();

                $month_name = Carbon::parse($date)->format('F');

                $monthData = [
                    'month_year' => $month_name.' '.$year,
                    'fees' => $feesCounts,
                ];

                $data[] = $monthData;
            }

            if (isset($data)) {
                return $this->sendResponse($data, '',200);
            } else{
                return $this->sendResponse([], 'Not found',200);
            }
        }

        if ($request->inactive){

            $male = Student::where('status',4)
                ->whereDate('Joining_date','>=',$request->start_date)
                ->whereDate('Joining_date','<=',$request->end_date)
                ->where('gender','Male')
                ->count();

            $female = Student::where('status',4)
                ->whereDate('Joining_date','>=',$request->start_date)
                ->whereDate('Joining_date','<=',$request->end_date)
                ->where('gender','Female')
                ->count();

            $data = [
                'male' => $male,
                'female' => $female,
                'total_admissions' => $male+$female,
            ];

            return $this->sendResponse($data, '',200);
        }

        return false;
    }
}
