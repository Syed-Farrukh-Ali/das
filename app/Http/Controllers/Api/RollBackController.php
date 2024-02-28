<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeeChallan;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RollBackController extends BaseController
{
    public function RollBackChallan(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'challan_ids.*'  => 'required|exists:fee_challans,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $challan_ids = $request->challan_ids;

        DB::beginTransaction();
        try {

            // $seperate_challans = explode(',', $challan_ids);

            foreach ($challan_ids as $challan_id) {

                FeeChallan::where('id', $challan_id)->update([
                    'bank_account_id' => null, 'status' => '0',
                    'feed_at' => null, 'received_date' => null, 'paid' => null
                ]);
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], 422);
        }

        DB::commit();


        return $this->sendResponse("Fee Successfully Rolled Back", []);
    }

    public function UpdateChallan(Request $request)
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

        DB::beginTransaction();
        try {

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

        return $this->sendResponse("Fee Challan Successfully Updated", []);
    }

    public function SearchBankWiseChallans(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'receiving_date' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $bank_account = $request->bank_account_id;
        $receiving_date = $request->receiving_date;

        $fee_challans_data =  FeeChallan::with('student')->with('feeChallanDetails')->with('campus')->with('bank_account')
            ->where('status', '1')->where('bank_account_id', $bank_account)->where('received_date', $receiving_date)->get();

        $fee_challans_data->load('student.studentClass', 'student.globalSection', 'student.session');
        return $this->sendResponse($fee_challans_data, []);
    }
}
