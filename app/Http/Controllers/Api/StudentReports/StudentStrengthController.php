<?php

namespace App\Http\Controllers\Api\StudentReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StudentReport\StudentStrengthRequest;
use App\Models\Campus;
use App\Models\CampusClass;
use App\Models\ClassSection;
use App\Models\Course;
use App\Models\GlobalSection;
use App\Models\Student;
use App\Models\StudentClass;

class StudentStrengthController extends BaseController
{
    public function report(StudentStrengthRequest $request)
    {
        $campuses = Campus::when($request->campus_ids, function ($query) use ($request) {
            return $query->whereIn('id', $request->campus_ids);
        })->get()->toArray();

        $education_type = $request->education_type;

        $data = [];

        if ($request->summary) {

            foreach ($campuses as $campus) {
                $campusData = [
                    'Campus' => $campus['name'],
                    'classes' => []
                ];

                $class_ids = CampusClass::where('campus_id', $campus['id'])
                    ->pluck('student_class_id')
                    ->unique()
                    ->toArray();

                $classes = StudentClass::whereIn('id', $class_ids)->get()->toArray();

                foreach ($classes as $class) {

                    $classData = [
                        'Class' => StudentClass::where('id', $class['id'])->get(['name'])->first(),
                        'sections' => []
                    ];

                    $sections = ClassSection::where('campus_id', $campus['id'])
                        ->where('student_class_id', $class['id'])
                        ->pluck('global_section_id')
                        ->toArray();

                    foreach ($sections as $section) {
                        $sectionData = [
                            'Section' => GlobalSection::where('id', $section)->get(['name'])->first(),
                            'programs' => [],
                        ];

                        $programs = Course::all();

                        foreach ($programs as $program) {
                            $programData = [
                                'program' => $program->name,
                            ];

                            $strength = Student::with('course')
                                ->where('campus_id', $campus['id'])
                                ->where('session_id', $request->year_id)
                                ->where('student_class_id', $class['id'])
                                ->where('course_id', $program->id)
                                ->where('global_section_id', $section)
                                ->where('education_type', $education_type)
                                ->where('status', 2)
                                ->count();

                            $left_students = Student::where('campus_id', $campus['id'])
                                ->where('session_id', $request->year_id)
                                ->where('student_class_id', $class['id'])
                                ->where('course_id', $program->id)
                                ->where('global_section_id', $section)
                                ->where('education_type', $education_type)
                                ->where('status', 5)
                                ->count();

                            if ($strength == 0 && $left_students == 0)
                                continue;

                            $programData['report'] = [
                                'retain' => $strength - $left_students,
                                'left_students' => $left_students,
                                'strength' => $strength,
                            ];

                            $sectionData['programs'][] = $programData;
                        }

                        $classData['sections'][] = $sectionData;
                    }

                    $campusData['classes'][] = $classData;
                }

                $data[] = $campusData;
            }
        } else {

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

                    $sections = ClassSection::where('campus_id', $campus['id'])
                        ->where('student_class_id', $class['id'])
                        ->pluck('global_section_id')
                        ->toArray();

                    foreach ($sections as $section) {
                        $sectionData = [
                            'Section' => GlobalSection::where('id', $section)->get(['name'])->first(),
                        ];

                        $male = Student::where('campus_id', $campus['id'])
                            ->where('student_class_id', $class['id'])
                            ->where('global_section_id', $section)
                            ->where('status', 2)
                            // ->where('education_type', $education_type)
                            ->when($education_type != '0', function ($query) use ($education_type) {
                                return $query->where('education_type', $education_type);
                            })
                            ->where('gender', 'Male')
                            ->count();



                        $female = Student::where('campus_id', $campus['id'])
                            ->where('student_class_id', $class['id'])
                            ->where('global_section_id', $section)
                            ->where('status', 2)
                            // ->where('education_type', $education_type)
                            ->when($education_type != '0', function ($query) use ($education_type) {
                                return $query->where('education_type', $education_type);
                            })
                            ->where('gender', 'Female')
                            ->count();

                        $sectionData['report'] = [
                            'male' => $male,
                            'female' => $female,
                            'strength' => $male + $female,
                        ];

                        $strength = $male + $female;

                        if ($male != 0 || $female != 0 && $strength != 0)
                            $classData['sections'][] = $sectionData;
                        else
                            continue;
                    }

                    $campusData['classes'][] = $classData;
                }

                $data[] = $campusData;
            }
        }

        return $this->sendResponse($data, '', 200);
    }
}
