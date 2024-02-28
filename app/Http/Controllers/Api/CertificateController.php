<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CampusClass;
use App\Models\Certificate;
use App\Models\FeeChallan;
use App\Models\Student;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CertificateController extends BaseController
{
    public function SaveCertificate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admission_id'  => 'required|exists:students,admission_id',
            'leaving_date' => 'required',
            'certificate_type' => 'required',
            'issue_date' => 'required',
            'passed_class' => 'required',
            'total_Marks' => 'required',
            'obtain_marks' => 'required',
            'class_position' => 'required',
            'total_attandance' => 'required',
            'attandance' => 'required',
            'migration' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }


        $Student_id = Student::where('admission_id', $request->admission_id)->pluck('id')->first();
        $check_certificate = Certificate::where('student_Id', $Student_id)
            ->where('certificate_type_id', $request->certificate_type)
            ->first();

        if ($check_certificate) {


            $check_certificate->issue_date = $request->issue_date;
            $check_certificate->leaving_date = $request->leaving_date;
            $check_certificate->class_passed_fail = $request->passed_class;
            $check_certificate->total_marks = $request->total_Marks;
            $check_certificate->obtain_marks = $request->obtain_marks;
            $check_certificate->class_position = $request->class_position;
            $check_certificate->total_Attendance = $request->total_attandance;
            $check_certificate->attendance = $request->attandance;
            if ($request->certificate_type == '1') {
                $check_certificate->activity = $request->migration;
            } else {
                $check_certificate->migration_to = $request->migration;
            }
            $check_certificate->certificate_type_id = $request->certificate_type;
            $check_certificate->save();


            return $this->sendResponse('Certificate Updated Successfuly', 200);
        }

        $student_status = 2;

        if ($request->certificate_type == '2') {
            $student_status = '7';
        } else {
            $student_status = '6';
        }
        $results = "";
        Student::where('admission_id', $request->admission_id)->update(['status' => $student_status, 'struck_off_date' => $request->leaving_date]);
        if ($request->certificate_type == '1') {
            $results = Certificate::updateOrCreate([
                'student_id' => $Student_id,
                'issue_date' => $request->issue_date,
                'leaving_date' => $request->leaving_date,
                'class_passed_fail' => $request->passed_class,
                'total_marks' => $request->total_Marks,
                'obtain_marks' => $request->obtain_marks,
                'class_position' => $request->class_position,
                'total_Attendance' => $request->total_attandance,
                'attendance' => $request->attandance,
                'activity' => $request->migration,
                'certificate_type_id' => $request->certificate_type,
            ]);
        } else {
            $results = Certificate::updateOrCreate([
                'student_id' => $Student_id,
                'issue_date' => $request->issue_date,
                'leaving_date' => $request->leaving_date,
                'class_passed_fail' => $request->passed_class,
                'total_marks' => $request->total_Marks,
                'obtain_marks' => $request->obtain_marks,
                'class_position' => $request->class_position,
                'total_Attendance' => $request->total_attandance,
                'attendance' => $request->attandance,
                'migration_to' => $request->migration,
                'certificate_type_id' => $request->certificate_type,
            ]);
        }

        if ($results) {
            return $this->sendResponse('Certificate Created Successfuly', 200);
        }
    }

    // public function LeavingCertificate(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'admission_id'  => 'required|exists:students,admission_id',
    //         'leaving_date' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->sendError($validator->errors(), [], 422);
    //     }
    //     $Student_id = Student::where('admission_id', $request->admission_id)->pluck('id')->first();
    //     Student::where('admission_id', $request->admission_id)->update(['status' => '6', 'struck_off_date' => $request->leaving_date]);
    //     $results = Certificate::create([
    //         'student_id' => $Student_id,
    //         'issue_date' => $request->issue_date,
    //         'leaving_date' => $request->leaving_date,
    //         'class_passed_fail' => $request->passed_class,
    //         'total_marks' => $request->total_Marks,
    //         'obtain_marks' => $request->obtain_marks,
    //         'class_position' => $request->class_position,
    //         'total_Attendance' => $request->total_attandance,
    //         'attendance' => $request->attandance,
    //         'migration_to' => $request->migration,
    //         'activity' => 'NULL',
    //         'certificate_type_id' => '1'
    //     ]);
    //     if ($results) {
    //         return $this->sendResponse('Certificate Created Successfuly', 200);
    //     }
    // }

    public function viewCerificate(Request $request)
    {
        //return $request->search_keyword;certificate_type: "1",
        $validator = Validator::make($request->all(), [
            'admission_id'  => 'required|exists:students,admission_id',
            'certificate_type'  => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        //$ex[0]

        $admission_id = $request->admission_id;
        $certificate_type = $request->certificate_type;

        $Student_id = Student::where('admission_id', $admission_id)->pluck('id')->first();
        //return Student::where('student_id',$ex[0])->with('certificate')->with('certificate_type')->get();
        //return Certificate::where('student_id',$ex[0])->with('student')->with('certificate_type')->get();
        $certificate_find = Certificate::where('certificate_type_id', $certificate_type)->where('student_id', $Student_id)->get();
        // return $certificate_find;
        if ($certificate_find->isNotEmpty()) {
            $certificate_data = Student::where('admission_id', $request->admission_id)
                ->with('certificate')
                ->with('campus')
                ->with('globalSection')
                ->with('studentClass')
                ->get();
            //$challans->load('certificate');
            return $this->sendResponse($certificate_data, '', 200);
        } else {
            $certificate_data = Student::where('admission_id', $request->admission_id)
                ->with('campus')
                ->with('globalSection')
                ->with('studentClass')
                ->get()
                ->map(function ($student) {
                    $student->setAttribute('certificate', []);
                    return $student;
                });
            //$challans->load('certificate');
            return $this->sendResponse($certificate_data, '', 200);
        }
    }
    public function CertificateAll(Request $req)
    {
        $certificate_type = $req->certificate_type;
        $session_id = $req->year_id;
        // $certificates = Certificate::with(['certificate_type', 'student' => function ($query) use ($session_id) {
        //     $query->where('session_id', $session_id); // Select only the required fields
        // }, 'student.studentClass', 'student.globalSection'])->where('certificate_type_id', $certificate_type)->orderBy('id', 'desc')->get();
        $certificates = Certificate::with(['certificate_type', 'student.studentClass', 'student.globalSection'])
            ->whereHas('student', function ($query) use ($session_id) {
                $query->where('session_id', $session_id);
            })
            ->where('certificate_type_id', $certificate_type)
            ->orderBy('id', 'desc')
            ->get();
        //return $certificate_id + 1;
        return $this->sendResponse($certificates, '', 200);
    }
    public function Certificate_id(Request $req)
    {
        $certificate_id = Certificate::orderBy('id', 'desc')->pluck('id')->first();
        //return $certificate_id + 1;
        return $this->sendResponse($certificate_id, '', 200);
    }
    public function campusClass(Request $req)
    {
        $idx = $req->id;
        $classes = CampusClass::with('student')->where('campus_id', $idx)->get();
        return $classes;
    }
    public function StudentClasses(Request $req)
    {
        $classes = StudentClass::get();
        //$classes->load('student');
        return $classes;
    }
    public function paidchallansearch(Request $req)
    {
        $student_id = 0;
        $ex = Student::where('admission_id', $req->id)->get();
        foreach ($ex as $dx) {
            $student_id = $dx['id'];
        }
        $hostel_fees =  FeeChallan::with('feeChallanDetails1')->where('status', '2')->where('student_id', $student_id)->orderBy('id', 'desc')->first();
        return $hostel_fees;
    }



    public function allchallansearchforcertificate(Request $req)
    {
        $student_id = 0;
        $ex = Student::where('admission_id', $req->id)->get();
        foreach ($ex as $dx) {
            $student_id = $dx['id'];
        }
        $sumtotal = FeeChallan::where('student_id', $student_id)->sum('payable');
        $hostel_fees =  FeeChallan::with('student')->with('feeChallanDetails')->with('campus')->where('status', '0')->where('student_id', $student_id)->get();
        $hostel_fees->load('student.studentClass', 'student.globalSection', 'student.session', 'campus.printAccountNos');
        return $hostel_fees;
    }
}
