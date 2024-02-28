<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CampusResource;
use App\Http\Resources\FeeChallanDetailResource;
use App\Http\Resources\FeeChallanResourceCopy;
use App\Http\Resources\StudentResource;
use App\Models\BankAccount;
use App\Models\BankAccountCategory;
use App\Models\Campus;
use App\Models\FeeChallan;
use App\Models\FeeChallanDetail;
use App\Models\HighestValue;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PartialFeeReceiveController extends BaseController
{
    public function getAllBanks(Request $req)
    {
        return BankAccountCategory::all();
    }


    public function updateChallanFee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'challan_detail_id'  => 'required|exists:fee_challan_details,id',
            'challan_amount' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $challan_detail_id  = $request->challan_detail_id;
        $challan_updated_amount = $request->challan_amount;

        DB::beginTransaction();
        try {

            $fee_challan_detail = FeeChallanDetail::find($challan_detail_id);
            $old_amount = $fee_challan_detail->amount;

            // Logic to set new payable in Fee Challan
            $challan_id = $fee_challan_detail->fee_challan_id;

            $old_payable = FeeChallan::where('id', $challan_id)->where('status', 0)
                ->pluck('payable')->first();

            if ($old_payable == null) {
                return $this->sendResponse('Challan Paid / Not Found', []);
            }

            $fee_challan_detail->update(['amount' => $challan_updated_amount]);

            $challan_detail_ids = FeeChallanDetail::where('fee_challan_id', $challan_id)->pluck('id');
            $new_payable = FeeChallanDetail::whereIn('id', $challan_detail_ids)->sum('amount');

            FeeChallan::find($challan_id)->update(['payable' => $new_payable]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], 500);
        }

        DB::commit();

        return $this->sendResponse('Succesfully Updated the challan', []);
    }


    public function SearchStudentChallans(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_value'  => 'required',
            'search_type'  => 'required',
            'challan_status' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $search_value = $request->search_value;
        $search_type = $request->search_type;
        $challan_status = $request->challan_status;

        $dbColumnName = '';

        $studentID = null;

        switch ($search_type) {
            case 'inv':
                $dbColumnName = 'challan_no';
                $studentID =  FeeChallan::where('challan_no', $search_value)->pluck('student_id')->first();
                break;
            case 'adm':
                $studentID = Student::where('admission_id', $search_value)->pluck('id')->first();
                $search_value = $studentID;
                $dbColumnName = 'student_id';
                break;
            case 'reg':
                $dbColumnName = 'student_id';
                $studentID = $search_value;
                break;
        }

        if (!$studentID) {
            $data = [
                'fee_challans_data' => [],
                'past_challans_detail' => [],
            ];

            return $this->sendResponse($data, []);
        }


        // $fee_challans_data =  FeeChallan::with('student')->with('feeChallanDetails')->with('campus')->with('bank_account')
        //     ->where('status', $challan_status)->where($dbColumnName, $search_value)->get();

        $student = Student::where('id', $studentID)->with('campus')->with('globalSection')->with('studentClass')->get();

        $fee_challans_data =  FeeChallan::with('feeChallanDetails')
            ->where('status', $challan_status)->where($dbColumnName, $search_value)->get();


        $fee_month = Carbon::today()->subMonth(11)->firstOfMonth();

        $challan_detail_past = FeeChallanDetail::with('feeChallan')->where(['student_id' => $studentID])->whereDate('fee_month', '>=', $fee_month)->get();

        // return $this->sendResponse(FeeChallanDetailResource::collection($challan_detail_of_past_6month), []);

        // $fee_challans_data->load('student.studentClass', 'student.globalSection', 'student.session');

        $data = [
            'fee_challans_data' => FeeChallanResourceCopy::collection($fee_challans_data),
            'student' => StudentResource::collection($student),
        ];


        if (!empty($challan_detail_past)) {
            $data['past_challans_detail'] = FeeChallanDetailResource::collection($challan_detail_past);
        } else {
            $data['past_challans_detail'] = [];
        }

        return $this->sendResponse($data, []);
    }

    public function SearchRollBackChallans(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_value'  => 'required',
            // 'search_type'  => 'required',
            // 'challan_status' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $search_value = $request->search_value;

        $studentID = Student::where('admission_id', $search_value)->pluck('id')->first();
        if (!$studentID) {
            $data = [
                'fee_challans_data' => [],
                'student' => [],
            ];

            return $this->sendResponse($data, []);
        }

        $student = Student::where('id', $studentID)->with('campus')->with('globalSection')->with('studentClass')->get();

        $fee_challans_data =  FeeChallan::with('feeChallanDetails')->with('bank_account')
            ->where('status', '1')->where('student_id', $studentID)->get();

        $data = [
            'fee_challans_data' => FeeChallanResourceCopy::collection($fee_challans_data),
            'student' => StudentResource::collection($student),
        ];

        return $this->sendResponse($data, []);
    }

    public function ReceivePartialFee(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'challan_ids.*'  => 'required|exists:fee_challans,id',
            'receiving_date' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $bank_account = $request->bank_account_id;
        $challan_ids = $request->challan_ids;
        $receiving_date = $request->receiving_date;

        $feed_at = Carbon::now();
        $student_id = FeeChallan::where('id', $challan_ids[0])->pluck('student_id')->first();

        DB::beginTransaction();
        try {

            // $seperate_challans = explode(',', $challan_ids);

            foreach ($challan_ids as $challan_id) {

                FeeChallan::where('id', $challan_id)->update([
                    'bank_account_id' => $bank_account, 'status' => '1',
                    'feed_at' => $feed_at, 'received_date' => $receiving_date, 'paid' => DB::raw('payable')
                ]);
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], 422);
        }

        DB::commit();
        $student = Student::find($student_id);

        if ($student->status == 3) {
            $student = _studentAdmission($student, $receiving_date);
            // return $this->sendResponse($student->admission_id, []);

            return $this->sendResponse("Admitted Successfully with Admission Number: " . $student->admission_id, []);
        }


        return $this->sendResponse("Successfully Received Fee", []);
    }

    public function getFeeChallanDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'challan_id'  => 'required|exists:fee_challans,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $challan_id = $request->challan_id;

        $student_challan_details =  FeeChallanDetail::where('fee_challan_id', '=', $challan_id)->get();

        return $this->sendResponse($student_challan_details, []);
    }

    public function ReceiveSubPartialFee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'challan_detail_ids.*'  => 'required|exists:fee_challan_details,id',
            'receiving_date' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $bank_account = $request->bank_account_id;
        $challan_detail_ids = $request->challan_detail_ids;
        $receiving_date = $request->receiving_date;

        $challan_id = FeeChallanDetail::where('id', $challan_detail_ids[0])->pluck('fee_challan_id');
        $student_id = 0;

        $totalCount = FeeChallanDetail::where('fee_challan_id', $challan_id)->count();
        $totalAmount = FeeChallanDetail::whereIn('id', $challan_detail_ids)->sum('amount');


        // if user selects all challans
        if (count($challan_detail_ids) == $totalCount) {
            // $updatedChallan = FeeChallan::find($challan_id);

            FeeChallan::where('id', $challan_id)->update([
                'bank_account_id' => $bank_account,
                'paid' => $totalAmount,
                'status' => '1',
                'received_date' => $receiving_date,
                'feed_at' => Carbon::now(),
            ]);

            return $this->sendResponse("Fee Received Successfully", []);
        }

        DB::beginTransaction();
        try {
            $originalFeeChallan = FeeChallan::find($challan_id)->first();
            $student_id = $originalFeeChallan->student_id;

            // give latest challan no
            $challan_latest_id = FeeChallan::max('id');
            $challan_latest = FeeChallan::find($challan_latest_id);
            $old_ch_no = $challan_latest ? $challan_latest->challan_no : 1;
            $new_challan_no = $old_ch_no + 1;
            //


            $newfeeChallan = FeeChallan::create([
                'student_id' => $student_id,
                'campus_id' => $originalFeeChallan->campus_id,
                'bank_account_id' => $bank_account,
                'challan_no' => $new_challan_no,
                'paid' => $totalAmount,
                'payable' => $totalAmount,
                'status' => '1',
                'received_date' => $receiving_date,
                'feed_at' => Carbon::now(),
                'issue_date' => $originalFeeChallan->issue_date,
                'due_date' => $originalFeeChallan->due_date,
            ]);

            $newId = $newfeeChallan->id;
            FeeChallanDetail::whereIn('id', $challan_detail_ids)
                ->update(['fee_challan_id' => $newId]);

            FeeChallan::where('id', $challan_id)
                ->decrement('payable', $totalAmount);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], 422);
        }

        DB::commit();

        $student = Student::find($student_id);



        if ($student->status == 3) {
            $student = _studentAdmission($student, $receiving_date);
            return $this->sendResponse("Admitted Successfully with Admission Number: " . $student->admission_id, []);
        }

        return $this->sendResponse("Fee Received Successfully", []);
    }

    public function GetStudentChallans(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'challan_ids.*'  => 'required|exists:fee_challans,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $student_id = $request->student_id;
        $fee_challan_ids = $request->challan_ids;

        $fee_challan_detail_ids = FeeChallanDetail::whereIn('fee_challan_id', $fee_challan_ids)
            ->pluck('id')
            ->toArray();

        $std = Student::where('id', $student_id)->get();

        $std->load('campus.printAccountNos');

        $std->load([
            'feeChallanDetails' => function ($query) use ($fee_challan_detail_ids) {
                return $query->whereIn('id', $fee_challan_detail_ids)->with('feeChallan');
            },
        ]);

        $data = [
            'total amount' => $std->pluck('feeChallans')->flatten()->sum('payable'),
            'students' => StudentResource::collection($std),
        ];

        return $this->sendResponse($data, 'student wise challan');
    }
}
