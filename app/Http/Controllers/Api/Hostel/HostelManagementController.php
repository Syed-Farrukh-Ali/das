<?php

namespace App\Http\Controllers\Api\Hostel;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Hostel\AssignHostelRequest;
use App\Http\Requests\Hostel\HostelFilterRequest;
use App\Http\Requests\Hostel\HostelStudentRequest;
use App\Http\Requests\Hostel\RemoveHostelStudentRequest;
use App\Http\Resources\FeeChallanResourceCopy;
use App\Http\Resources\StudentResourcePure;
use App\Models\FeeChallan;
use App\Models\Student;
use App\Models\StudentLiableFee;

class HostelManagementController extends BaseController
{

    public function hostelStudentNameSearch(HostelFilterRequest $request)
    {
        $isAdmission = null;
        $isName = null;
        if (preg_match('~[0-9]+~', $request->search_keyword)) {
            $isAdmission = true;
        } else {
            $isName = true;
        }

        if ($isAdmission) {

            $student = Student::where('admission_id', 'like', '%'.$request->search_keyword.'%')
                ->where('hostel_id', '!=', 'null')
                ->where('status', '=', '2')
                ->with('hostel','campus', 'studentClass', 'globalSection','studentLiableFees')
                ->get();

            return $this->sendResponse(StudentResourcePure::collection($student), [], 200);
        }
        elseif ($isName) {

            $student = Student::where('name', 'like', '%'.$request->search_keyword.'%')
                ->where('hostel_id', '!=', 'null')
                ->where('status', '=', '2')
                ->with('hostel','campus', 'studentClass', 'globalSection','studentLiableFees')
                ->get();

            return $this->sendResponse(StudentResourcePure::collection($student), [], 200);
        }
    }

    public function hostelStudents(HostelStudentRequest $request)
    {
        $student = Student::where('hostel_id', '=', $request->hostel_id)
            ->where('status', '=', '2')
            ->with('hostel','campus', 'studentClass', 'globalSection','studentLiableFees')
            ->get();

        return $this->sendResponse(StudentResourcePure::collection($student), [], 200);
    }

    public function assignHostelStudent(AssignHostelRequest  $request)
    {

        $student = Student::where('id','=',$request->student_id)
            ->first();
        if ($student->hostel_id)
        {
            return $this->sendError('this student already admitted to hostel',[],200);

        }
        $student->setLiableFees($request->toArray());

        if ($student->status == 2) {
            $student->update(['hostel_id' => $request->hostel_id]);

            $date = date('Y-m-d');

            $old = FeeChallan::max('challan_no') ?? 1;

            $new_challan_no = $old + 1;
            $feeChallan = $student->FeeChallans()->create([
                'campus_id' => $student->campus_id,
                'challan_no' => $new_challan_no,
                'payable' => null, //first fee chalan detail wil be created & then sum of amount wil be here
                'due_date' => $request->due_date ?? $date,
                'issue_date' => $request->issue_date ?? $date,
            ]);

            // hostel admission fees
            $feeChallan->feeChallanDetails()->create([
                'student_id' => $student->id,
                'amount' => $request->hostel_admission_fee ?? 3300,
                'fee_month' => $request->fee_months[0],
                'fee_name' => 'HOSTEL ADMISSION FEE',
                'campus_id' => $student->campus_id,
                'fees_type_id' => 6,
            ]);
            // monthly defined fees is creating,
            foreach ($request->fee_months as $key => $month) {

                    $feeChallan->feeChallanDetails()->create([
                        'student_id' => $student->id,
                        'amount' => $request->fee_amount[0],
                        'fee_month' => $month,
                        'fee_name' => 'HOSTEL FEE',
                        'campus_id' => $student->campus_id,
                        'fees_type_id' => $request->fee_type_id[0],
                    ]);

            }

            $feeChallan->update([
                'payable' => $feeChallan->feeChallanDetails()->sum('amount'),
            ]);

        }

        $student->load('studentLiableFees.feesType','hostel');

        $data = [
            'student' =>  new StudentResourcePure($student),
            'challan' =>    new FeeChallanResourceCopy($feeChallan),
        ];
        return $this->sendResponse($data, [], 200);
    }

    public function removeHostelStudent(RemoveHostelStudentRequest $request)
    {
        $student = Student::find($request->student_id);

        if (!$student->hostel_id)
        {
            return $this->sendError('Student does not have hostel',[],200);
        } else {
            $student->update(['hostel_id' => null]);

            StudentLiableFee::where('student_id','=',$student->id)
                ->where('fees_type_id','=',7)
                ->delete();

            return $this->sendError('Successfully removed hostel',[],200);
        }
    }

}
