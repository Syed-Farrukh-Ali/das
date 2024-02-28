<?php

namespace App\Http\Controllers\Api\AccountReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\ProjectedMonthlyIncomeRequest;
use App\Http\Resources\StudentLiableFeeResource;
use App\Http\Resources\StudentResource;
use App\Models\Campus;
use App\Models\CampusClass;
use App\Models\ClassSection;
use App\Models\GlobalSection;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentLiableFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectedMonthlyIncome extends BaseController
{
    // Projected Monthly Income
    public function MonthlyIncomeReport(ProjectedMonthlyIncomeRequest $request)
    {
        $campus_ids = $request->campus_id;

        $data = [
            'all_campus_data' => [],
        ];

        if ($campus_ids) {
            $campus_student_data = [
                'campus' => Campus::where('id', $request->campus_id)->value('name') ?? '',
                'campus_data' => [],
            ];

            $class_ids = CampusClass::where('campus_id', $campus_ids)->pluck('student_class_id')->toArray();

            $classes = StudentClass::find($class_ids)->toArray();


            foreach ($classes as $class) {

                $classData = [
                    'Class' => StudentClass::where('id', $class['id'])->get(['name'])->first(),
                    // 'sections' => [],
                    'monthly_fee' => 0,
                    'hostel_fee' => 0,
                    'total_student_count' => 0,
                ];

                $total_student_count = 0;
                $total_monthly_fee = 0;
                $total_hostel_fee = 0;

                $section_ids = ClassSection::where('campus_id', $campus_ids)
                    ->where('student_class_id', $class['id'])
                    ->pluck('global_section_id')->unique()->toArray();

                $sections = GlobalSection::whereIn('id', $section_ids)->get()->toArray();

                foreach ($sections as $section) {

                    $students = Student::where('campus_id', $campus_ids)
                        ->where('student_class_id', $class['id'])
                        ->where('global_section_id', $section['id'])
                        ->where('session_id', $request->year_id)
                        ->where('status', 2)
                        ->get();

                    $total_students = Student::where('campus_id', $campus_ids)
                        ->where('student_class_id', $class['id'])
                        ->where('global_section_id', $section['id'])
                        ->where('session_id', $request->year_id)
                        ->where('status', 2)
                        ->count();

                    $total_student_count += $total_students;

                    foreach ($students as $student) {
                        $student_monthly_fee = StudentLiableFee::where('student_id', $student->id)
                            ->where('fees_type_id', 4)
                            ->first();

                        if ($student_monthly_fee) {
                            $student->monthly_fees = $student_monthly_fee->amount;
                            $total_monthly_fee += $student->monthly_fees;
                        }

                        $student_hostel_fee = StudentLiableFee::where('student_id', $student->id)
                            ->where('fees_type_id', 7)
                            ->first();

                        if ($student_hostel_fee) {
                            $student->hostel_fees = $student_hostel_fee->amount;
                            $total_hostel_fee += $student->hostel_fees;
                        }
                    }

                    // $sectionData = [
                    //     'section' => $section['name'],
                    //     'monthly_fee' => $total_monthly_fee,
                    //     'hostel_fee' => $total_hostel_fee,
                    //     'total_student' => $total_students,
                    // ];

                    $classData['monthly_fee'] = $total_monthly_fee;
                    $classData['hostel_fee'] = $total_hostel_fee;
                    $classData['total_student_count'] = $total_student_count;
                }

                $campus_student_data['campus_data'][] = $classData;
            }
            $data['all_campus_data'][] = $campus_student_data;
        } else {
            $campuses = Campus::when($campus_ids, function ($query) use ($request) {
                return $query->whereIn('id', $request->campus_ids);
            })->get()->toArray();

            foreach ($campuses as $campus) {
                $campus_student_data = [
                    'campus' => $campus['name'],
                    'campus_data' => [],
                ];

                $class_ids = CampusClass::where('campus_id', $campus)->pluck('student_class_id')->toArray();

                $classes = StudentClass::find($class_ids)->toArray();


                foreach ($classes as $class) {

                    $classData = [
                        'Class' => StudentClass::where('id', $class['id'])->get(['name'])->first(),
                        // 'sections' => [],
                        'monthly_fee' => 0,
                        'hostel_fee' => 0,
                        'total_student_count' => 0,
                    ];

                    $total_student_count = 0;
                    $total_monthly_fee = 0;
                    $total_hostel_fee = 0;

                    $section_ids = ClassSection::where('campus_id', $campus)
                        ->where('student_class_id', $class['id'])
                        ->pluck('global_section_id')->unique()->toArray();

                    $sections = GlobalSection::whereIn('id', $section_ids)->get()->toArray();

                    foreach ($sections as $section) {

                        $students = Student::where('campus_id', $campus)
                            ->where('student_class_id', $class['id'])
                            ->where('global_section_id', $section['id'])
                            ->where('session_id', $request->year_id)
                            ->where('status', 2)
                            ->get();

                        $total_students = Student::where('campus_id', $campus)
                            ->where('student_class_id', $class['id'])
                            ->where('global_section_id', $section['id'])
                            ->where('session_id', $request->year_id)
                            ->where('status', 2)
                            ->count();

                        $total_student_count += $total_students;

                        foreach ($students as $student) {
                            $student_monthly_fee = StudentLiableFee::where('student_id', $student->id)
                                ->where('fees_type_id', 4)
                                ->first();

                            if ($student_monthly_fee) {
                                $student->monthly_fees = $student_monthly_fee->amount;
                                $total_monthly_fee += $student->monthly_fees;
                            }

                            $student_hostel_fee = StudentLiableFee::where('student_id', $student->id)
                                ->where('fees_type_id', 7)
                                ->first();

                            if ($student_hostel_fee) {
                                $student->hostel_fees = $student_hostel_fee->amount;
                                $total_hostel_fee += $student->hostel_fees;
                            }
                        }

                        // $sectionData = [
                        //     'section' => $section['name'],
                        //     'monthly_fee' => $total_monthly_fee,
                        //     'hostel_fee' => $total_hostel_fee,
                        //     'total_student' => $total_students,
                        // ];

                        $classData['monthly_fee'] = $total_monthly_fee;
                        $classData['hostel_fee'] = $total_hostel_fee;
                        $classData['total_student_count'] = $total_student_count;
                    }
                    $campus_student_data['campus_data'][] = $classData;
                }
                $data['all_campus_data'][] = $campus_student_data;
            }
        }
        return $this->sendResponse($data, '', 200);
    }

    public function MonthlyFeeBreakReport(ProjectedMonthlyIncomeRequest $request)
    {
        $campus_ids = $request->campus_id;

        $data = [
            'all_campus_data' => [],
        ];

        if ($campus_ids) {
            $campus_student_data = [
                'campus_name' => Campus::where('id', $request->campus_id)->value('name') ?? '', // Use find() to get a single model instance
                'campus_data' => [], // Initialize to 0
            ];

            $student_ids = Student::where('campus_id', $campus_ids)
                ->where('session_id', $request->year_id)
                ->where('status', 2)
                ->pluck('id')
                ->toArray();

            $student_fee_break = DB::table('student_liable_fees')
                ->select('amount', DB::raw('count(amount) as countStudent'))
                ->whereIn('student_id', $student_ids)
                ->groupBy('amount')
                ->where('fees_type_id', 4)
                ->get();

            $campus_student_data['campus_data'] = $student_fee_break;
            $data['all_campus_data'][] = $campus_student_data;
        } else {

            $campuses = Campus::when($campus_ids, function ($query) use ($request) {
                return $query->whereIn('id', $request->campus_ids);
            })->get()->toArray();


            foreach ($campuses as $campus) {
                $campus_student_data = [
                    'campus_name' => $campus['name'], // Use find() to get a single model instance
                    'campus_data' => [], // Initialize to 0
                ];

                // $campus_name = Campus::find($campus)->name;

                $student_ids = Student::where('campus_id', $campus)
                    ->where('session_id', $request->year_id)
                    ->where('status', 2)
                    ->pluck('id')
                    ->toArray();

                $student_fee_break = DB::table('student_liable_fees')
                    ->select('amount', DB::raw('count(amount) as countStudent'))
                    ->whereIn('student_id', $student_ids)
                    ->groupBy('amount')
                    ->where('fees_type_id', 4)
                    ->get();

                $campus_student_data['campus_data'] = $student_fee_break;
                $data['all_campus_data'][] = $campus_student_data;
            }
        }

        return $this->sendResponse($data, '', 200);
    }
}
