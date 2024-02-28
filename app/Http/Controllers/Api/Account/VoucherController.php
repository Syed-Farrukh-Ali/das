<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Accounts\VoucherResource;
use App\Http\Resources\FeeChallanResource;
use App\Http\Resources\SalaryResource;
use App\Jobs\SendMessageJob;
use App\Models\AccountChart;
use App\Models\BankAccount;
use App\Models\Campus;
use App\Models\EmployeeSalary;
use App\Models\FeeChallan;
use App\Models\FeeReturn;
use App\Models\GeneralLedger;
use App\Models\Session;
use App\Models\Setting;
use App\Models\Student;
use App\Models\SubAccount;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\Foreach_;

class VoucherController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_id' => 'required|integer|min:1|max:6',
            'date' => 'required|date|date_format:Y-m-d',
            'voucher_type_id' => 'required|integer|exists:voucher_types,id',
            'remarks' => 'nullable|string|max:255',
            'check_no' => 'nullable|string|max:25',
            'campus_id.*' => 'nullable|integer|exists:campuses,id',

            'contra_sub_account_id' => 'nullable|exists:sub_accounts,id',
            'contra_debit' => 'nullable|min:0|max:900000000',
            'contra_credit' => 'nullable|min:0|max:900000000',

            'target_sub_account_id.*' => 'nullable|exists:sub_accounts,id',
            'target_credit.*' => 'required|min:0|max:900000000',
            'target_debit.*' => ['required', 'min:0', 'max:900000000'],
            'target_remarks.*' => ['nullable', 'string', 'max:255'],

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $latest_voucher_date = Voucher::orderBy('date', 'DESC')->value('date');
        if ($request->date < $latest_voucher_date) {
            return $this->sendError('Invalid Voucher date', [], 422);
        }


        if ($request->voucher_type_id < 5) {
            $this->otherVoucherValidate();
        } else {
            $this->journalVoucherValidate();
        }

        //        $campus_name = Campus::find($request->campus_id)->name;
        //        $CSA = SubAccount::find($request->contra_sub_account_id);
        DB::beginTransaction();

        try {
            $voucher = Voucher::create([
                'date' => $request->date,
                'voucher_type_id' => $request->voucher_type_id,
                'resolved' => false,
                'session_id' => $request->year_id,
                'check_no' => $request->check_no,
                // 'campus_id' => $request->campus_id,
            ]);
            if ($request->voucher_type_id < 5) {
                // in case of journal it will not run, 5 is voucher type of JV
                $voucher->general_ledgers()->create([
                    'transaction_at' => $request->date,
                    'sub_account_id' => $request->contra_sub_account_id,
                    'account_chart_id' => SubAccount::find($request->contra_sub_account_id)->account_chart_id,
                    'debit' => $request->contra_debit,
                    'credit' => $request->contra_credit,
                    'remarks' => $request->remarks,
                    // 'campus_id' => $request->campus_id,
                    'session_id' => $request->year_id,
                ]);
            }
            foreach ($request->target_sub_account_id as $key => $target_sub_account_id) {
                $TSA = SubAccount::find($target_sub_account_id);
                $voucher->general_ledgers()->create([
                    'transaction_at' => $request->date,
                    'sub_account_id' => $target_sub_account_id,
                    'account_chart_id' => SubAccount::find($target_sub_account_id)->account_chart_id,
                    'debit' => $request->target_debit[$key],
                    'credit' => $request->target_credit[$key],
                    'remarks' => $request->target_remarks[$key],
                    'campus_id' => $request->campus_id[$key],
                    'session_id' => $request->year_id,
                ]);
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->sendError('some thing went wrong', [], 500);
        }
        $total_debit = $voucher->general_ledgers()->sum('debit');
        $total_credit = $voucher->general_ledgers()->sum('credit');
        $voucher->update(['total_debit' => $total_debit, 'total_credit' => $total_credit]);

        if ($total_debit != $total_credit) {
            DB::rollBack();

            return $this->sendError('debit credit not equal,something wrong with data', ['Dr' => $total_debit, 'Cr' => $total_credit], 422);
        }
        DB::commit();

        // return $this->sendResponse(new VoucherResource($voucher->load('general_ledgers')),[], 200);
        return $this->sendResponse(new VoucherResource($voucher->load('general_ledgers.sub_account', 'general_ledgers.campus', 'session', 'voucher_type', 'campus')), [], 200);
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     * @return type
     * @throws conditon
     **/
    public function update(Request $request, Voucher $voucher)
    {
        if ($voucher->resolved) {
            return $this->sendError('This voucher cannot be edit', [], 422);
        } else {
            $validator = Validator::make($request->all(), [
                'year_id' => 'required|integer|min:1|max:6',
                'date' => 'required|date|date_format:Y-m-d',
                'voucher_type_id' => 'required|integer|exists:voucher_types,id',
                'remarks' => 'nullable|string|max:255',
                'check_no' => 'nullable|string|max:25',
                'campus_id.*' => 'nullable|integer|exists:campuses,id',

                'contra_sub_account_id' => 'nullable|exists:sub_accounts,id',
                'contra_debit' => 'nullable|min:0|max:900000000',
                'contra_credit' => 'nullable|min:0|max:900000000',

                'target_sub_account_id.*' => 'nullable|exists:sub_accounts,id',
                'target_credit.*' => 'required|min:0|max:900000000',
                'target_debit.*' => ['required', 'min:0', 'max:900000000'],
                'target_remarks.*' => ['nullable', 'string', 'max:255'],

            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->errors(), [], 422);
            }
            if ($request->voucher_type_id < 5) {
                $this->otherVoucherValidate();
            } else {
                $this->journalVoucherValidate();
            }

            //            $campus_name = Campus::find($request->campus_id)->name;
            //            $CSA = SubAccount::find($request->contra_sub_account_id);
            DB::beginTransaction();

            try {
                $voucher->update([
                    'date' => $request->date,
                    'voucher_type_id' => $request->voucher_type_id,
                    'resolved' => false,
                    'session_id' => $request->year_id,
                    'check_no' => $request->check_no,
                    // 'campus_id' => $request->campus_id,
                ]);

                $voucher->general_ledgers()->delete();
                if ($request->voucher_type_id < 5) {
                    // in case of journal it will not run, 5 is voucher type of JV
                    $voucher->general_ledgers()->create([
                        'transaction_at' => $request->date,
                        'sub_account_id' => $request->contra_sub_account_id,
                        'account_chart_id' => SubAccount::find($request->contra_sub_account_id)->account_chart_id,
                        'debit' => $request->contra_debit,
                        'credit' => $request->contra_credit,
                        'remarks' => $request->remarks,
                        //   'campus_id' => $request->campus_id,
                        'session_id' => $request->year_id,
                    ]);
                }
                foreach ($request->target_sub_account_id as $key => $target_sub_account_id) {
                    $TSA = SubAccount::find($target_sub_account_id);
                    $voucher->general_ledgers()->create([
                        'transaction_at' => $request->date,
                        'sub_account_id' => $target_sub_account_id,
                        'account_chart_id' => SubAccount::find($target_sub_account_id)->account_chart_id,
                        'debit' => $request->target_debit[$key],
                        'credit' => $request->target_credit[$key],
                        'remarks' => $request->target_remarks[$key],
                        'campus_id' => $request->campus_id[$key],
                        'session_id' => $request->year_id,
                    ]);
                }
            } catch (\Throwable $e) {
                DB::rollBack();
                return $this->sendError('some thing went wrong', [], 500);
            }
            $total_debit = $voucher->general_ledgers()->sum('debit');
            $total_credit = $voucher->general_ledgers()->sum('credit');
            $voucher->update(['total_debit' => $total_debit, 'total_credit' => $total_credit]);

            if ($total_debit != $total_credit) {
                DB::rollBack();

                return $this->sendError('debit credit not equal,something wrong with data', ['Dr' => $total_debit, 'Cr' => $total_credit], 422);
            }
            DB::commit();

            return $this->sendResponse(new VoucherResource($voucher->load('general_ledgers', 'campus')), [], 200);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    protected function journalVoucherValidate()
    {
        if (array_sum(request()->target_debit) != array_sum(request()->target_debit)) {
            return $this->sendError('the transaction is not balanced', [], 422);
        }
    }

    protected function otherVoucherValidate()
    {
        if (array_sum(request()->target_debit) != request()->contra_credit) {
            return $this->sendError('the transaction is not balanced', [], 422);
        }
        if (array_sum(request()->target_credit) != request()->contra_debit) {
            return $this->sendError('the transaction is not balanced', [], 422);
        }
        if (request()->contra_debit > 0 and request()->contra_credit > 0) {
            return $this->sendError(' One of contra debit or credit should be zero', [], 422);
        }
        if (array_sum(request()->target_debit) > 0 and array_sum(request()->target_credit) > 0) {
            return $this->sendError('One of target debit or credit should be zero', [], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function show(Voucher $voucher)
    {
        $voucher->load('voucher_type', 'session', 'general_ledgers.sub_account', 'campus');

        return $this->sendResponse(new VoucherResource($voucher), [], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function filter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date' => 'nullable|date|date_format:Y-m-d',
            'voucher_type_id' => 'nullable|integer|min:1|max:6',
            'voucher_no' => 'nullable|integer|min:1|max:100000000',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $latest_voucher_date = Voucher::orderBy('date', 'DESC')->value('date');
        Voucher::query()->whereDate('date', '<', $latest_voucher_date)->update(['resolved' => true]);

        $vouchers = $this->searchable(
            $request->voucher_no,
            $request->start_date,
            $request->end_date,
            $request->voucher_type_id,
        );
        $vouchers->load('general_ledgers.sub_account', 'general_ledgers.campus', 'session', 'voucher_type', 'campus');

        return $this->sendResponse(VoucherResource::collection($vouchers)->resource, [], 200);
    }

    public function voucherSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'voucher_id' => 'required|exists:vouchers,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $voucher = Voucher::with('general_ledgers.sub_account', 'session', 'voucher_type', 'campus')->find($request->voucher_id);
        $general_ledgers = $voucher->general_ledgers->groupBy('sub_account_id');

        $summary_general_ledger = [];

        $voucher->total_debit = number_format((float)$voucher->total_debit, 2, '.', '');
        $voucher->total_credit = number_format((float)$voucher->total_credit, 2, '.', '');

        foreach ($general_ledgers as $group) {
            $object = [
                "voucher_id" => $group[0]->voucher_id,
                "sub_account_id" => $group[0]->sub_account_id,
                "session_id" => $group[0]->session_id,
                "campus_id" => $group[0]->campus_id,
                "transaction_at" => $group[0]->transaction_at,
                "debit" =>  $group->sum('debit'),
                "credit" => $group->sum('credit'),
                "sub_account" =>  $group[0]->sub_account,
            ];
            $summary_general_ledger[] = $object;
        }

        $voucher->summary_general_ledger  = $summary_general_ledger;

        return $this->sendResponse($voucher, [], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    protected function searchable(
        $v_no = null,
        $start_date = null,
        $end_date = null,
        $v_type_id = null,
        $campus_id = null
    ) {
        if ($v_no) {
            return Voucher::where('voucher_no', 'like', "%-$v_no")
                ->when($v_type_id, fn ($query) => $query->where('voucher_type_id', $v_type_id))
                ->get();
        } else {
            $query = Voucher::when($v_type_id, fn ($query) => $query->where('voucher_type_id', $v_type_id))
                ->when($start_date, fn ($query) => $query->whereDate('date', '>=', $start_date))
                ->when($end_date, fn ($query) => $query->whereDate('date', '<=', $end_date))
                ->when($campus_id, fn ($query) => $query->where('campus_id', $campus_id))
                ->latest()
                ->paginate(10);

            return $query;
        }
    }

    public function submittedChallans(Request $request) //return challans that only submitted not posted to gl and status is 1
    {
        $date = Carbon::now()->subDays(100);

        $feeChallan = FeeChallan::where(['status' => 1])->where('updated_at', '>=', $date)->get();
        $feeChallan->load('feeChallanDetails');
        // return $this->sendResponse(FeeChallanResource::collection($feeChallan), [], 200);

        $result = [
            'feeChallan' => FeeChallanResource::collection($feeChallan),
            'total_challan_amount' => $feeChallan->where('parent_id', null)->sum('payable'),

        ];

        return $this->sendResponse($result, []);
    }

    public function challansToVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fee_challan_ids.*' => 'nullable|exists:fee_challans,id',
            'date' => 'required|date|date_format:Y-m-d',
            'year_id' => 'required|integer|exists:sessions,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $salaries = EmployeeSalary::where('status', 1)->get();
        // $submittedFeechallans =FeeChallan::where(['status' => 1])->get();
        $submittedFeechallans = FeeChallan::where(['status' => 1])->get();
        $pendingVouchers = Voucher::where('resolved', false)->get();
        $staff_child_sub_account = SubAccount::where('acode', 43020003)->first();

        if ($salaries->isEmpty() and $submittedFeechallans->isEmpty()) {
            return $this->sendError('no fee challan , no salary for dn voucher, no voucher', [], 422);
        }

        // // $aabbcc =  $aabbcc->take(331);
        // // return $aabbcc->last()->load('feeChallanDetails');
        // # code...
        // // dd($aabbcc->sum('paid'),$aabbcc->sum('payable'), $aabbcc->pluck('feeChallanDetails')-                     >flatten()->sum('amount'));
        // // $check = 0;
        // //     foreach ($aabbcc as $key => $challan) {
        // //         $payable  = $challan->payable;
        // //         $detail_amount = $challan->feeChallanDetails->sum('amount');
        // //         $challan->load('feeChallanDetails');
        // //         if ($payable !== $detail_amount) {
        // //             return $challan->feeChallanDetails;
        // //         }
        // //         $check++;
        // //     }


        // dd('not found', $check);

        DB::beginTransaction();
        try {
            //_._._._._._._._._._._._._._._._._._._._._._._._.__._
            $voucher = Voucher::create([
                'date' => $request->date,
                'voucher_type_id' => 6,
                'session_id' => $request->year_id,
            ]);
            //-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-

            //`-.-`-.-`-`.-`-.-`-.-`-.-`-.-`-.-`-.-`-.-`-.-`-.-`-.` Salaries voucher entry start
            $salaries = EmployeeSalary::where('status', 1)->get();
            if ($salaries->isNotEmpty()) {
                $salaries->toQuery()->update(['voucher_id' => $voucher->id]);
                $bank_account_ids = $salaries->pluck('bank_account_id')->unique()->toArray();
                $bank_accounts = BankAccount::with(['sub_account.account_chart', 'bank_account_category'])->whereIn('id', $bank_account_ids)->get();
                $all_campus_ids = $salaries->pluck('campus_id')->unique();

                foreach ($bank_accounts as $key => $bank_account) {

                    $salary_amounts = $salaries->toQuery()->where(['bank_account_id' => $bank_account->id])->sum('net_pay');

                    if ($salary_amounts) {

                        $voucher->general_ledgers()->create([
                            'sub_account_id' => $bank_account->sub_account->id,
                            'transaction_at' => $request->date,
                            //  'campus_id' => $campus_id, bank account wise data that's why campus id not available
                            'account_chart_id' => $bank_account->sub_account->account_chart_id,
                            'debit' => 0,
                            'credit' => $salary_amounts,
                            'remarks' => $this->getBankName($bank_account->bank_name) . ' ' . $bank_account->account_title . ' Day End' . ' Date:' . $request->date,
                            'session_id' => $request->year_id,

                        ]);
                    }
                }

                $school_salaries_chart_id = AccountChart::where('acode', 5101)->value('id');
                $hostel_salaries_chart_id = AccountChart::where('acode', 5301)->value('id');

                foreach ($all_campus_ids as $key => $campus_id) {

                    $campus = Campus::find($campus_id);

                    if ($campus->type == 'campus') {
                        $salaries_chart_id = $school_salaries_chart_id;
                        $account_head_code = '5101';
                    } else {
                        $salaries_chart_id = $hostel_salaries_chart_id;
                        $account_head_code = '5301';
                    }

                    $cmps_basic = $salaries->where('campus_id', $campus_id)->sum('basic_pay');
                    if ($cmps_basic) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => SubAccount::where('acode', $account_head_code . '0001')->value('id'), // basic pay 51-01-0001
                            'account_chart_id' => $salaries_chart_id,
                            'debit' => $cmps_basic,
                            'credit' => 0,
                            'remarks' => "Basic Pay \n Day End." . '(' . $this->campus_name($campus_id) . ')',
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }

                    ////////////////////////////////////////
                    $cmps_hifz = $salaries->where('campus_id', $campus_id)->sum('hifz');
                    if ($cmps_hifz) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => SubAccount::where('acode', $account_head_code . '0002')->value('id'), // cmps_hifz 51-01-0002
                            'account_chart_id' => $salaries_chart_id,
                            'debit' => $cmps_hifz,
                            'credit' => 0,
                            'remarks' => "Hifz Allowance  \n Day End." . '(' . $this->campus_name($campus_id) . ')',
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }
                    ////////////////////////////////////
                    $cmps_hostel = $salaries->where('campus_id', $campus_id)->sum('hostel');
                    if ($cmps_hostel) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => SubAccount::where('acode', $account_head_code . '0003')->value('id'), // cmps_hostel 51-01-0002
                            'account_chart_id' => $salaries_chart_id,
                            'debit' => $cmps_hostel,
                            'credit' => 0,
                            'remarks' => "Hostel Allowance  \n Day End." . '(' . $this->campus_name($campus_id) . ')',
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }
                    ////////////////////////////////////////
                    $cmps_college = $salaries->where('campus_id', $campus_id)->sum('college');
                    if ($cmps_college) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => SubAccount::where('acode', $account_head_code . '0004')->value('id'), // cmps_college 51-01-0002
                            'account_chart_id' => $salaries_chart_id,
                            'debit' => $cmps_college,
                            'credit' => 0,
                            'remarks' => "College Allowance  \n Day End." . '(' . $this->campus_name($campus_id) . ')',
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }
                    //////////////////////////////////////////////
                    $cmps_additional_allowance = $salaries->where('campus_id', $campus_id)->sum('additional_allowance');
                    if ($cmps_additional_allowance) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => SubAccount::where('acode', $account_head_code . '0005')->value('id'), // cmps_additional_allowance 51-01-0002
                            'account_chart_id' => $salaries_chart_id,
                            'debit' => $cmps_additional_allowance,
                            'credit' => 0,
                            'remarks' => "Additional Allowance  \n Day End." . '(' . $this->campus_name($campus_id) . ')',
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }
                    /////////////////////////////
                    $cmps_increment = $salaries->where('campus_id', $campus_id)->sum('increment');
                    if ($cmps_increment) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => SubAccount::where('acode', $account_head_code . '0006')->value('id'), // cmps_increment
                            'account_chart_id' => $salaries_chart_id,
                            'debit' => $cmps_increment,
                            'credit' => 0,
                            'remarks' => "Increment Allowance  \n Day End." . '(' . $this->campus_name($campus_id) . ')',
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }
                    //////////////////////////////
                    $cmps_second_shift = $salaries->where('campus_id', $campus_id)->sum('second_shift');
                    if ($cmps_second_shift) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => SubAccount::where('acode', $account_head_code . '0007')->value('id'), // cmps_second_shift
                            'account_chart_id' => $salaries_chart_id,
                            'debit' => $cmps_second_shift,
                            'credit' => 0,
                            'remarks' => "Second Shift Allowance  \n Day End." . '(' . $this->campus_name($campus_id) . ')',
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }
                    //////////////////////////////
                    $qualification_allowance = $salaries->where('campus_id', $campus_id)->sum('ugs');
                    if ($qualification_allowance) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => SubAccount::where('acode', $account_head_code . '0008')->value('id'),
                            'account_chart_id' => $salaries_chart_id,
                            'debit' => $qualification_allowance,
                            'credit' => 0,
                            'remarks' => "Qualification Allowance  \n Day End." . '(' . $this->campus_name($campus_id) . ')',
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }
                    //////////////////////////////
                    $cmps_other_allowance = $salaries->where('campus_id', $campus_id)->sum('other_allowance');
                    if ($cmps_other_allowance) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => SubAccount::where('acode', $account_head_code . '0009')->value('id'), // cmps_other_allowance
                            'account_chart_id' => $salaries_chart_id,
                            'debit' => $cmps_other_allowance,
                            'credit' => 0,
                            'remarks' => "Other Allowance  \n Day End." . '(' . $this->campus_name($campus_id) . ')',
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }
                    //////////////////////////////
                    $cmps_hod = $salaries->where('campus_id', $campus_id)->sum('hod');
                    if ($cmps_hod) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => SubAccount::where('acode', $account_head_code . '0010')->value('id'), // cmps_hod
                            'account_chart_id' => $salaries_chart_id,
                            'debit' => $cmps_hod,
                            'credit' => 0,
                            'remarks' => "HOD Allowance  \n Day End." . '(' . $this->campus_name($campus_id) . ')',
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }
                    //////////////////////////////
                    $cmps_science = $salaries->where('campus_id', $campus_id)->sum('science');
                    if ($cmps_science) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => SubAccount::where('acode', $account_head_code . '0011')->value('id'), // cmps_science
                            'account_chart_id' => $salaries_chart_id,
                            'debit' => $cmps_science,
                            'credit' => 0,
                            'remarks' => "Science Allowance  \n Day End." . '(' . $this->campus_name($campus_id) . ')',
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }
                    $cmps_eobi_payments = $salaries->where('campus_id', $campus_id)->sum('eobi_payments');
                    if ($cmps_eobi_payments) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => SubAccount::where('acode', $account_head_code . '0013')->value('id'), // cmps_eobi_payments
                            'account_chart_id' => $salaries_chart_id,
                            'debit' => $cmps_eobi_payments,
                            'credit' => 0,
                            'remarks' => "EOBI Fund  \n Day End." . '(' . $this->campus_name($campus_id) . ')',
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }
                    //////////////////////////////
                    $cmps_extra_period = $salaries->where('campus_id', $campus_id)->sum('extra_period');
                    if ($cmps_extra_period) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => SubAccount::where('acode', $account_head_code . '0014')->value('id'), // cmps_extra_period
                            'account_chart_id' => $salaries_chart_id,
                            'debit' => $cmps_extra_period,
                            'credit' => 0,
                            'remarks' => "Extra Period Allowance  \n Day End." . '(' . $this->campus_name($campus_id) . ')',
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }
                    //////////////////////////////
                    $cmps_extra_coaching = $salaries->where('campus_id', $campus_id)->sum('extra_coaching');
                    if ($cmps_extra_coaching) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => SubAccount::where('acode', $account_head_code . '0015')->value('id'), // cmps_extra_coaching
                            'account_chart_id' => $salaries_chart_id,
                            'debit' => $cmps_extra_coaching,
                            'credit' => 0,
                            'remarks' => "Extra Coaching Allowance  \n Day End." . '(' . $this->campus_name($campus_id) . ')',
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }

                    //////////////////////////////
                    $convince_allownce = $salaries->where('campus_id', $campus_id)->sum('convance');
                    if ($convince_allownce) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => SubAccount::where('acode', $account_head_code . '0017')->value('id'),
                            'account_chart_id' => $salaries_chart_id,
                            'debit' => $convince_allownce,
                            'credit' => 0,
                            'remarks' => "Convince Allowance  \n Day End." . '(' . $this->campus_name($campus_id) . ')',
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }
                    //////////////////////////////

                    $employee_funds_chart_id = AccountChart::where('acode', 2103)->value('id');
                    ///////////////////////////////
                    $cmps_gpf_return = $salaries->where('campus_id', $campus_id)->sum('gpf_return');
                    if ($cmps_gpf_return) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => SubAccount::where('acode', 21030001)->value('id'), // cmps_gp_fund
                            'account_chart_id' => $employee_funds_chart_id,
                            'debit' => $cmps_gpf_return,
                            'credit' => 0,
                            'remarks' => "GP Fund  \n Day End." . '(' . $this->campus_name($campus_id) . ')',
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }
                    /////////////////////////////
                    $cmps_gp_fund = $salaries->where('campus_id', $campus_id)->sum('gp_fund');
                    if ($cmps_gp_fund) {
                        $gp_fund_account = SubAccount::where('acode', 21030001)->first();
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => $gp_fund_account->id, // cmps_gp_fund
                            'account_chart_id' => $employee_funds_chart_id,
                            'debit' => 0,
                            'credit' => $cmps_gp_fund,
                            'remarks' => $this->campus_name($campus_id) . " " . $gp_fund_account->title . " Day End. Deductions From Salary . (" . _getUnitName() . ")",
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }
                    //////////////////////////////
                    $cmps_welfare_fund = $salaries->where('campus_id', $campus_id)->sum('welfare_fund');
                    if ($cmps_welfare_fund) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => $campus->welfare_account_id, // cmps_welfare_fund
                            'account_chart_id' => SubAccount::find($campus->welfare_account_id)->account_chart_id,
                            'debit' => 0,
                            'credit' => $cmps_welfare_fund,
                            'remarks' => $this->campus_name($campus_id) . " Welfare Fund \n Day End. Deductions From Salary . (" . _getUnitName() . ")",
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }
                    //////////////////////////////
                    /////////////////// DEDUCTIONS
                    $cmps_eobi = $salaries->where('campus_id', $campus_id)->sum('eobi');
                    if ($cmps_eobi) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => SubAccount::where('acode', 21030003)->value('id'), // cmps_eobi
                            'account_chart_id' => $employee_funds_chart_id,
                            'debit' => 0,
                            'credit' => $cmps_eobi,
                            'remarks' => $this->campus_name($campus_id) . " EOBI \n Day End. Deductions From Salary . (" . _getUnitName() . ")",
                            'campus_id' => $campus_id,
                            'session_id' => $request->year_id,
                        ]);
                    }
                    //////////////////////////////
                    //                    $cmps_van_charge = $salaries->where('campus_id', $campus_id)->sum('van_charge');
                    //                    if ($cmps_van_charge) {
                    //                        $voucher->general_ledgers()->create([
                    //                            'transaction_at' => $request->date,
                    //                            'sub_account_id' => SubAccount::where('acode',21030004)->value('id'), // cmps_van_charge
                    //                            'account_chart_id' => $employee_funds_chart_id,
                    //                            'debit' => 0,
                    //                            'credit' => $cmps_van_charge,
                    //                            'remarks' => $this->campus_name($campus_id)." Van Charges \n Day End. Deductions From Salary . (Dar-E-Arqam Schools)",
                    //                            'campus_id' => $campus_id,
                    //                            'session_id' => $request->year_id,
                    //                        ]);
                    //                    }
                    //                    //////////////////////////////
                    //                    $cmps_other_deduction = $salaries->where('campus_id', $campus_id)->sum('other_deduction');
                    //                    if ($cmps_other_deduction) {
                    //                        $voucher->general_ledgers()->create([
                    //                            'transaction_at' => $request->date,
                    //                            'sub_account_id' => SubAccount::where('acode',21030006)->value('id'), // cmps_other_deduction
                    //                            'account_chart_id' => $employee_funds_chart_id,
                    //                            'debit' => 0,
                    //                            'credit' => $cmps_other_deduction,
                    //                            'remarks' => $this->campus_name($campus_id)." Other Deduction \n Day End. Deductions From Salary . (Dar-E-Arqam Schools)",
                    //                            'campus_id' => $campus_id,
                    //                            'session_id' => $request->year_id,
                    //                        ]);
                    //                    }
                    //////////////////////////////
                    //child_fee_deduction will be later on work on.
                    //................................................
                    //                    $cmps_child_fee_deduction = $salaries->where('campus_id', $campus_id)->sum('child_fee_deduction');
                    //                    if ($cmps_child_fee_deduction) {
                    //                        $sub_account = SubAccount::where('acode', 43020003)->first(); //Staff Child fee deduction Account
                    //                        $voucher->general_ledgers()->create([
                    //                            'transaction_at' => $request->date,
                    //                            'sub_account_id' => $sub_account->id, // staff child fee deduction account
                    //                            'account_chart_id' => $sub_account->account_chart_id,
                    //                            'debit' => $cmps_child_fee_deduction,
                    //                            'credit' => 0,
                    //                            'remarks' => "Staff Child Fee Deduction Account \n (Day End) Child Fee Deduction From Salary" . $this->campus_name($campus_id),
                    //                            'campus_id' => $campus_id,
                    //                            'session_id' => $request->year_id,
                    //                        ]);
                    //                    }
                    //................................................

                    //////////////////////////////
                    $cmps_income_tax = $salaries->where('campus_id', $campus_id)->sum('income_tax');
                    if ($cmps_income_tax) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => SubAccount::where('acode', 22080001)->value('id'), // cmps_income_tax
                            'account_chart_id' => AccountChart::where('acode', 2208)->value('id'),
                            'debit' => 0,
                            'credit' => $cmps_income_tax,
                            'remarks' => "Income Tax Payable (Deduction from Employees) \n Day End. Deductions From Salary . (" . _getUnitName() . ")",
                            'session_id' => $request->year_id,
                        ]);
                    }
                }

                foreach ($salaries as $key => $salary) {

                    $campus = $salary->employee->campus;

                    if ($campus->type == 'campus') {
                        $account_head_code = '5101';
                    } else {
                        $account_head_code = '5301';
                    }

                    if ($salary->loan_refund > 0) {

                        $loan = $salary->employee->loans->first();
                        if ($loan) {
                            $sub_account = $loan->subAccount;

                            $voucher->general_ledgers()->create([
                                'transaction_at' => $request->date,
                                'sub_account_id' => $sub_account->id, // cmps_income_tax
                                'account_chart_id' => $sub_account->account_chart->id,
                                'debit' => 0,
                                'credit' => $salary->loan_refund,
                                'remarks' => $sub_account->title . "  \n Loan Installment Paid. Empcode=" . $salary->employee->emp_code . "\n  (" . _getUnitName() . ")",
                                'session_id' => $request->year_id,
                            ]);
                        }
                    }

                    if ($salary->child_fee_deduction != 0) {
                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => $staff_child_sub_account->id,
                            'account_chart_id' => $staff_child_sub_account->account_chart->id,
                            'debit' => 0,
                            'credit' => $salary->child_fee_deduction,
                            'remarks' => 'Staff Child Fee Deduction Account (Day End) Child Fee Deduction From Salary.Empcode=' . $salary->employee->emp_code . ' ' . $salary->employee->full_name . '( ' . $salary->employee->campus->name . ')',
                            'session_id' => $request->year_id,
                        ]);
                    }

                    if ($salary->other_deduction > 0) {

                        $other_deduction_account = SubAccount::where('acode', $account_head_code . '0001')->first();

                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => $other_deduction_account->id,
                            'account_chart_id' => $other_deduction_account->account_chart->id,
                            'debit' => 0,
                            'credit' => $salary->other_deduction,
                            'remarks' => $other_deduction_account->title . ' Other Deduction From Salary. Empcode=' . $salary->employee->emp_code . ' ' . $salary->employee->full_name . '( ' . $salary->employee->campus->name . ')',
                            'session_id' => $request->year_id,
                        ]);
                    }

                    if ($salary->van_charge > 0) {

                        $van_charge_account = SubAccount::where('acode', $account_head_code . '0001')->first();

                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => $van_charge_account->id,
                            'account_chart_id' => $van_charge_account->account_chart->id,
                            'debit' => 0,
                            'credit' => $salary->van_charge,
                            'remarks' => $van_charge_account->title . ' Van Charges Deduction From Salary. Empcode=' . $salary->employee->emp_code . ' ' . $salary->employee->full_name . '( ' . $salary->employee->campus->name . ')',
                            'session_id' => $request->year_id,
                        ]);
                    }
                    if ($salary->insurance > 0) {

                        $insurance_charge_account = SubAccount::where('acode', $account_head_code . '0001')->first();

                        $voucher->general_ledgers()->create([
                            'transaction_at' => $request->date,
                            'sub_account_id' => $insurance_charge_account->id,
                            'account_chart_id' => $insurance_charge_account->account_chart->id,
                            'debit' => 0,
                            'credit' => $salary->insurance,
                            'remarks' => $insurance_charge_account->title . ' Insurance Deduction From Salary. Empcode=' . $salary->employee->emp_code . ' ' . $salary->employee->full_name . '( ' . $salary->employee->campus->name . ')',
                            'session_id' => $request->year_id,
                        ]);
                    }
                }
                //////////////////////////////
            }

            //-.-`-.-`-`.-`-.-`-.-`-.-`-.-`-.-`-.-`-.-`-.-`-.-`- Salaries voucher ends

            //.............................................fee challan voucher entry started

            if ($submittedFeechallans->isNotEmpty()) {
                // code...

                $feeChallans = $submittedFeechallans;
                $bank_account_ids = $feeChallans->pluck('bank_account_id')->unique()->toArray();
                $bank_accounts = BankAccount::with('sub_account.account_chart')->whereIn('id', $bank_account_ids)->get();
                $campus_ids = $feeChallans->pluck('campus_id')->unique();
                $bank_id_arrays = [];
                $bank_sub_account_id_array = $bank_accounts->pluck('sub_account_id')->unique()->toArray();
                foreach ($bank_sub_account_id_array as $key => $bank_sub_account_id) {
                    $bank_id_arrays[$key]['bank_ids'] = $bank_accounts->where('sub_account_id', $bank_sub_account_id)->pluck('id')->toArray();
                }

                foreach ($bank_id_arrays as $key => $bank_id_array) {
                    // $challan_this_bank_only = $feeChallans->where('bank_account_id',$bank_account->id)->whereNull('parent_id')->get();
                    $received_dates = $feeChallans->whereIn('bank_account_id', $bank_id_array['bank_ids'])->pluck('received_date')->unique()->toArray();

                    foreach ($received_dates as $key => $received_date) {
                        // dd($bank_account->id,$received_date,$feeChallans->where(['received_date'=>$received_date,'bank_account_id'=>$bank_account->id])->pluck('campus_id'));
                        $campus_ids = $feeChallans->toQuery()->whereIn('bank_account_id', $bank_id_array['bank_ids'])->where(['received_date' => $received_date, 'parent_id' => null])->pluck('campus_id')->unique();

                        // if ($bank_account->id = 3 and $received_date = '2022-03-23') {
                        //     # code...
                        //     dd($feeChallans->toQuery()->where(['bank_account_id'=> $bank_account->id,'received_date'=>$received_date])->whereNull('parent_id')->get()->pluck('campus_id')->unique()->toArray(),$campus_ids,$bank_account->id,$received_date);
                        // }

                        $challan_amount = $feeChallans->toQuery()->whereIn('bank_account_id', $bank_id_array['bank_ids'])->where(['received_date' => $received_date, 'parent_id' => null])->sum('paid');
                        // code...
                        $bank_account = BankAccount::find($bank_id_array['bank_ids'][0]);
                        if ($challan_amount) {
                            $voucher->general_ledgers()->create([
                                'sub_account_id' => $bank_account->sub_account->id,
                                'transaction_at' => $received_date,
                                //  'campus_id' => $campus_id, bank account wise data that's why campus id not available
                                'account_chart_id' => $bank_account->sub_account->account_chart_id,
                                'debit' => $challan_amount,
                                'credit' => 0,
                                'remarks' => $this->getBankName($bank_account->bank_name) . ' ' . $bank_account->account_title . ' ' . 'Date:' . $received_date,
                                'session_id' => $request->year_id,

                            ]);
                        }
                    }

                    // $challan_total_paid_to_bank = $feeChallans->where('bank_account_id', $bank_account->id)->whereNull('parent_id')->sum('paid');

                    // $voucher->general_ledgers()->create([
                    //     'sub_account_id' => $bank_account->sub_account->id,
                    //     'account_chart_id' => $bank_account->sub_account->account_chart_id,
                    //     'debit'            => $challan_total_paid_to_bank,
                    //     'credit'           => 0,
                    //     'remarks'          => $bank_account->bank_name . ' ' . $bank_account->account_title . '/' . $bank_account->account_head . '/' . 'Bank Account number:' . $bank_account->account_number,

                    // ]);
                }
                $received_dates = $feeChallans->pluck('received_date')->unique()->toArray();

                foreach ($received_dates as $key => $received_date) {
                    $campus_ids = $feeChallans->where('received_date', $received_date)->pluck('campus_id')->unique();
                    foreach ($campus_ids as $key => $campus_id) {
                        $sum_amount_of_fees_for_campus = $feeChallans->where('campus_id', $campus_id)->where('received_date', $received_date)
                            ->pluck('feeChallanDetails')->collapse()
                            ->where('fees_type_id', 1)->sum('amount');

                        $account_chart_id = AccountChart::where('acode', 4201)->value('id');

                        if ($sum_amount_of_fees_for_campus) {
                            $voucher->general_ledgers()->create([
                                'transaction_at' => $received_date,
                                'sub_account_id' => SubAccount::where('acode', 42010001)->value('id'), // prospectus fee acode:42-01-0001
                                'account_chart_id' => $account_chart_id,
                                'debit' => 0,
                                'credit' => $sum_amount_of_fees_for_campus,
                                'remarks' => "Prospectus Fees  \n Day End. (Fee Income)" . '(' . $this->campus_name($campus_id) . ')',
                                'campus_id' => $campus_id,
                                'session_id' => $request->year_id,

                            ]);
                        }

                        $sum_amount_of_fees_for_campus = $feeChallans->where('campus_id', $campus_id)->where('received_date', $received_date)
                            ->pluck('feeChallanDetails')->collapse()
                            ->where('fees_type_id', 2)->sum('amount');
                        if ($sum_amount_of_fees_for_campus) {
                            $voucher->general_ledgers()->create([
                                'transaction_at' => $received_date,
                                'sub_account_id' => SubAccount::where('acode', 42010002)->value('id'),
                                'account_chart_id' => $account_chart_id,
                                'debit' => 0,
                                'credit' => $sum_amount_of_fees_for_campus,
                                'remarks' => "Registration Fees  \n Day End. (Fee Income)" . '(' . $this->campus_name($campus_id) . ')',
                                'campus_id' => $campus_id,
                                'session_id' => $request->year_id,

                            ]);
                        }

                        $sum_amount_of_fees_for_campus = $feeChallans->where('campus_id', $campus_id)->where('received_date', $received_date)
                            ->pluck('feeChallanDetails')->collapse()
                            ->where('fees_type_id', 3)->sum('amount');
                        if ($sum_amount_of_fees_for_campus) {
                            $voucher->general_ledgers()->create([
                                'transaction_at' => $received_date,
                                'sub_account_id' => SubAccount::where('acode', 42010003)->value('id'),
                                'account_chart_id' => $account_chart_id,
                                'debit' => 0,
                                'credit' => $sum_amount_of_fees_for_campus,
                                'remarks' => "Admission Fee  \n Day End. (Fee Income)" . '(' . $this->campus_name($campus_id) . ')',
                                'campus_id' => $campus_id,
                                'session_id' => $request->year_id,

                            ]);
                        }

                        $sum_amount_of_fees_for_campus = $feeChallans->where('campus_id', $campus_id)->where('received_date', $received_date)
                            ->pluck('feeChallanDetails')->collapse()
                            ->where('fees_type_id', 4)->sum('amount');
                        if ($sum_amount_of_fees_for_campus) {
                            $voucher->general_ledgers()->create([
                                'transaction_at' => $received_date,
                                'sub_account_id' => SubAccount::where('acode', 42010004)->value('id'),
                                'account_chart_id' => $account_chart_id,
                                'debit' => 0,
                                'credit' => $sum_amount_of_fees_for_campus,
                                'remarks' => "Monthly Fees \n Day End. (Fee Income)" . '(' . $this->campus_name($campus_id) . ')',
                                'campus_id' => $campus_id,
                                'session_id' => $request->year_id,

                            ]);
                        }

                        $sum_amount_of_fees_for_campus = $feeChallans->where('campus_id', $campus_id)->where('received_date', $received_date)
                            ->pluck('feeChallanDetails')->collapse()
                            ->where('fees_type_id', 5)->sum('amount');
                        if ($sum_amount_of_fees_for_campus) {
                            $voucher->general_ledgers()->create([
                                'transaction_at' => $received_date,
                                'sub_account_id' => SubAccount::where('acode', 42010005)->value('id'),
                                'account_chart_id' => $account_chart_id,
                                'debit' => 0,
                                'credit' => $sum_amount_of_fees_for_campus,
                                'remarks' => "Annual Funds  \n Day End. (Fee Income)" . '(' . $this->campus_name($campus_id) . ')',
                                'campus_id' => $campus_id,
                                'session_id' => $request->year_id,

                            ]);
                        }

                        $sum_amount_of_fees_for_campus = $feeChallans->where('campus_id', $campus_id)->where('received_date', $received_date)
                            ->pluck('feeChallanDetails')->collapse()
                            ->whereIn('fees_type_id', [8, 13, 14, 15, 16, 17, 18, 19])->sum('amount');
                        if ($sum_amount_of_fees_for_campus) {
                            $voucher->general_ledgers()->create([
                                'transaction_at' => $received_date,
                                'sub_account_id' => SubAccount::where('acode', 42010006)->value('id'),
                                'account_chart_id' => $account_chart_id,
                                'debit' => 0,
                                'credit' => $sum_amount_of_fees_for_campus,
                                'remarks' => "Others Fines  \n Day End. (Fee Income)" . '(' . $this->campus_name($campus_id) . ')',
                                'campus_id' => $campus_id,
                                'session_id' => $request->year_id,

                            ]);
                        }

                        $sum_amount_of_fees_for_campus = $feeChallans->where('campus_id', $campus_id)->where('received_date', $received_date)
                            ->pluck('feeChallanDetails')->collapse()
                            ->where('fees_type_id', 9)->sum('amount');
                        if ($sum_amount_of_fees_for_campus) {
                            $voucher->general_ledgers()->create([
                                'transaction_at' => $received_date,
                                'sub_account_id' => SubAccount::where('acode', 42010007)->value('id'),
                                'account_chart_id' => $account_chart_id,
                                'debit' => 0,
                                'credit' => $sum_amount_of_fees_for_campus,
                                'remarks' => "Re-Admission Fees  \n Day End. (Fee Income)" . '(' . $this->campus_name($campus_id) . ')',
                                'campus_id' => $campus_id,
                                'session_id' => $request->year_id,

                            ]);
                        }

                        $sum_amount_of_fees_for_campus = $feeChallans->where('campus_id', $campus_id)->where('received_date', $received_date)
                            ->pluck('feeChallanDetails')->collapse()
                            ->where('fees_type_id', 20)->sum('amount');
                        if ($sum_amount_of_fees_for_campus) {
                            $voucher->general_ledgers()->create([
                                'transaction_at' => $received_date,
                                'sub_account_id' => SubAccount::where('acode', 42010008)->value('id'),
                                'account_chart_id' => $account_chart_id,
                                'debit' => 0,
                                'credit' => $sum_amount_of_fees_for_campus,
                                'remarks' => "Transport Fees  \n Day End. (Fee Income)" . '(' . $this->campus_name($campus_id) . ')',
                                'campus_id' => $campus_id,
                                'session_id' => $request->year_id,

                            ]);
                        }

                        $sum_amount_of_fees_for_campus = $feeChallans->where('campus_id', $campus_id)->where('received_date', $received_date)
                            ->pluck('feeChallanDetails')->collapse()
                            ->where('fees_type_id', 10)->sum('amount');
                        if ($sum_amount_of_fees_for_campus) {
                            $voucher->general_ledgers()->create([
                                'transaction_at' => $received_date,
                                'sub_account_id' => SubAccount::where('acode', 42010009)->value('id'),
                                'account_chart_id' => $account_chart_id,
                                'debit' => 0,
                                'credit' => $sum_amount_of_fees_for_campus,
                                'remarks' => "Extra Coaching Fees  \n Day End. (Fee Income)" . '(' . $this->campus_name($campus_id) . ')',
                                'campus_id' => $campus_id,
                                'session_id' => $request->year_id,

                            ]);
                        }

                        $sum_amount_of_fees_for_campus = $feeChallans->where('campus_id', $campus_id)->where('received_date', $received_date)
                            ->pluck('feeChallanDetails')->collapse()
                            ->whereIn('fees_type_id', [12, 25, 26, 27, 28, 29, 30, 31])->sum('amount'); //22.10,24
                        if ($sum_amount_of_fees_for_campus) {
                            $voucher->general_ledgers()->create([
                                'transaction_at' => $received_date,
                                'sub_account_id' => SubAccount::where('acode', 42010009)->value('id'),
                                'account_chart_id' => $account_chart_id,
                                'debit' => 0,
                                'credit' => $sum_amount_of_fees_for_campus,
                                'remarks' => "Other Fee  \n Day End. (Fee Income)" . '(' . $this->campus_name($campus_id) . ')',
                                'campus_id' => $campus_id,
                                'session_id' => $request->year_id,

                            ]);
                        }

                        $hostel_chart_id = AccountChart::where('acode', 4203)->value('id');
                        $sum_amount_of_fees_for_campus = $feeChallans->where('campus_id', $campus_id)->where('received_date', $received_date)
                            ->pluck('feeChallanDetails')->collapse()
                            ->where('fees_type_id', 6)->sum('amount');
                        if ($sum_amount_of_fees_for_campus) {
                            $voucher->general_ledgers()->create([
                                'transaction_at' => $received_date,
                                'sub_account_id' => SubAccount::where('acode', 42030001)->value('id'), // Hostel Admission Fee fee acode:42-01-0009
                                'account_chart_id' => $hostel_chart_id,
                                'debit' => 0,
                                'credit' => $sum_amount_of_fees_for_campus,
                                'remarks' => "Hostel Admission Fee  \n Day End. (Fee Income)" . '(' . $this->campus_name($campus_id) . ')',
                                'campus_id' => $campus_id,
                                'session_id' => $request->year_id,

                            ]);
                        }

                        $sum_amount_of_fees_for_campus = $feeChallans->where('campus_id', $campus_id)->where('received_date', $received_date)
                            ->pluck('feeChallanDetails')->collapse()
                            ->where('fees_type_id', 7)->sum('amount');
                        if ($sum_amount_of_fees_for_campus) {
                            $voucher->general_ledgers()->create([
                                'transaction_at' => $received_date,
                                'sub_account_id' => SubAccount::where('acode', 42030002)->value('id'), // Hostel Monthly acode:42-03-0002
                                'account_chart_id' => $hostel_chart_id,
                                'debit' => 0,
                                'credit' => $sum_amount_of_fees_for_campus,
                                'remarks' => "Hostel Monthly Fee  \n Day End. (Fee Income)" . '(' . $this->campus_name($campus_id) . ')',
                                'campus_id' => $campus_id,
                                'session_id' => $request->year_id,

                            ]);
                        }

                        $sum_amount_of_fees_for_campus = $feeChallans->where('campus_id', $campus_id)->where('received_date', $received_date)
                            ->pluck('feeChallanDetails')->collapse()
                            ->where('fees_type_id', 11)->sum('amount');
                        if ($sum_amount_of_fees_for_campus) {
                            $voucher->general_ledgers()->create([
                                'transaction_at' => $received_date,
                                'sub_account_id' => SubAccount::where('acode', 43020002)->value('id'),
                                'account_chart_id' => $hostel_chart_id,
                                'debit' => 0,
                                'credit' => $sum_amount_of_fees_for_campus,
                                'remarks' => "Exam stationary Charges  \n Day End. (Fee Income)" . '(' . $this->campus_name($campus_id) . ')',
                                'campus_id' => $campus_id,
                                'session_id' => $request->year_id,

                            ]);
                        }

                        $sum_amount_of_fees_for_campus = $feeChallans->where('campus_id', $campus_id)->where('received_date', $received_date)
                            ->pluck('feeChallanDetails')->collapse()
                            ->where('fees_type_id', 21)->sum('amount');
                        if ($sum_amount_of_fees_for_campus) {
                            $voucher->general_ledgers()->create([
                                'transaction_at' => $received_date,
                                'sub_account_id' => SubAccount::where('acode', 43020015)->value('id'),
                                'account_chart_id' => $hostel_chart_id,
                                'debit' => 0,
                                'credit' => $sum_amount_of_fees_for_campus,
                                'remarks' => "Sports Charges  \n Day End. (Fee Income)" . '(' . $this->campus_name($campus_id) . ')',
                                'campus_id' => $campus_id,
                                'session_id' => $request->year_id,

                            ]);
                        }
                    } // End Foreach
                }

                FeeChallan::where(['status' => 1])->update(['status' => 2, 'voucher_id' => $voucher->id]);
            } //..................... fee challan entry ended

            //____________________________________FEE return
            $feeReturns = FeeReturn::where('status', 1)->get();
            if ($feeReturns->isNotEmpty()) {
                foreach ($feeReturns as $key => $feeReturn) {
                    $contra = SubAccount::find($feeReturn->sub_account_id);
                    $fee_subAccont = SubAccount::find($feeReturn->fee_sub_account_id);
                    $std = Student::find($feeReturn->student_id);

                    $voucher->general_ledgers()->create([
                        'transaction_at' => $feeReturn->date,
                        'sub_account_id' => $feeReturn->sub_account_id, // bank or cash account asset
                        'account_chart_id' => $contra->account_chart_id,
                        'debit' => 0,
                        'credit' => $feeReturn->fee_return_amount,
                        'remarks' => $contra->title . '(' . $contra->acode . ')' . ' Fee return ' . $std->name . '(' . $std->admission_id . ') (' . $feeReturn->remarks . ')',
                        'campus_id' => $feeReturn->campus_id,
                        'session_id' => $request->year_id,
                    ]);

                    $voucher->general_ledgers()->create([
                        'transaction_at' => $feeReturn->date,
                        'sub_account_id' => $feeReturn->fee_sub_account_id, // fee account head
                        'account_chart_id' => $fee_subAccont->account_chart_id,
                        'debit' => $feeReturn->fee_return_amount,
                        'credit' => 0,
                        'remarks' => $fee_subAccont->title . '(' . $fee_subAccont->acode . ')' . ' Fee return ' . $std->name . '(' . $std->admission_id . ') (' . $feeReturn->remarks . ')',
                        'campus_id' => $feeReturn->campus_id,
                        'session_id' => $request->year_id,
                    ]);
                }
            }
            //____________________________________vouchers
            // $pendingVouchers = Voucher::where('resolved',false)->get();
            //            foreach ($pendingVouchers as $key => $pendingVoucher) {
            //                   $temp_gls = $pendingVoucher->temp_general_ledgers;
            //                   foreach ($temp_gls as $key => $temp_gl) {
            //                    $pendingVoucher->general_ledgers()->create([
            //                        'transaction_at' => $temp_gl->transaction_at,
            //                        'sub_account_id' => $temp_gl->sub_account_id,
            //                        'account_chart_id' => $temp_gl->account_chart_id,
            //                        'debit' => $temp_gl->debit,
            //                        'credit' => $temp_gl->credit,
            //                        'remarks' => $temp_gl->remarks,
            //                        'campus_id' => $temp_gl->campus_id,
            //                        'session_id' => $temp_gl->session_id,
            //                    ]);
            //                   }
            //                   $pendingVoucher->update(['resolved' => true]);
            //            }

            //while preview generate extra DN
            $total_debit = $voucher->general_ledgers()->sum('debit');
            $total_credit = $voucher->general_ledgers()->sum('credit');
            $voucher->update(['total_debit' => $total_debit, 'total_credit' => $total_credit]);

            if ($request->has('preview') and $request->preview == 1) {
                $previewData = $voucher->load('general_ledgers.sub_account', 'session', 'voucher_type');
                $voucher->forceDelete();
                DB::rollBack();

                return $this->sendResponse(new VoucherResource($previewData), 'preview DN voucher', 200);
            }

            if ($total_debit != $total_credit) {
                $voucher->forceDelete();
                DB::rollBack();

                return $this->sendError('Total debit and total credit not equals', [], 500);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return $this->sendError($th->getMessage(), [], 500);
        }


        if ($salaries->isNotEmpty()) {
            // code...
            EmployeeSalary::where('status', 1)->update(['status' => 2]);
        }
        if ($feeReturns->isNotEmpty()) {
            FeeReturn::where('status', 1)
                ->update([
                    'status' => 2,
                    'voucher_id' => $voucher->id,
                ]);
        }

        $voucher->load('general_ledgers.sub_account', 'session', 'voucher_type');

        $this->sendDNMessage();

        return $this->sendResponse(new VoucherResource($voucher), [], 200);
    }

    public function sendDNMessage()
    {
        $date = now()->format('d-m-Y');

        $checkingDate = now()->format('Y-m');

        $active_students = Student::where('status', '2')->pluck('id')->unique();

        // return $this->sendResponse(count($active_students), []);


        $remainingDue = FeeChallan::where('status', '0')->whereIn('student_id', $active_students)->sum('payable');

        $receivedFee = FeeChallan::where('status', '2')->where('received_date', 'LIKE', '%' . $checkingDate . '%')->whereIn('student_id', $active_students)->sum('payable');

        // calculating Banks GL entries

        // $sub_accounts = $account_chart->sub_accounts;
        $sub_accounts = BankAccount::groupby('sub_account_id')->pluck('sub_account_id');

        $active_financial_year = Session::where('active_financial_year', '1')->value('id');

        // return $this->sendResponse($sub_accounts, []);

        $banksMessage = "\n" . 'Balances on Day End is as under: ' . "\n";

        foreach ($sub_accounts as $sub_account) {

            $final_credit = 0;
            $final_debit = 0;

            $credit = GeneralLedger::where('sub_account_id', $sub_account)
                ->where('session_id', $active_financial_year)
                ->sum('credit');

            $debit = GeneralLedger::where('sub_account_id', $sub_account)
                ->where('session_id', $active_financial_year)
                ->sum('debit');


            // return $this->sendResponse($credit, []);

            if ($credit - $debit > 0) {
                $final_credit = $credit - $debit;
            }

            if ($debit - $credit > 0) {
                $final_debit = $debit - $credit;
            }

            if ($final_credit == 0 && $final_debit == 0)
                continue;

            $account_title = BankAccount::where('sub_account_id', $sub_account)->pluck('account_title')->first();

            // return $this->sendResponse($final_debit, []);

            $final_amount = '';
            if ($final_credit)
                $final_amount = $final_credit . " CR";
            else
                $final_amount = $final_debit . " DR";

            $banksMessage .= $account_title . " = " . $final_amount . "\n";
        }

        $message = _getUnitName() . "\n" . 'Dated: ' . $date . ' Remaining Due Fee: ' . $remainingDue . ' Received Fee: ' . $receivedFee . $banksMessage;


        $director_number = Setting::where('id', '1')->value('director_number');
        SendMessageJob::dispatch(5, $director_number, $message);

        return $this->sendResponse("Message Successfully Sent", []);
    }



    public function payedSalaries(Request $request)
    {
        $salaries = EmployeeSalary::with('employee')->where('status', 1)->get();
        // return $this->sendResponse(SalaryResource::collection($salaries),[],200);

        $result = [
            'salaries' => SalaryResource::collection($salaries),
            'total_net_salary' => $salaries->sum('net_pay'),
            'total_gross_salary' => $salaries->sum('gross_salary'),
        ];

        return $this->sendResponse($result, []);
    }
    private function campus_name($id)
    {
        $campus = Campus::find($id);
        return  $campus ? $campus->name : 'deleted campus';
    }

    private function getBankName($bank_account)
    {
        $string = $bank_account;
        $pattern = '/^([A-Z]+)-/';

        if (preg_match($pattern, $string, $matches)) {
            $bank_name = $matches[0];
        } else {
            $bank_name = $bank_account;
        }

        return $bank_name;
    }

    public function destroy(Voucher $voucher)
    {
        if ($voucher->resolved) {
            return $this->sendError('This voucher cannot be deleted', [], 422);
        } else {
            $voucher->general_ledgers()->delete();
            $voucher->delete();

            return $this->sendResponse([], 'Voucher Deleted Successfully', 200);
        }
    }

    // } catch (\Throwable $th) {
    //     dd($th);
    //     DB::rollBack();

    //     return $this->sendError('Something went wrong, internal server error', [], 422);
    // }
    // $total_debit = $voucher->general_ledgers()->sum('debit');
    // $total_credit = $voucher->general_ledgers()->sum('credit');
    // $voucher->update(['total_debit' => $total_debit, 'total_credit' => $total_credit]);

    // if ($total_debit != $total_credit) {
    //     DB::rollBack();
    //     $error_challan = FeeChallan::where('bank_account_id', null)->where('received_date', '!=', null)->get();
    //     $errordata = ['Dr' => $total_debit, 'Cr' => $total_credit, 'diff' => $total_credit - $total_debit, 'challan having error of empty bank' => $error_challan];

    //     return $this->sendError('debit credit not equal,Debit:Credit::' . $total_debit . ':' . $total_credit, $errordata, 422);
    // }
    // if ($salaries->isNotEmpty()) {
    //     // code...
    //     EmployeeSalary::where('status', 1)->update(['status' => 2]);
    // }
    // if ($request->has('preview') and $request->preview == 1) {
    //     $previewData = $voucher->load('general_ledgers.sub_account', 'session', 'voucher_type');
    //     DB::rollBack();

    //     return $this->sendResponse(new VoucherResource($previewData), 'preview DN voucher', 200);
    // }
    // DB::commit();
    // $voucher->load('general_ledgers.sub_account', 'session', 'voucher_type');

    // return $this->sendResponse(new VoucherResource($voucher), [], 200);
}
