<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Accounts\SubAccountResource;
use App\Models\AccountChart;
use App\Models\SubAccount;
use App\Models\GeneralLedger;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SubAccountController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function subAccountBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_id' => 'required|exists:sessions,id',
            'sub_account_id' => 'required|exists:sub_accounts,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }

        $vocher_ids_for_balance = Voucher::where('session_id', $request->year_id)
            ->whereDate('date', '<=', Carbon::now()->format('Y-m-d'))
            ->pluck('id')->unique()->toArray();

        # ._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._.
        $subAccount = SubAccount::find($request->sub_account_id);

        $debit = GeneralLedger::where('sub_account_id', $subAccount->id)->where('session_id', $request->year_id)->whereIn('voucher_id', $vocher_ids_for_balance)->sum('debit');
        $credit = GeneralLedger::where('sub_account_id', $subAccount->id)->where('session_id', $request->year_id)->whereIn('voucher_id', $vocher_ids_for_balance)->sum('credit');

        $final_credit = 0;
        $final_debit = 0;

        if ($credit - $debit > 0) {
            $final_credit = $credit - $debit;
        }

        if ($debit - $credit > 0) {
            $final_debit = $debit - $credit;
        }

        $data = [
            'opening_balance_credit' => $final_credit,
            'opening_balance_debit' => $final_debit,
        ];

        return $this->sendResponse($data, [], 200);
    }
    public function index()
    {
        $subaccount = SubAccount::all();

        return $this->sendResponse(SubAccountResource::collection($subaccount), [], 200);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_chart_id' => 'required|integer|exists:account_charts,id',
            'acode' => 'required|integer',
            'title' => 'required|string|',
            // 'torise_debit'               => 'required|integer|min:0|max:1',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        DB::beginTransaction();
        try {
            $accountChart = AccountChart::find($request->account_chart_id);
            $accountChart->sub_accounts()->create([
                'acode' => $request->acode,
                'title' => $request->title,
                'torise_debit' => $accountChart->torise_debit,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->sendError('internal server error', [], 500);
        }
        DB::commit();

        return $this->sendResponse([], 'account created successfully', 200);
    }


    public function show(SubAccount $subaccount)
    {
        return $this->sendResponse(new SubAccountResource($subaccount), [], 200);
    }

    public function contra()
    {
        $accountChart_ids = AccountChart::whereIn('acode', [1401, 1402])->pluck('id');
        $subaccounts = SubAccount::whereIn('account_chart_id', $accountChart_ids)->get();

        return $this->sendResponse($subaccounts, [], 200);

        // // $accountChart_ids = AccountChart::where('acode', '1401')->with('sub_accounts')->get();
        // $subaccounts = SubAccount::whereIn('account_chart_id', [26, 27])->get(); //temporary
        // return $this->sendResponse($subaccounts, [], 200);
    }

    public function loanAccounts()
    {
        $accountChart_id = AccountChart::where('acode', 1301)->first()->id;
        $subaccounts = SubAccount::where('account_chart_id', $accountChart_id)->get();

        return $this->sendResponse(SubAccountResource::collection($subaccounts), [], 200);
    }

    public function FeesAccounts()
    {
        $accountChart_ids = AccountChart::whereIn('acode', [4201, 4203])->pluck('id')->toArray();
        $subaccounts = SubAccount::whereIn('account_chart_id', $accountChart_ids)->get();

        return $this->sendResponse(SubAccountResource::collection($subaccounts), [], 200);
    }


    public function update(Request $request, SubAccount $subaccount)
    {
        $validator = Validator::make($request->all(), [
            'acode' => 'required|integer',
            'title' => 'required|string|',
            // 'torise_debit'               => 'required|integer|min:0|max:1',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        DB::beginTransaction();
        try {
            $subaccount->update([
                'acode' => $request->acode,
                'title' => $request->title,
                // 'torise_debit' => $request->torise_debit,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->sendError('internal server error', [], 500);
        }
        DB::commit();

        return $this->sendResponse([], 'account updated successfully', 200);
    }

    public function subAccountBanks()
    {
        $accountChart_ids = AccountChart::whereIn('acode', [1401, 1402])->pluck('id')->toArray();

        $subaccount = SubAccount::whereIn('account_chart_id', $accountChart_ids)->get();

        return $this->sendResponse(SubAccountResource::collection($subaccount), [], 200);
    }

    public function destroy(SubAccount $subaccount)
    {
        return $this->sendError('Sorry ! Delete function is restricted for data safety', [], 450);
        //
        //        $deleted = $subaccount->delete();
        //        if ($deleted) {
        //            return $this->sendResponse([], 'subaccount successfully removed', 200);
        //        }
    }
}
