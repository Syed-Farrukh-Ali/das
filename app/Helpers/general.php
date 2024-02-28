<?php

use App\Models\BankAccount;
use App\Models\Campus;
use App\Models\Employee;
use App\Models\HighestValue;
use App\Models\Setting;
use App\Models\SMS\SMSLog;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

function _user()
{
    return auth()->user();
}

function _getUnitName()
{
    return Setting::where('id', 1)->value('unit_name');
}

function _studentAdmission(Student $student, $Joining_date)
{
    $student->update(['status' => 2]);


    if ($student->admission_id == null) {
        // if ($student->gender = 'Male')
        //    {
        //      $gender = 'His';
        //        } else{
        //          $gender = 'Her';
        //    }
        $student_gender = $student->gender == 'Male' ? 'His' : 'Her';

        $message = 'Congratulations! ' . $student->name . ' has been admitted to ' . _getUnitName() . ' ('
            //.Campus::find($student->campus_id)->name.'). '.$gender.' admission no is ';
            . Campus::find($student->campus_id)->name . '). ' . $student_gender . ' admission no is ';

        $alpahanumeric = Setting::where('id', 1)->pluck('alphanumeric_adm_no')->first();

        if ($alpahanumeric == 0) {
            HighestValue::get()->first()->increment('admission_id');
            $admission_id = HighestValue::get()->first()->admission_id;
            $student->update(['admission_id' => $admission_id, 'Joining_date' => $Joining_date]);
            _sendSMS(1, $student->mobile_no, $message . $student->admission_id . '.');
        } else {

            HighestValue::get()->first()->increment('admission_id');
            $num = HighestValue::get()->first()->admission_id;
            $admission_id = $student->campus->code . '-' . $num;
            $admission_id = strtoupper($admission_id);
            $student->update(['admission_id' => $admission_id, 'Joining_date' => $Joining_date]);

            _sendSMS(1, $student->mobile_no, $message . $student->admission_id . '.');
        }
    }

    return $student;
}

function _childFeeBankAccount()
{
    $bankAccount = BankAccount::where('account_head', 43020003)->first();
    return $bankAccount;
}

function _loanInstallmentAmountIfAny(Employee $employee)
{
    $loan_refund = 0;
    if ($employee->loans->where('status', 1)->isNotEmpty()) {

        $loan = $employee->loans->where('status', 1)->first();
        $loan_refund = $loan->monthly_loan_installment;
        $subAccount  =  $employee->loans->first()->subAccount;

        $total_debit =  $subAccount->general_ledgers()->sum('debit');
        $total_credit = $subAccount->general_ledgers()->sum('credit');
        $balance = $total_debit - $total_credit;

        if ($loan->monthly_loan_installment >= $balance) {
            $loan_refund = $balance;
        }
        if ($balance < 1) {
            $loan->update(['status' => 0]);
            return $loan_refund = 0;
        }
    }
    return $loan_refund;
}

function normalizeMobileNumber($mobile)
{
    // Remove any non-numeric characters
    $mobile = preg_replace('/[^0-9]/', '', $mobile);

    // Check the length of the mobile number
    $length = strlen($mobile);

    // If the number starts with "0", replace it with "92"
    if ($length == 11 && $mobile[0] == '0') {
        $mobile = '92' . substr($mobile, 1);
    } elseif ($length == 10 && substr($mobile, 0, 2) != '92') {
        $mobile = '92' . $mobile;
    }

    return $mobile;
}


function _sendSMS($sms_typ_id, $mobile, $message)
{
    $messageValue = Setting::where('id', 1)->value('send_message');

    if (!$messageValue)
        return;


    $mobile_number = normalizeMobileNumber($mobile);

    $current_date = date('Y-m-d H:i:s');

    $user = Auth::user();

    // $api_login_id = Config::get('app.sms_api_login_id');
    // $api_login_password = Config::get('app.sms_api_login_password');

    $api_login_id = Setting::where('id', 1)->value('sms_api_login');
    $api_login_password = Setting::where('id', 1)->value('sms_api_password');

    if (!$api_login_id || !$api_login_password)
        return;

    $data = [
        'loginId' => $api_login_id,
        'loginPassword' => $api_login_password,
        'Destination' => $mobile_number,
        'Mask' => 'DAR-E-ARQAM',
        'Message' => $message,
        'UniCode' => 0,
        'ShortCodePrefered' => 'n',
    ];

    $url = "https://cbs.zong.com.pk/reachrestapi/home/SendQuickSMS";



    try {

        $response = Http::post($url, $data);

        if ($response->successful()) {
            return SMSLog::create([
                'sms_type_id' => $sms_typ_id,
                'date_time' => $current_date,
                'user' => $user->getRoleNames(),
                'number' => $mobile,
                'message' => $message,
            ]);
        } else {
            return "Message not sent";
        }
    } catch (Exception $e) {
        return "Exception: " . $e->getMessage();
    }
}

function _campusAccess($campus_id): bool
{
    $user = Auth::user();

    if ($user->campus && !$user->isAdmin() && !$user->head_office) {
        if ($campus_id != $user->campus->id) {
            return false;
        } else {
            return true;
        }
    } else {
        return true;
    }
}

function _campusId()
{
    $user = _user();

    $campus_id = null;

    if ($user->campus_id) {
        $campus_id = $user->campus_id;
    }

    return $campus_id;
}
