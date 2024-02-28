<?php

namespace App\Http\Controllers\Api\StudentReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StudentReport\StudentConcessionReportRequest;
use App\Models\Campus;
use App\Models\CampusClass;
use App\Models\ClassSection;
use App\Models\GlobalSection;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentLiableFee;

class ConcessionReportController extends BaseController
{
    public function concessionReport(StudentConcessionReportRequest $request)
    {
        $campuses = Campus::get()->toArray();

        $data = [];

        $student_liable_fees = StudentLiableFee::where('fees_type_id', $request->fees_type_id)->first();

        if ($student_liable_fees != null) {
            foreach ($campuses as $campus) {

                $campusData = [
                    'Campus' => $campus['name'],
                    'classes' => []
                ];

                $class_ids = CampusClass::where('campus_id', $campus['id'])->pluck('student_class_id')->toArray();
                $classes = StudentClass::find($class_ids)->toArray();

                foreach ($classes as $class) {

                    $global_sections = ClassSection::where('campus_id', $campus['id'])->pluck('global_section_id')->unique()->toArray();

                    $classData = [
                        'Class' => StudentClass::where('id', $class['id'])->get(['name'])->first(),
                        'sections' => []
                    ];

                    foreach ($global_sections as $section) {
                        $sectionData = [
                            'Section' => GlobalSection::where('id', $section)->get(['name'])->first(),
                            'students' => []
                        ];

                        $students = Student::with(['campus', 'studentClass', 'globalSection', 'studentLiableFees'
                        => function ($query) use ($request) {
                            return $query->where('fees_type_id', $request->fees_type_id);
                        }])
                            ->where('global_section_id', $section)
                            ->where('campus_id', $campus['id'])
                            ->where('status', '2')
                            ->where('student_class_id', $class['id'])
                            ->when($request->year_id, function ($query) use ($request) {
                                return $query->where('session_id', $request->year_id);
                            })
                            ->when($request->concession_id, function ($query) use ($request) {
                                return $query->where('concession_id', $request->concession_id);
                            })
                            ->get();

                        foreach ($students as $student) {

                            $student_fee = StudentLiableFee::where('student_id', $student->id)
                                ->where('fees_type_id', $request->fees_type_id)
                                ->get()->first();

                            if ($student_fee) {
                                if ($student_fee->concession_amount >= 0) {
                                    $sectionData['students'][] = $student;
                                }
                            }
                        }

                        $classData['sections'][] = $sectionData;
                    }

                    $campusData['classes'][] = $classData;
                }

                $data[] = $campusData;
            }

            return $this->sendResponse($data, '', 200);
        } else {
            return $this->sendResponse([], "This fee type's record not exit", 200);
        }
    }
}
