<?php

namespace App\Repository;

use App\Http\Resources\FeeChallanDetailResource;
use App\Http\Resources\FeeChallanResource;
use App\Http\Resources\FeeChallanResourceCopy;
use App\Http\Resources\StudentResource;
use App\Models\Campus;
use App\Models\FeeChallan;
use App\Models\FeeChallanDetail;
use App\Models\Student;
use App\Repository\Interfaces\FeesChallanRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FeesChallanRepository extends BaseRepository implements FeesChallanRepositoryInterface
{
    /**$userRepository
      * ProfileRepository constructor.
      *
      * @param User $model
      */
    public function __construct(Student $model)
    {
        parent::__construct($model);
    }

    public function campusChallan(Campus $campus, $status)
    {
        $std_ids = $campus->students->pluck('id')->toArray();
        $challans = FeeChallan::with('feeChallanDetails', 'campus.printAccountNos')->whereIn('student_id', $std_ids)->where('status', $status)->latest()->paginate(10);
        $challans_for_total = FeeChallan::whereIn('student_id', $std_ids)->where('status', '<', 2)->get();
        $totalPaid = $challans_for_total->sum('paid');
        $totalPayable = $challans_for_total->sum('payable');
        $netPayable = $totalPayable - $totalPaid;

        return [
            'total payable' => $totalPayable,
            'total paid' => $totalPaid,
            'net payable' => $netPayable,
            'challans' => FeeChallanResource::collection($challans)->resource,
        ];
    }

     public function classChallan(Campus $campus, $class_id, $education_type, $status)
     {
         $std_ids = $campus->students()
                    ->where('student_class_id', $class_id)
                    ->where('education_type', $education_type)
                    ->pluck('id')
                    ->toArray();

         $challans = FeeChallan::with('feeChallanDetails', 'campus.printAccountNos')->whereIn('student_id', $std_ids)->where('status', $status)->latest()->paginate(10);
         $challans_for_total = FeeChallan::whereIn('student_id', $std_ids)->where('status', '<', 2)->get();
         $totalPaid = $challans_for_total->sum('paid');
         $totalPayable = $challans_for_total->sum('payable');
         $netPayable = $totalPayable - $totalPaid;

         return [
             'total payable' => $totalPayable,
             'total paid' => $totalPaid,
             'net payable' => $netPayable,
             'challans' => FeeChallanResource::collection($challans)->resource,

         ];
     }

    public function sectionChallan(Campus $campus, $class_id, $section_id, $status)
    {
        $std_ids = $campus->students()->where(['student_class_id' => $class_id, 'global_section_id' => $section_id])->pluck('id')->toArray();

        $challans = FeeChallan::with('feeChallanDetails', 'campus.printAccountNos')->whereIn('student_id', $std_ids)->where('status', $status)->latest()->paginate(10);
        $challans_for_total = FeeChallan::whereIn('student_id', $std_ids)->where('status', '<', 2)->get();
        $totalPaid = $challans_for_total->sum('paid');
        $totalPayable = $challans_for_total->sum('payable');
        $netPayable = $totalPayable - $totalPaid;

        return [
            'total payable' => $totalPayable,
            'total paid' => $totalPaid,
            'net payable' => $netPayable,
            'challans' => FeeChallanResource::collection($challans)->resource,

        ];
    }

    public function getChallanByNo($challan_no)
    {
        $challan = FeeChallan::where('challan_no', $challan_no)->first();
        $challan->load('feeChallanDetails', 'student.session', 'student.campus.printAccountNos');

        return new FeeChallanResource($challan);
    }

    public function feeReceiving(FeeChallan $feeChallan, Request $request)
    {
        $student = $feeChallan->student;
        $challan = $feeChallan;
        $challan = $this->submit($challan, $request);
        if ($student->status == 3) {
            $student = _studentAdmission($student, $request->received_date);
        }

        return new FeeChallanResource($challan);
        //_.._.._.._._.._.._._.._.._._.._.._.._..
    }

    public function createFeechallanWithFeeDetial(Student $student, $due_date, $fee_details, $challan_no = null)
    {
        if (! $challan_no) {
            $old = FeeChallan::max('challan_no') ?? 1;
            $challan_no = $old + 1;
        }

        $new_challan = FeeChallan::create([
            'challan_no' => $challan_no,
            'student_id' => $student->id,
            'campus_id' => $student->campus_id,
            'issue_date' => date('Y-m-d'),
            'due_date' => $due_date,
            'payable' => 0,
        ]);

        foreach ($fee_details as $key => $fee_detail) {
            $new_challan->feeChallanDetails()->create([
                'student_id' => $student->id,
                'amount' => $fee_detail['amount'],
                'fee_month' => $fee_detail['fee_month'],
                'fee_name' => $fee_detail['fee_name'],
                'campus_id' => $student->campus_id,
                'fees_type_id' => $fee_detail['fees_type_id'],
            ]);
            $total_amount = $new_challan->feeChallanDetails->sum('amount');
            $new_challan->update(['payable' => $total_amount]);

            return $new_challan;
        }
    }

    public function studentUnpaidChllans(Request $request)
    {
        $fee_month = Carbon::today()->subMonth(11)->firstOfMonth();
        if ($request->admission_id) {
            $student = Student::with('campus.printAccountNos')->where('admission_id', $request->admission_id)->first();
        } else {
            $student = Student::with('campus.printAccountNos')->where('registration_id', $request->registration_id)->first();
        }

        $student_unpaid_challans = FeeChallan::with('feeChallanDetails')->where(['student_id' => $student->id, 'status' => 0])->get();
        $fee_challan_details = FeeChallanDetail::whereIn('fee_challan_id', $student_unpaid_challans->pluck('id')->toArray())->get();
        $fee_challan_details->load('feeChallan');

        $challan_detail_of_past_6month = FeeChallanDetail::with('feeChallan')->where(['student_id' => $student->id])->whereDate('fee_month', '>=', $fee_month)->get();
        $data = [
            'total_amount' => $fee_challan_details->sum('amount'),
            'student' => new StudentResource($student),
            'fee_challan_details' => FeeChallanDetailResource::collection($fee_challan_details),
            'student_challans' => FeeChallanResourceCopy::collection($student_unpaid_challans),
            'past_6_month_challan_details' => FeeChallanDetailResource::collection($challan_detail_of_past_6month),
        ];

        return $data;
    }

    public function editChallanDetailFeesubmit(Request $request)
    {
        $challan_detial_object = FeeChallanDetail::find($request->fee_challan_detail_id);
        $fee_challan = FeeChallan::find($challan_detial_object->fee_challan_id);
        if ($fee_challan->status != 0) {
            return false;
        }
        $challan_detial_object->update(['amount' => $request->amount]);
        $fee_challan->update(['payable' => $fee_challan->feeChallanDetails->sum('amount')]);

        $fee_month = Carbon::today()->subMonth(5)->firstOfMonth();
        $student = Student::find($request->student_id);
        $student_unpaid_challans = FeeChallan::with('feeChallanDetails', 'campus.printAccountNos')->where(['student_id' => $student->id, 'status' => 0])->get();
        $fee_challan_details = FeeChallanDetail::whereIn('fee_challan_id', $student_unpaid_challans->pluck('id')->toArray())->get();
        $fee_challan_details->load('feeChallan');

        $challan_detail_of_past_6month = FeeChallanDetail::with('feeChallan')->where(['student_id' => $student->id])->whereDate('fee_month', '>=', $fee_month)->get();
        $data = [
            'total_amount' => $fee_challan_details->sum('amount'),
            'student' => new StudentResource($student->load('campus.printAccountNos')),
            'fee_challan_details' => FeeChallanDetailResource::collection($fee_challan_details),
            'student_challans' => FeeChallanResourceCopy::collection($student_unpaid_challans),
            'past_6_month_challan_details' => FeeChallanDetailResource::collection($challan_detail_of_past_6month),
        ];

        return $data;
    }

    public function submitStudentUnpaidChllans(Request $request)
    {
        $student = Student::find($request->student_id);
        $challans = FeeChallan::where(['status' => 0, 'student_id' => $student->id])->get();

        foreach ($challans as $key => $challan) {
            $challan->update([
            'paid' => $challan->payable,
            'status' => 1,
            'feed_at' => Carbon::now(),
            'received_date' => $request->received_date,
            'bank_account_id' => $request->bank_account_id,
            ]);
        }

        if ($student->status == 3) {
            $student = _studentAdmission($student, $request->received_date);
            return Student::find($request->student_id);
        }

        return Student::find($request->student_id);
    }

// this function war written for challan to challan child parent relation no future use now
    protected function submit(FeeChallan $challan, Request $request)
    {
        $challan->update([
            'paid' => $challan->payable,
            'status' => 1,
            'feed_at' => Carbon::now(),
            'received_date' => $request->received_date,
            'bank_account_id' => $request->bank_account_id,
        ]);

        return $challan;
    }

    public function feeRoleback(FeeChallan $feeChallan)
    {
        $feeChallan->update([
            'paid' => null,
            'bank_account_id' => null,
            'status' => 0,
            'received_date' => null,
        ]);

        $feeChallan->load('feeChallanDetails', 'campus.printAccountNos');

        return new FeeChallanResource($feeChallan);
    }

    public function destroy(FeeChallan $feeChallan)
    {
        $feeChallan->delete();

        return response()->json('challan successfully deleted');
    }
}
