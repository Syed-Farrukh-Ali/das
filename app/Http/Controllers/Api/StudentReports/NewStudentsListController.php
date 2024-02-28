<?php

namespace App\Http\Controllers\Api\StudentReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StudentReport\NewStudentRequest;
use App\Models\Campus;
use App\Models\CampusClass;
use App\Models\ClassSection;
use App\Models\GlobalSection;
use App\Models\Student;
use App\Models\StudentClass;

class NewStudentsListController extends BaseController
{
    public function index(NewStudentRequest $request)
    {
        $campuses = Campus::when($request->campus_id, function ($query) use ($request) {
            return $query->where('id', $request->campus_id);
            })->get()->toArray();

        $struck_off = false;

        if ($request->status >= 4){
            $struck_off = true;
        }

        foreach ($campuses as $campus)
        {
            $campusData = [
                'Campus' => $campus['name'],
                'classes' => []
            ];

            $class_ids = CampusClass::where('campus_id', $campus['id'])->pluck('student_class_id')->toArray();
            $classes = StudentClass::find($class_ids)->toArray();

            foreach ($classes as $class)
            {

                $classData = [
                    'Class' => StudentClass::where('id',$class['id'])->get(['name'])->first(),
                    'sections' => []
                ];

                $sections = ClassSection::where('campus_id',$campus['id'])
                    ->where('student_class_id',$class['id'])
                    ->pluck('global_section_id')
                    ->toArray();

                foreach ($sections as $section)
                {
                    $sectionData = [
                        'Section' => GlobalSection::where('id',$section)->get(['name'])->first(),
                    ];

                    $sectionData['students'] = Student::where('campus_id', $campus['id'])
                        ->where('student_class_id', $class['id'])
                        ->where('global_section_id', $section)
                        ->when($request->year_id, function ($query) use ($request) {
                            return $query->where('session_id', $request->year_id);
                        })
                        ->when($request->gender, function ($query) use ($request) {
                            return $query->where('gender', $request->gender);
                        })
                        ->where('status', $request->status);

                    if ($struck_off) {
                        $sectionData['students']->whereDate('struck_off_date', '>=', $request->start_date)
                            ->whereDate('struck_off_date', '<=', $request->end_date);
                    } else {
                        $sectionData['students']->whereDate('Joining_date', '>=', $request->start_date)
                            ->whereDate('Joining_date', '<=', $request->end_date);
                    }

                    $sectionData['students'] = $sectionData['students']->get();


                    $classData['sections'][] = $sectionData;
                }

                $campusData['classes'][] = $classData;
            }

            $response_data[] = $campusData;
        }

        return $this->sendResponse($response_data, '',200);
    }
}
