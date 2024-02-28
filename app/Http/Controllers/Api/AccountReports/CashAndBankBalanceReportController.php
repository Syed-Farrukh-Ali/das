<?php

namespace App\Http\Controllers\Api\AccountReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Account\CashAndBankBalanceReportRequest;
use App\Models\AccountChart;
use App\Models\BankAccount;
use App\Models\GeneralLedger;
use App\Models\SubAccount;

class CashAndBankBalanceReportController extends BaseController
{
    public function report(CashAndBankBalanceReportRequest $request)
    {
        $data = [
            'bank' => $this->getData(25,$request->bank_account_id),
            'cash' => $this->getData(26,$request->bank_account_id),
        ];
        return $this->sendResponse($data, '',200);
    }

    public function getData($account_chart_id, $bank_account_id)
    {

        $account_chart = AccountChart::find($account_chart_id);

        $account_chart_data = [
            'account_chart_code' => $account_chart->acode,
            'account_chart' => $account_chart->title,
            'account_data' => [],
        ];

        if ($account_chart_id == 25)
        {
            $bank_accounts = BankAccount::when($bank_account_id, function ($query) use ($bank_account_id) {
                return $query->where('id',$bank_account_id);
            })->get();
        } else {
            $bank_accounts = BankAccount::where('bank_account_category_id',1)
            ->when($bank_account_id, function ($query) use ($bank_account_id) {
                return $query->where('id',$bank_account_id);
            })->get();
        }

        foreach ($bank_accounts as $bank_account)
        {
            $credit = $bank_account->sub_account->general_ledgers()->sum('credit');
            $debit = $bank_account->sub_account->general_ledgers()->sum('debit');

            if ($credit == 0 && $debit == 0)
                continue;

            $bank_account_data = [
                'account_code' => $bank_account->sub_account->acode,
                'account_name' => $bank_account->bank_name,
                'credit' => $credit,
                'debit' => $debit,
            ];

            $account_chart_data['account_data'][] = $bank_account_data;
        }

        return $account_chart_data;
    }
}
