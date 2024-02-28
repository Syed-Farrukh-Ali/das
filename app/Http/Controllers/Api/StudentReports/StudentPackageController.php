<?php

namespace App\Http\Controllers\Api\StudentReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StudentReport\StudentPackageRequest;
use App\Http\Resources\StudentResourcePure;
use App\Models\Campus;
use App\Models\CampusClass;
use App\Models\ClassSection;
use App\Models\GlobalSection;
use App\Models\Student;
use App\Models\StudentClass;

class StudentPackageController extends BaseController
{
    public function report(StudentPackageRequest $request)
    {
        if ($request->session_wise)
        {
            $campuses = Campus::all()->toArray();

            $data = [];
            foreach ($campuses as $campus) {
                $campusData = [
                    'Campus' => $campus['name'],
                    'classes' => []
                ];

                $class_ids = CampusClass::where('campus_id', $campus['id'])->pluck('student_class_id')->toArray();
                $classes = StudentClass::find($class_ids)->toArray();

                foreach ($classes as $class) {

                    $classData = [
                        'Class' => StudentClass::where('id', $class['id'])->get(['name'])->first(),
                        'sections' => []
                    ];

                    $section_ids = ClassSection::where('campus_id',$campus['id'])
                                ->where('student_class_id',$class['id'])
                                ->pluck('global_section_id')->unique()->toArray();
                    $sections = GlobalSection::whereIn('id',$section_ids)->get()->toArray();

                    foreach ($sections as $section)
                    {

                        $students = Student::with(['campus','globalSection','studentClass','studentLiableFees'=>
                            function ($query)
                            {   return $query
                                ->where('fees_type_id',4)
                                ->first();
                            }])
                            ->where('campus_id',$campus['id'])
                            ->where('student_class_id',$class['id'])
                            ->where('global_section_id',$section['id'])
                            ->where('session_id',$request->year_id)
                            ->get();

                        $sectionData = [
                            'section' => $section['name'],
                            'students' => $students,
                        ];

                        $classData['sections'][] = $sectionData;
                    }

                    $campusData['classes'][] = $classData;
                }

                $data[] = $campusData;
            }

            return $this->sendResponse($data, '',200);
        }

        if ($request->campus_wise)
        {
                $data = [
                    'campus' => Campus::where('id',$request->campus_id)->value('name') ?? '',
                    'campus_data' => [],
                ];

                $class_ids = CampusClass::where('campus_id', $request->campus_id)->pluck('student_class_id')->toArray();
                $classes = StudentClass::find($class_ids)->toArray();

                foreach ($classes as $class) {

                    $classData = [
                        'Class' => StudentClass::where('id', $class['id'])->get(['name'])->first(),
                        'sections' => []
                    ];

                    $section_ids = ClassSection::where('campus_id',$request->campus_id)
                        ->where('student_class_id',$class['id'])
                        ->pluck('global_section_id')->unique()->toArray();
                    $sections = GlobalSection::whereIn('id',$section_ids)->get()->toArray();

                    foreach ($sections as $section)
                    {

                        $students = Student::with(['campus','globalSection','studentClass','studentLiableFees'=>
                            function ($query)
                            {   return $query
                                ->where('fees_type_id',4)
                                ->first();
                            }])
                            ->where('campus_id',$request->campus_id)
                            ->where('student_class_id',$class['id'])
                            ->where('global_section_id',$section['id'])
                        //    ->where('session_id',$request->year_id)
                            ->get();

                        $sectionData = [
                            'section' => $section['name'],
                            'students' => $students,
                        ];

                        $classData['sections'][] = $sectionData;
                    }

                    $data['campus_data'][] = $classData;
                }

            return $this->sendResponse($data, '',200);
        }

        if ($request->class_wise)
        {
                $classData = [
                    'campus' => Campus::where('id',$request->campus_id)->get(['name'])->first(),
                    'Class' => StudentClass::where('id', $request->class_id)->get(['name'])->first(),
                    'sections' => []
                ];

                $section_ids = ClassSection::where('campus_id',$request->campus_id)
                    ->where('student_class_id',$request->class_id)
                    ->pluck('global_section_id')->unique()->toArray();
                $sections = GlobalSection::whereIn('id',$section_ids)->get()->toArray();

                foreach ($sections as $section)
                {

                    $students = Student::with(['campus','globalSection','studentClass','studentLiableFees'=>
                        function ($query)
                        {   return $query
                            ->where('fees_type_id',4)
                            ->first();
                        }])
                        ->where('campus_id',$request->campus_id)
                        ->where('student_class_id',$request->class_id)
                        ->where('global_section_id',$section['id'])
                        //    ->where('session_id',$request->year_id)
                        ->get();

                    $sectionData = [
                        'section' => $section['name'],
                        'students' => $students,
                    ];

                    $classData['sections'] = $sectionData;
                }

            return $this->sendResponse($classData, '',200);
        }

        if ($request->section_wise)
        {
                $students = Student::with(['campus','globalSection','studentClass','studentLiableFees'=>
                    function ($query)
                    {   return $query
                        ->where('fees_type_id',4)
                        ->first();
                    }])
                    ->where('campus_id',$request->campus_id)
                    ->where('student_class_id',$request->class_id)
                    ->where('global_section_id',$request->section_id)
                    //    ->where('session_id',$request->year_id)
                    ->get();

            $sectionData = [
                'campus' => Campus::where('id',$request->campus_id)->get(['name'])->first(),
                'Class' => StudentClass::where('id', $request->class_id)->get(['name'])->first(),
                'section' => GlobalSection::where('id',$request->section_id)->get(['name'])->first(),
                'students' => $students,
            ];

            return $this->sendResponse($sectionData, '',200);
        }
    }
}
