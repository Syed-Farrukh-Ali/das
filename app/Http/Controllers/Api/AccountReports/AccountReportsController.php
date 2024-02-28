<?php

namespace App\Http\Controllers\Api\AccountReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Accounts\AccountGroupResource;
use App\Http\Resources\Accounts\GeneralLedgerResource;
use App\Http\Resources\Accounts\GL\GLGeneralLedgerResource;
use App\Http\Resources\Accounts\SubAccountResource;
use App\Http\Resources\Accounts\VoucherResource;
use App\Http\Resources\FeeChallanResourceCopy;
use App\Models\AccountChart;
use App\Models\AccountGroup;
use App\Models\BankAccount;
use App\Models\CampusClass;
use App\Models\FeeChallan;
use App\Models\GeneralLedger;
use App\Models\Hostel;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\SubAccount;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AccountReportsController extends BaseController
{
    public function dailyScroll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|date_format:Y-m-d',
            'sub_account_id' => 'required_without:bank_account_id',
            'bank_account_id' => 'required_without:sub_account_id',
            'campus_id' => 'nullable|exists:campuses,id',
        ]);
        $campus_id = $request->campus_id;
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
        if ($request->bank_account_id) {
            $bank_account_ids = [$request->bank_account_id];
        } elseif ($request->sub_account_id) {
            $bank_account_ids = BankAccount::where('sub_account_id', $request->sub_account_id)->pluck('id');
        }
        $challan = FeeChallan::where('received_date', $request->date)->whereIn('bank_account_id', $bank_account_ids)
        ->where(function ($query) use ($campus_id) {
            return $campus_id != null ? $query->where('campus_id', $campus_id) : '';
        })
        ->get();
        $challan->load('student.studentClass', 'student.globalSection', 'feeChallanDetails');
        $data = [
            'challans' => FeeChallanResourceCopy::collection($challan),
        ];

        return $this->sendResponse($data, 'daily scroll', 200);
    }

    public function bankDailyScroll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|date_format:Y-m-d',
            'sub_account_id' => 'required|exists:sub_accounts,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
        $date = Carbon::createFromFormat('Y-m-d', $request->date);

        $subAccount = SubAccount::find($request->sub_account_id);
        $gl = $subAccount->general_ledgers()->where(['credit' => 0])
        ->whereMonth('transaction_at', $date->month)
        ->whereYear('transaction_at', $date->year)->get();
        $data = [
            'account_head' => new SubAccountResource($subAccount),
            'transactions' => GeneralLedgerResource::collection($gl),
        ];

        return $this->sendResponse($data, [], 200);
    }

    public function vouchersDateWise(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date|date_format:Y-m-d',
            'to_date' => 'required|date|date_format:Y-m-d',
            'year_id' => 'required|exists:sessions,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
        $vouchers = Voucher::whereBetween('date', [$request->from_date, $request->to_date])->get();
        $vouchers->load('general_ledgers', 'voucher_type');
        $data = [
            'vouchers' => VoucherResource::collection($vouchers),
        ];

        return $this->sendResponse($data, '', 200);
    }

    public function projectedIncome(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campus_id' => 'required|exists:campuses,id',
            'year_id' => 'required|exists:sessions,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }

        $student_all_cls = Student::where([
            'campus_id' => $request->campus_id,
            'status' => 2,
            'session_id' => $request->year_id,
        ])->get();
        $monthly_fee_all_cls = $student_all_cls->pluck('studentLiableFees')->flatten()->where('fees_type_id', 4)->sum('amount');
        $hostel_fee_all_cls = $student_all_cls->pluck('studentLiableFees')->flatten()->where('fees_type_id', 7)->sum('amount');

        $studentClasses = StudentClass::find(CampusClass::where('campus_id', $request->campus_id)->pluck('student_class_id'));
        foreach ($studentClasses as $key => $studentClass) {
            $student = Student::where([
                'campus_id' => $request->campus_id,
                'status' => 2,
                'session_id' => $request->year_id,
                'student_class_id' => $studentClass->id,
            ])->get();
            $monthly_fees = $student->pluck('studentLiableFees')->flatten()->where('fees_type_id', 4)->sum('amount');
            $hostel_fee = $student->pluck('studentLiableFees')->flatten()->where('fees_type_id', 7)->sum('amount');
            $numberOfStudent = $student->count();
            $numberOfStudentInHostel = $student->where('hostel_id', '>', 0)->count();

            $avg_fee = 0;
            if ($numberOfStudent > 0 and $numberOfStudent > 0) {
                $avg_fee = round($monthly_fees / $numberOfStudent);
            }

            $class_fee_details[$key] = [
                'name' => $studentClass->name,
                'monthly_fee' => $monthly_fees,
                'student_inclass' => $numberOfStudent,
                'avg_fee' => $avg_fee,
                'hostel_fee' => $hostel_fee,
                'student_inhostel' => $numberOfStudentInHostel,
            ];
        }

        $data = [
            'class_fee_details' => $class_fee_details,
            'campus_total_montly_fee' => $monthly_fee_all_cls,
            'campus_total_hostel_fee' => $hostel_fee_all_cls,
        ];

        return $this->sendResponse($data, [], 200);

        return $class_fee_details;
    }

    public function chartOfAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'main_head' => 'required_without:detail_account',
            'detail_account' => 'required_without:main_head',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }

        if ($request->main_head) {
            // code...
            $account_group = AccountGroup::with('account_charts')->get();
        } elseif ($request->detail_account) {
            $account_group = AccountGroup::with('account_charts.sub_accounts')->get();
        }

        $data = [
            'account_group' => AccountGroupResource::collection($account_group),
        ];

        return $this->sendResponse($data, [], 200);
    }

    public function classWiseFeeSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_id' => 'required|exists:sessions,id',
            'campus_id' => 'nullable|exists:campuses,id',
            'from_date' => 'required|date_format:Y-m-d',
            'to_date' => 'required|date_format:Y-m-d',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }

        $campus_class_ids = CampusClass::when($request->campus_id, function ($query) use ($request) {
            return $query->where('campus_id',$request->campus_id);
        })->pluck('student_class_id')->unique()->toArray();

        $studentClasses = StudentClass::whereIn('id', $campus_class_ids)->get();

        $students = Student::when($request->campus_id, function ($query) use ($request) {
            return $query->where('campus_id',$request->campus_id);
        })->get();
        $fee_challans = FeeChallan::whereBetween('received_date', [$request->from_date, $request->to_date])
        ->where('status', 2)->when($request->campus_id, function ($query) use ($request) {
                return $query->where('campus_id',$request->campus_id);
            })->get();

        foreach ($studentClasses as $key => $studentClass) {
            $cls_student_ids = $students->where('student_class_id', $studentClass->id)->pluck('id');

            $fee_challan_detail = $fee_challans->whereIn('student_id', $cls_student_ids)->pluck('feeChallanDetails')->flatten();

            $fee_summary[$key] = [
                'name' => $studentClass->name,
                'admission_fee' => $fee1 = $fee_challan_detail->where('fees_type_id', 3)->sum('amount'),
                'annual_fund' => $fee2 = $fee_challan_detail->where('fees_type_id', 5)->sum('amount'),
                'duplicate_fee_bill_charges' => $fee3 = $fee_challan_detail->where('fees_type_id', 27)->sum('amount'),
                'hostel_fee' => $fee4 = $fee_challan_detail->where('fees_type_id', 7)->sum('amount'),
                'monthly_fee' => $fee5 = $fee_challan_detail->where('fees_type_id', 4)->sum('amount'),
                'others_fine' => $fee6 = $fee_challan_detail->where('fees_type_id', 8)->sum('amount'),
                'prospectus' => $fee7 = $fee_challan_detail->where('fees_type_id', 1)->sum('amount'),
                're_admission_fee' => $fee8 = $fee_challan_detail->where('fees_type_id', 9)->sum('amount'),
                'registration' => $fee10 = $fee_challan_detail->where('fees_type_id', 2)->sum('amount'),
                'second_shift_study' => $fee11 = $fee_challan_detail->where('fees_type_id', 12)->sum('amount'),
                'sports_charges' => $fee12 = $fee_challan_detail->where('fees_type_id', 21)->sum('amount'),
                'total' => $fee1 + $fee2 + $fee3 + $fee4 + $fee5 + $fee6 + $fee7 + $fee8 + $fee10 + $fee11 + $fee12,
            ];
        }
        $data = [
            'fee_summary' => $fee_summary,
        ];

        return $this->sendResponse($data, [], 200);
    }

    public function incomeAndExpenditure(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_id' => 'required|exists:sessions,id',
            'campus_id' => 'nullable|exists:campuses,id',
            'from_date' => 'required|date_format:Y-m-d',
            'to_date' => 'required|date_format:Y-m-d',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
        //_._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._.

        $accountgroup = AccountGroup::where('acode', 42)->get()->first();
        $accountchart = AccountChart::where('account_group_id', $accountgroup->id)->get();
        $accountchart_ids = $accountchart->pluck('id');
        $amount_fee = GeneralLedger::whereIn('account_chart_id', $accountchart_ids)
             ->where('session_id', $request->year_id)->whereBetween('transaction_at', [$request->from_date, $request->to_date])
             ->sum('credit');

        $totol_fee = [
            'acode' => 24,
            'account_name' => $accountgroup->name,
            'amount' => $amount_fee,
        ];

        foreach ($accountchart as $key => $accountchart) {
            $fees_array[$key] = [
                'acode' => $accountchart->acode,
                'account_name' => $accountchart->title,
                'total_amount' => '',
                'sub_account' => [],
            ];
            $subAccounts = SubAccount::where('account_chart_id', $accountchart->id)->get();
            $sum = 0;
            foreach ($subAccounts as $sub_key => $subAccount) {
                array_push($fees_array[$key]['sub_account'], [
                    'acode' => $subAccount->acode,
                    'account_name' => $subAccount->title,
                    'amount' => $sum += GeneralLedger::where('sub_account_id', $subAccount->id)
                    ->where('session_id', $request->year_id)->whereBetween('transaction_at', [$request->from_date, $request->to_date])
                    ->sum('credit'),
                ]);
            }
            $fees_array[$key]['total_amount'] = $sum;
        }
        //_._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._.
        $accountgroup = AccountGroup::where('acode', 43)->get()->first();
        $accountchart = AccountChart::where('account_group_id', $accountgroup->id)->get();
        $accountchart_ids = $accountchart->pluck('id');
        $amount_fee = GeneralLedger::whereIn('account_chart_id', $accountchart_ids)
        ->where('session_id', $request->year_id)->whereBetween('transaction_at', [$request->from_date, $request->to_date])
        ->sum('credit');

        $total_other_receipts = [
            'acode' => 24,
            'account_name' => $accountgroup->name,
            'amount' => $amount_fee,
        ];

        foreach ($accountchart as $key => $accountchart) {
            $other_receipts_detail[$key] = [
                'acode' => $accountchart->acode,
                'account_name' => $accountchart->title,
                'total_amount' => '',
                'sub_account' => [],
            ];
            $subAccounts = SubAccount::where('account_chart_id', $accountchart->id)->get();
            $sum = 0;
            foreach ($subAccounts as $sub_key => $subAccount) {
                array_push($other_receipts_detail[$key]['sub_account'], [
                    'acode' => $subAccount->acode,
                    'account_name' => $subAccount->title,
                    'amount' => $sum += GeneralLedger::where('sub_account_id', $subAccount->id)
                    ->where('session_id', $request->year_id)->whereBetween('transaction_at', [$request->from_date, $request->to_date])
                    ->sum('credit'),
                ]);
            }
            $other_receipts_detail[$key]['total_amount'] = $sum;
        }
        //_._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._.
        $accountgroup = AccountGroup::where('acode', 51)->get()->first();
        $accountchart = AccountChart::where('account_group_id', $accountgroup->id)->get();
        $accountchart_ids = $accountchart->pluck('id');
        $amount_fee = GeneralLedger::whereIn('account_chart_id', $accountchart_ids)
        ->where('session_id', $request->year_id)->whereBetween('transaction_at', [$request->from_date, $request->to_date])
        ->sum('credit');

        $total_school_expense = [
            'acode' => 24,
            'account_name' => $accountgroup->name,
            'amount' => $amount_fee,
        ];

        foreach ($accountchart as $key => $accountchart) {
            $school_expense_detail[$key] = [
                'acode' => $accountchart->acode,
                'account_name' => $accountchart->title,
                'total_amount' => '',
                'sub_account' => [],
            ];
            $subAccounts = SubAccount::where('account_chart_id', $accountchart->id)->get();
            $sum = 0;
            foreach ($subAccounts as $sub_key => $subAccount) {
                array_push($school_expense_detail[$key]['sub_account'], [
                    'acode' => $subAccount->acode,
                    'account_name' => $subAccount->title,
                    'amount' => $sum += GeneralLedger::where('sub_account_id', $subAccount->id)
                    ->where('session_id', $request->year_id)->whereBetween('transaction_at', [$request->from_date, $request->to_date])
                    ->sum('credit'),
                ]);
            }
            $school_expense_detail[$key]['total_amount'] = $sum;
        }
        //_._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._.
        $accountgroup = AccountGroup::where('acode', 51)->get()->first();
        $accountchart = AccountChart::where('account_group_id', $accountgroup->id)->get();
        $accountchart_ids = $accountchart->pluck('id');
        $amount_fee = GeneralLedger::whereIn('account_chart_id', $accountchart_ids)
        ->where('session_id', $request->year_id)->whereBetween('transaction_at', [$request->from_date, $request->to_date])
        ->sum('credit');

        $total_hostel_expense = [
            'acode' => 24,
            'account_name' => $accountgroup->name,
            'amount' => $amount_fee,
        ];

        foreach ($accountchart as $key => $accountchart) {
            $hostel_expense_detail[$key] = [
                'acode' => $accountchart->acode,
                'account_name' => $accountchart->title,
                'total_amount' => '',
                'sub_account' => [],
            ];
            $subAccounts = SubAccount::where('account_chart_id', $accountchart->id)->get();
            $sum = 0;
            foreach ($subAccounts as $sub_key => $subAccount) {
                array_push($hostel_expense_detail[$key]['sub_account'], [
                    'acode' => $subAccount->acode,
                    'account_name' => $subAccount->title,
                    'amount' => $sum += GeneralLedger::where('sub_account_id', $subAccount->id)
                    ->where('session_id', $request->year_id)->whereBetween('transaction_at', [$request->from_date, $request->to_date])
                    ->sum('credit'),
                ]);
            }
            $hostel_expense_detail[$key]['total_amount'] = $sum;
        }
        //_._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._.

        $data = [
            'total_fees' => $totol_fee,
            'fee_details' => $fees_array,

            'total_other_receipts' => $total_other_receipts,
            'other_receipts_detail' => $other_receipts_detail,

            'total_school_expense' => $total_school_expense,
            'school_expense_detail' => $school_expense_detail,

            'total_hostel_expense' => $total_hostel_expense,
            'hostel_expense_detail' => $hostel_expense_detail,

            'total_revenue' => $totol_fee['amount'] + $total_other_receipts['amount'],
            'total_expense' => $total_school_expense['amount'] + $total_hostel_expense['amount'],
        ];

        return $this->sendResponse($data, [], 200);
    }

    public function transactionReports(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_id' => 'required|exists:sessions,id',
            'date' => 'required|date_format:Y-m-d',
            'daily' => 'nullable|boolean',
            'monthly' => 'nullable|boolean',
            'annually' => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }

        $account_groups = AccountGroup::all();

        $month = Carbon::createFromFormat('Y-m-d', $request->date)->month;
        $year = Carbon::createFromFormat('Y-m-d', $request->date)->year;

        $vocher_ids = Voucher::where('session_id',$request->year_id)
            ->when($request->daily, function ($query) use ($request) {
                return $query->where('date', $request->date);
            })
            ->when($request->monthly, function ($query) use ($month) {
                return $query->whereMonth('date', $month);
            })
            ->when($request->annually, function ($query) use ($year) {
                return $query->whereYear('date', $year);
            })
            ->pluck('id')->unique()->toArray();

        $data = [];

        foreach ($account_groups as $account_group)
        {
            $account_chart_ids = $account_group->account_charts->pluck('id');

            $credit = GeneralLedger::whereIn('account_chart_id', $account_chart_ids)
                ->where('session_id', $request->year_id)
                ->whereIn('voucher_id',$vocher_ids)
                ->sum('credit');
            $debit = GeneralLedger::whereIn('account_chart_id', $account_chart_ids)
                ->where('session_id', $request->year_id)
                ->whereIn('voucher_id',$vocher_ids)
                ->sum('debit');

            $account_group_data = [
                'account_name' => $account_group->title,
                'acode' => $account_group->acode,
                'debit' => $debit,
                'credit' => $credit,
                'account_charts' => [],
            ];

            foreach ($account_group->account_charts as  $account)
            {
                $sub_accounts_details = [
                    'account_name' => $account->title,
                    'acode' => $account->acode,
                    'sub_accounts' => [],
                ];

                $debit_sum = 0;
                $credit_sum = 0;

                foreach ($account->sub_accounts as $subAccount)
                {
                    $debit_sa = GeneralLedger::where('sub_account_id', $subAccount->id)
                        ->where('session_id', $request->year_id)
                        ->whereIn('voucher_id',$vocher_ids)
                        ->sum('debit');
                    $credit_sa = GeneralLedger::where('sub_account_id', $subAccount->id)
                        ->where('session_id', $request->year_id)
                        ->whereIn('voucher_id',$vocher_ids)
                        ->sum('credit');

                    if ($debit_sa == 0 and $credit_sa == 0) {
                        continue;
                    }

                    $sub_accounts_details['sub_accounts'][] = [
                        'acode' => $subAccount->acode,
                        'account_name' => $subAccount->title,
                        'debit' => $debit_sa,
                        'credit' => $credit_sa,
                    ];

                    $debit_sum += $debit_sa;
                    $credit_sum += $credit_sa;
                }
                $sub_accounts_details['total_debit'] = $debit_sum;
                $sub_accounts_details['total_credit'] = $credit_sum;

                $account_group_data['account_charts'][] = $sub_accounts_details;
            }

            $data[] = $account_group_data;
        }

        return $this->sendResponse($data, [], 200);
    }

    public function accountLedgerReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_id' => 'required|exists:sessions,id',
            'sub_account_id' => 'required|exists:sub_accounts,id',
            'from' => 'required|date_format:Y-m-d',
            'to' => 'required|date_format:Y-m-d',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }

        $vocher_ids = Voucher::where('session_id',$request->year_id)
            ->whereDate('date','>=',$request->from)
            ->whereDate('date','<=',$request->to)
            ->pluck('id')->unique()->toArray();

        $vocher_ids_for_balance = Voucher::whereDate('date','<',$request->from)
            ->pluck('id')->unique()->toArray();

        # ._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._._.
        $subAccount = SubAccount::find($request->sub_account_id);

        $debit = GeneralLedger::where('sub_account_id',$subAccount->id)
            ->where('session_id',$request->year_id)
            ->whereIn('voucher_id',$vocher_ids_for_balance)
            ->sum('debit');
        $credit = GeneralLedger::where('sub_account_id',$subAccount->id)
            ->where('session_id',$request->year_id)
            ->whereIn('voucher_id',$vocher_ids_for_balance)
            ->sum('credit');

        $final_credit = 0;
        $final_debit = 0;

        if ($credit-$debit > 0){
            $final_credit = $credit-$debit;
        }

        if ($debit-$credit > 0){
            $final_debit = $debit-$credit;
        }
//
//        if ($subAccount->torise_debit) {
//                $openning_balance = $debit - $credit;
//            }else {
//                $openning_balance = $credit - $debit;
//        }

        $gl = GeneralLedger::whereIn('voucher_id',$vocher_ids)
                ->where('sub_account_id',$subAccount->id)
                ->get();

//        $gl_exists = $subAccount->general_ledgers()
//            ->whereDate('transaction_at','>=',$request->from)
//            ->whereDate('transaction_at','<=',$request->to)
//            ->where('session_id',$request->year_id)->exists();
//
//        if($gl_exists)
//        {
//            $gl = $subAccount->general_ledgers()
//                ->whereDate('transaction_at','>=',$request->from)
//                ->whereDate('transaction_at','<=',$request->to)
//                ->where('session_id',$request->year_id)->get();
//        } else {
//            $gl = $subAccount->tempgeneral_ledgers()
//                ->whereDate('transaction_at','>=',$request->from)
//                ->whereDate('transaction_at','<=',$request->to)
//                ->where('session_id',$request->year_id)->get();
//        }

        $gl->load('voucher.voucher_type');
        $transactions = GeneralLedgerResource::collection($gl);

        $sorted_transactions = collect($transactions)->sortBy(function ($transaction) {
            return $transaction['voucher']['date'];
        })->values()->all();

        $data = [
            'openning_balance_credit' => $final_credit,
            'openning_balance_debit' => $final_debit,
            'account_head' => new SubAccountResource($subAccount),
            'transactions' => $sorted_transactions,
        ];

        return $this->sendResponse($data, [], 200);

    }
    public function dailyScrollReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
      $feeChallans =  FeeChallan::with('student:id,name,father_name,admission_id,registration_id,campus_id,student_class_id,global_section_id','bank_account.sub_account','voucher','student.studentClass','student.campus')
      ->when($request->bank_account_id, fn ($query) => $query->where('bank_account_id',$request->bank_account_id))
      ->whereDate('feed_at',$request->date)->orderBy('feed_at')->get();
      $data = [
        'total_amount' => $feeChallans->sum('payable'),
        'feeChallans' => $feeChallans,
      ];
      return $this->sendResponse($data,'daily scroll',200);
    }
    public function dailyScrollReceived(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
      $feeChallans =  FeeChallan::with('student:id,name,father_name,admission_id,registration_id,campus_id,student_class_id,global_section_id','bank_account.sub_account','voucher','student.globalSection','student.studentClass','student.campus')
      ->when($request->bank_account_id, fn ($query) => $query->where('bank_account_id',$request->bank_account_id))
      ->whereDate('received_date',$request->date)->orderBy('received_date','asc')->get();
      $data = [
        'total_amount' => $feeChallans->sum('payable'),
        'feeChallans' => $feeChallans,
      ];
      return $this->sendResponse($data,'daily scroll',200);
    }

}
