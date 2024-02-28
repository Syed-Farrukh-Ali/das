<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\GeneralLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\FeeChallan;
use App\Models\FeeChallanDetail;

class SessionWiseFeeReportController extends BaseController
{
    public function sessionWiseFee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_id'  => 'required|exists:sessions,id',
            'campus_ids' => 'nullable|integer|exists:campuses,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        //GeneralLedger
        $session_id = $request->year_id;
        $campus_id = $request->campus_ids;

        $voucher_no = GeneralLedger::where('campus_id', $campus_id)
            ->where('session_id', $session_id)
            ->where('account_chart_id', '49')
            ->where('sub_account_id', '864')
            ->pluck('voucher_id')->toArray();

        $fee_challan_ids = FeeChallan::whereIn('voucher_id', $voucher_no)
            ->where('campus_id', $campus_id)
            ->pluck('id')->toArray();

        //Waqas Logic ******************///

        // $voucher_no = GeneralLedger::where('session_id', $session_id)
        //     ->where('account_chart_id', '49')
        //     ->where('sub_account_id', '864')
        //     ->pluck('voucher_id')->toArray();

        // $fee_challan_ids = FeeChallan::whereIn('voucher_id', $voucher_no)
        //     ->pluck('id')->toArray();


        //************ Chat GPT Logic */

        $sessionWise_students = FeeChallanDetail::select(
            'fee_challan_details.student_id',
            DB::raw('count(fee_challan_details.student_id) as months'),
            DB::raw('sum(fee_challan_details.amount) as total')
        )
            ->leftJoin('fee_challans', 'fee_challans.id', '=', 'fee_challan_details.fee_challan_id')
            ->where('fee_challan_details.fees_type_id', 4)
            ->where('fee_challans.status', 2)
            ->whereIn('fee_challans.id', $fee_challan_ids)
            ->groupBy('fee_challan_details.student_id')
            ->having(DB::raw('COUNT(fee_challan_details.student_id)'), '>', '8')
            ->with([
                'student' => function ($query) {
                    // Select specific columns from the "students" table
                    $query->select(
                        'id',
                        'name',
                        'father_name',
                        'mobile_no',
                        'admission_id',
                        'student_class_id',
                        'global_section_id'
                    );
                },
                'student.studentClass',
                'student.globalSection',
                'student.studentLiableFees',
                'student.feeChallanDetailsLast'
            ])
            ->get();

        return $this->sendResponse($sessionWise_students, '', 200);
    }
}
