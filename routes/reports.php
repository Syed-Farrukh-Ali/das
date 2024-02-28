<?php

use App\Http\Controllers\Api\Auth\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::post('login', 'LoginController@login');

Route::group(['middleware' => ['auth:sanctum', 'verified', 'cors']], function () {
    // Route::post('logout', 'LoginController@logout');

    Route::group(['namespace' => 'App\Http\Controllers\Api\StudentReports'], function () {
        Route::post('print_fee_bill', 'StudentReportController@feeBill');
        Route::post('student_fee_detail', 'StudentReportController@feeDetail');
        Route::post('student_check_list', 'StudentCheckListController@checkListReport');
        Route::post('hostel_student_report', 'StudentReportController@hostelStudentReport');
        Route::post('fee_concession_report', 'StudentReportController@feeConcession');
        Route::post('register_staff_list', 'StudentReportController@registerStaffList');
        Route::post('staff_list', 'StudentReportController@staffList');
        Route::post('staff_list_overall', 'StudentReportController@staffListOverall');
        Route::post('demand_pay_sheet', 'StudentReportController@demandPaySheet');
        Route::post('monthly_pay_sheet', 'StudentReportController@monthlyPaySheet');
        Route::post('student_due_fee', 'StudentReportController@studentDueFee');
        Route::post('student_due_fee_print_list', 'StudentReportController@studentDueFeePrint');
        Route::post('student_total_admissions_reports', 'StudentTotalAdmissionsReportController@totalAdmissionReport');
        Route::post('student-fee-progress-report', 'StudentFeeProgressReportController');
        Route::post('student-figure-report', 'StudentFigureController@StudentFigureReport');
        Route::post('student-strength-report', 'StudentStrengthController@report');
        Route::post('student-misc-fee-received-report', 'MiscFeeController@report');
        Route::post('student-package-report', 'StudentPackageController@report');
        Route::post('student-fee-received-month-wise-report', 'FeeReceivedMonthWiseController@report');
        Route::post('student-exam-report', 'ExamReportController@report');
        Route::post('new-students-report', 'NewStudentsListController@index');
    });

    Route::group(['namespace' => 'App\Http\Controllers\Api\StaffReport'], function () {
        Route::post('monthly_pay_summary', 'StaffReportController@monthlyPaySummary');
        Route::post('bank_pay_sheet', 'StaffReportController@bankPaySheet');
        Route::post('employee_salary_detail', 'StaffReportController@empSalaryDetial');
        Route::post('employee_pay_slip', 'StaffReportController@employeePaySlip');
        Route::post('employee_gross_salary', 'StaffReportController@empGrossSalary');
        Route::post('staff-figures-report', 'StaffFiguresReportController@staffFiguresReport');
        Route::post('staff-gp-fund-report', 'StaffGpFundReportController@staffGPFundReport');
        Route::post('staff-eobi-report', 'StaffEOBIReportController@index');
        Route::post('staff-monthly-salary-slip', 'StaffMonthlySalarySlipController@index');
    });

    Route::group(['namespace' => 'App\Http\Controllers\Api\AccountReports'], function () {
        Route::post('daily_scroll', 'AccountReportsController@dailyScroll');
        Route::post('bank_daily_scroll', 'AccountReportsController@bankDailyScroll');
        Route::post('print_voucher_list_date_wise', 'AccountReportsController@vouchersDateWise');
        Route::post('projected_monthly_Income_report', 'AccountReportsController@projectedIncome');
        Route::post('chart_of_account', 'AccountReportsController@chartOfAccount');
        Route::post('class_wise_fee_summary', 'AccountReportsController@classWiseFeeSummary');
        Route::post('income_and_expenditure_report', 'AccountReportsController@incomeAndExpenditure');
        Route::post('transaction_reports', 'AccountReportsController@transactionReports');
        Route::post('account_ledger_report', 'AccountReportsController@accountLedgerReport');
        Route::post('daily_scroll_report', 'AccountReportsController@dailyScrollReport');
        Route::post('daily_scroll_received_wise', 'AccountReportsController@dailyScrollReceived');
        Route::post('profit_and_loss_report', 'ProfitAndLossController');
        Route::post('trial_balance_report', 'TrialBalanceController@report');
        Route::post('income_expenditure_statement', 'IncomeExpenditureStatementController@report');
        Route::post('comparative_expense_report', 'ComparativeExpenseReportController@report');
        Route::post('cash_and_bank_balance_report', 'CashAndBankBalanceReportController@report');
        Route::post('date_wise_monthly_expense_summary_report', 'DateWiseMonthlyExpenseSummaryController@report');
        Route::post('monthly-pay-sheet', 'MonthlyPaySheetController@index');
        Route::post('monthly-pay-sheet-details', 'MonthlyPaySheetController@payDetails');
        Route::post('projected-monthly-income-report', 'ProjectedMonthlyIncome@MonthlyIncomeReport');
        Route::post('monthly_fee_break_report', 'ProjectedMonthlyIncome@MonthlyFeeBreakReport');
    });

    Route::group(['namespace' => 'App\Http\Controllers\Api\SMS'], function () {
        Route::get('/sms-log-report', 'SmsLogReportController@index');
        Route::post('/date-wise-sms-log-report', 'SmsLogReportController@dateWiseLogReport');
    });
});
