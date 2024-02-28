<?php

namespace App\Http\Controllers\Api\StudentReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StudentReport\MiscFeeRequest;
use App\Models\Campus;
use App\Models\CampusClass;
use App\Models\ClassSection;
use App\Models\GlobalSection;
use App\Models\Student;
use App\Models\StudentClass;

class MiscFeeController extends BaseController
{
    public function report(MiscFeeRequest $request)
    {

        $data = [];

        if ($request->campus_id)
        {
            $campusData = [
                'campus' => Campus::find($request->campus_id)->name,
                'classes' => [],
            ];

            $class_ids = CampusClass::where('campus_id', $request->campus_id)
                ->pluck('student_class_id')
                ->toArray();
            $classes = StudentClass::find($class_ids)->toArray();

            foreach ($classes as $class)
            {
                $global_sections = ClassSection::where('campus_id',$request->campus_id)->pluck('global_section_id')->unique()->toArray();

                $classData = [
                    'Class' => StudentClass::where('id',$class['id'])->get(['name'])->first(),
                    'sections' => []
                ];

                foreach ($global_sections as $section) {
                    $sectionData = [
                        'Section' => GlobalSection::where('id', $section)->get(['name'])->first(),
                        'students' => []
                    ];

                    $students = Student::with(['campus','studentClass','globalSection'])
                        ->where('global_section_id',$section)
                        ->where('campus_id',$request->campus_id)
                        ->where('student_class_id',$class['id'])
                        ->where('session_id', $request->year_id)
                        ->get();

                    foreach ($students as $student)
                    {
                        $fee_challan_detail = $student->feeChallans
                            ->where('status', 2)
                            ->pluck('feeChallanDetails')
                            ->flatten();


                        $amount = $fee_challan_detail->where('fees_type_id', $request->fees_type_id)->sum('amount');

                        if (!$amount)
                            continue;

                        $student->fees_amount = $amount;
                        $sectionData['students'][] = $student;
                    }

                    $classData['sections'][] = $sectionData;
                }

                $campusData['classes'] = $classData;
            }

            return $this->sendResponse($campusData, '',200);

        } else {
            $campuses = Campus::all()->toArray();

            foreach ($campuses as $campus)
            {

                $campusData = [
                  'campus' => $campus['name'],
                  'classes' => [],
                ];

                $class_ids = CampusClass::where('campus_id', $campus['id'])
                    ->pluck('student_class_id')
                    ->toArray();
                $classes = StudentClass::find($class_ids)->toArray();

                foreach ($classes as $class)
                {
                    $global_sections = ClassSection::where('campus_id',$campus['id'])->pluck('global_section_id')->unique()->toArray();

                    $classData = [
                        'Class' => StudentClass::where('id',$class['id'])->get(['name'])->first(),
                        'sections' => []
                    ];

                    foreach ($global_sections as $section) {
                        $sectionData = [
                            'Section' => GlobalSection::where('id', $section)->get(['name'])->first(),
                            'students' => []
                        ];

                        $students = Student::with(['campus','studentClass','globalSection'])
                            ->where('global_section_id',$section)
                            ->where('campus_id',$campus['id'])
                            ->where('student_class_id',$class['id'])
                            ->where('session_id', $request->year_id)
                            ->get();

                        foreach ($students as $student)
                        {
                            $fee_challan_detail = $student->feeChallans
                                ->where('status', 2)
                                ->pluck('feeChallanDetails')
                                ->flatten();

                            $amount = $fee_challan_detail->where('fees_type_id', $request->fees_type_id)->sum('amount');

                            if (!$amount)
                                continue;

                            $student->fees_amount = $amount;

                            $sectionData['students'][] = $student;
                        }

                        $classData['sections'][] = $sectionData;
                    }

                    $campusData['classes'][] = $classData;
                }
                $data[] = $campusData;
            }

            return $this->sendResponse($data, '',200);
        }
    }
}
