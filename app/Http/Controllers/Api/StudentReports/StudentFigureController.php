<?php

namespace App\Http\Controllers\Api\StudentReports;

use App\Http\Controllers\Api\BaseController;
use App\Models\Campus;
use App\Models\CampusClass;
use App\Models\ClassSection;
use App\Models\GlobalSection;
use App\Models\Student;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentFigureController extends BaseController
{
    public function StudentFigureReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|date_format:Y-m-d',
            'campus_ids' => 'nullable|array|exists:campuses,id',
            'campus_ids.*' => 'nullable|numeric|exists:campuses,id',
        ]);

        $campus_ids = $request->campus_ids;

        $campuses = Campus::when($campus_ids, function ($query) use ($request) {
            return $query->whereIn('id', $request->campus_ids);
        })->get()->toArray();

        $data = [];

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }

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

                $male = Student::where('campus_id', $campus['id'])
                    ->where('student_class_id', $class['id'])
                    ->where('Joining_date', '<=', $request->date)
                    ->where(function ($query) {
                        $query->where('struck_off_date', '>', '2023-12-01')
                            ->orWhere(function ($query) {
                                $query->whereNull('struck_off_date')
                                    ->where('status', 2);
                            });
                    })
                    ->where('gender', 'Male')
                    ->count();

                $female = Student::where('campus_id', $campus['id'])
                    ->where('student_class_id', $class['id'])
                    ->where('Joining_date', '<=', $request->date)
                    ->where(function ($query) {
                        $query->where('struck_off_date', '>', '2023-12-01')
                            ->orWhere(function ($query) {
                                $query->whereNull('struck_off_date')
                                    ->where('status', 2);
                            });
                    })
                    ->where('gender', 'Female')
                    ->count();

                $sectionData['report'] = [
                    'male' => $male,
                    'female' => $female,
                    'strength' => $male + $female,
                ];

                $classData['sections'][] = $sectionData;
                // }

                $campusData['classes'][] = $classData;
            }

            $data[] = $campusData;
        }
        return $this->sendResponse($data, '', 200);
    }
}
