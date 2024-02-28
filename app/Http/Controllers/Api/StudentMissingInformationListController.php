<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassSection;
use App\Models\GlobalSection;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentMissingInformationListController extends Controller
{
    public function studentMissingInfoReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'missingInfoType'  => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }


        $class = ClassSection::get();
        $class->load('student', 'section');
        $classes = GlobalSection::select('name')->get();
        $selected_type = $request->missingInfoType;

        $missingInfoColumnName = "";

        if ($selected_type == 1) {
            $missingInfoColumnName = 'cnic_no';
        } else if ($selected_type == 2) {
            $missingInfoColumnName = 'father_cnic';
        } else {
            $missingInfoColumnName = 'mobile_no';
        }

        $student_Details = Student::where('status', '2')->whereNull($missingInfoColumnName)->get();
        $student_Details->load('studentClass', 'globalSection');

        $data = [
            'classes' => $class,
            'student_Details' => $student_Details,

        ];

        return ['student_Details' => $student_Details, 'classes_sections' => $class];
    }
}
