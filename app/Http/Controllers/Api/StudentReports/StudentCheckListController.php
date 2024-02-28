<?php

namespace App\Http\Controllers\Api\StudentReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StudentReport\StudentCheckListRequest;
use App\Http\Resources\StudentResource;
use App\Models\Campus;
use App\Models\CampusClass;
use App\Models\ClassSection;
use App\Models\GlobalSection;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentLiableFee;

class StudentCheckListController extends BaseController
{
    public function checkListReport(StudentCheckListRequest $request)
    {
        $data = [
            'campus' => Campus::where('id',$request->campus_id)->value('name') ?? '',
            'campus_data' => [],
        ];

        $education_type = $request->education_type;

        if ($request->class_id){
            $class_ids = [$request->class_id];
        }else{
            $class_ids = CampusClass::where('campus_id', $request->campus_id)->pluck('student_class_id')->toArray();
        }

        $classes = StudentClass::find($class_ids)->toArray();

        foreach ($classes as $class) {

            $classData = [
                'Class' => StudentClass::where('id', $class['id'])->get(['name'])->first(),
                'sections' => []
            ];

            if ($request->section_id){
                $section_ids = [$request->section_id];
            }else{
                $section_ids = ClassSection::where('campus_id',$request->campus_id)
                    ->where('student_class_id',$class['id'])
                    ->pluck('global_section_id')->unique()->toArray();
            }

            $sections = GlobalSection::whereIn('id',$section_ids)->get()->toArray();

            foreach ($sections as $section)
            {

                $students = Student::where('campus_id',$request->campus_id)
                    ->where('student_class_id',$class['id'])
                    ->where('global_section_id',$section['id'])
                    ->where('session_id',$request->year_id)
                    ->where(function ($query) use ($education_type) {
                        return  $education_type != null ? $query->where('education_type', $education_type) : '';
                    })
                    ->where('status',2)
                    ->get();

                foreach ($students as $student){
                    $student_fee = StudentLiableFee::where('student_id',$student->id)
                            ->where('fees_type_id',4)
                            ->first();

                    if ($student_fee){
                        $student->monthly_fees = $student_fee->amount;
                    }
                }

                $sectionData = [
                    'section' => $section['name'],
                    'students' => StudentResource::collection($students),
                ];

                $classData['sections'][] = $sectionData;
            }

            $data['campus_data'][] = $classData;
        }

        return $this->sendResponse($data, '',200);
    }
}
