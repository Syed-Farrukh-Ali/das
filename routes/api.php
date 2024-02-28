<?php
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\DomainController;
use App\Http\Controllers\Api\FeesController;
use App\Http\Controllers\Api\Hostel\HostelManagementController;
use App\Http\Controllers\Api\Notification\NotificationsController;
use App\Http\Controllers\Api\Notification\NotificationTypesController;
use App\Http\Controllers\Api\PartialFeeReceiveController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\SMS\EmployeeCustomSMSController;
use App\Http\Controllers\Api\SMS\StudentCustomSMSController;
use App\Http\Controllers\Api\SMS\SmsLogReportController;
use App\Http\Controllers\Api\SMS\SmsTypesController;
use App\Http\Controllers\Api\SMS\StudentDueFeeSMS;
use App\Http\Controllers\Api\SMS\StudentResultSMSController;
use App\Http\Controllers\Api\StaffReport\LoanReportController;
use App\Http\Controllers\Api\StaffReport\StaffPayDetailReportController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\StudentReports\ConcessionReportController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\GraphController;
use App\Http\Controllers\Api\HostelStudentsController;
use App\Http\Controllers\Api\RollBackController;
use App\Http\Controllers\Api\SessionWiseFeeReport;
use App\Http\Controllers\Api\SessionWiseFeeReportController;
use App\Http\Controllers\Api\StaffReport\StaffGrossSalaryReportController;
use App\Http\Controllers\Api\StudentMissingInformationListController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Api\AttendanceSummaryController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\FirmWiseReportController;
use App\Http\Controllers\Api\GPFundReturn;
use App\Http\Controllers\Api\LateFeeFinesController;
use App\Http\Controllers\Api\StudentFeeInformationController;
use App\Http\Controllers\Api\StudentFeeStatusController;
use App\Http\Controllers\Api\YearEndController;
use App\Http\Controllers\Api\MessageManagementController;
use Illuminate\Support\Facades\Route;

Route::get('testing', [MessageManagementController::class, 'index']);
Route::post('update-head-office-messages', [MessageManagementController::class, 'update_head_office_messages']);
Route::get('get-head-office-messages', [MessageManagementController::class, 'get_head_office_messages']);
Route::post('create-unit', [MessageManagementController::class, 'create_new_unit']);
Route::post('update-unit-messages', [MessageManagementController::class, 'update_unit_messages']);
Route::get('get-unit-messages', [MessageManagementController::class, 'get_unit_messages']);
// Route::get('updatefine', 'App\Http\Controllers\Api\latefeefines@updatefine');
// Route::get('showfine', 'App\Http\Controllers\Api\latefeefines@showfine');
// Route::get('closing', 'App\Http\Controllers\Api\gbfund_return@closingYearView');
// Route::POST('gbs', 'App\Http\Controllers\Api\gbfund_return@gbfund_return');
// Route::POST('gp_return', 'App\Http\Controllers\Api\gbfund_return@genrate_return');
// Route::get('serial_id', 'App\Http\Controllers\Api\FeesChallanVoucherController@serial_id');
// Route::POST('view_certificate', 'App\Http\Controllers\Api\FeesChallanVoucherController@viewCerificate');
// Route::get('add_certificate1', 'App\Http\Controllers\Api\FeesChallanVoucherController@add_certificate1');
// Route::get('add_certificate', 'App\Http\Controllers\Api\FeesChallanVoucherController@add_certificate');
// Route::get('predndetails', 'App\Http\Controllers\Api\FeesChallanVoucherController@showAllChallans');
// Route::get('monthlyAttendanceDetails', 'App\Http\Controllers\Api\StudentsFeeReportsController@monthlyAttendanceDetails');
// Route::get('monthlyAttendance1', 'App\Http\Controllers\Api\StudentsFeeReportsController@monthlyAttendance1');
// Route::get('monthlyAttendance', 'App\Http\Controllers\Api\StudentsFeeReportsController@monthlyAttendance');
// Route::get('monthlysalary', 'App\Http\Controllers\Api\StudentsFeeReportsController@monthlysalary');
// Route::get('monthlysalary1', 'App\Http\Controllers\Api\StudentsFeeReportsController@monthlysalary1');
// Route::get('monthlysalary2', 'App\Http\Controllers\Api\StudentsFeeReportsController@monthlysalary2');
// Route::get('monthlysalary3', 'App\Http\Controllers\Api\StudentsFeeReportsController@monthlysalary3');
// Route::get('astrt', 'App\Http\Controllers\Api\StudentsFeeReportsController@allstn');
// Route::get('achart', 'App\Http\Controllers\Api\StudentsFeeReportsController@charts');
// Route::get('xfexs', 'App\Http\Controllers\Api\StudentsFeeReportsController@fex');
// Route::get('xfexs1', 'App\Http\Controllers\Api\StudentsFeeReportsController@fex1');
// Route::get('xfexs2', 'App\Http\Controllers\Api\StudentsFeeReportsController@fex2');
// Route::get('stnrpt', 'App\Http\Controllers\Api\StudentsFeeReportsController@stnrpt');
// Route::get('xxreport', 'App\Http\Controllers\Api\StudentsFeeReportsController@xxreports');
// Route::get('xxfeeReports', 'App\Http\Controllers\Api\StudentsFeeReportsController@xxfeeReports');
// Route::get('countstn', 'App\Http\Controllers\Api\HostelStudentsController@studentcount');

// //Route::post('sessionwise-fee-report', [HostelStudentsController::class, 'sessionWiseFeeReport']);

// Route::get('feepadx', 'App\Http\Controllers\Api\HostelStudentsController@custom_fee');
// Route::post('allstn', 'App\Http\Controllers\Api\HostelStudentsController@allhostelstudents');
// Route::get('allcampuses', 'App\Http\Controllers\Api\HostelStudentsController@allCampus');
// Route::get('allsessions', 'App\Http\Controllers\Api\HostelStudentsController@allsession');
// Route::get('allclasses', 'App\Http\Controllers\Api\HostelStudentsController@allClasses');
// Route::get('allclasses1', 'App\Http\Controllers\Api\HostelStudentsController@allClasses1');
// Route::get('cls', 'App\Http\Controllers\Api\HostelStudentsController@classSec');
// Route::get('challan', 'App\Http\Controllers\Api\HostelStudentsController@challans');
// Route::get('srchchallan', 'App\Http\Controllers\Api\HostelStudentsController@challansrch');
// //Route::get('challansrchinvoice', 'App\Http\Controllers\Api\HostelStudentsController@challansrchinvoice');
// //Route::get('challansrchreg', 'App\Http\Controllers\Api\HostelStudentsController@challansrchreg');
// Route::get('srchchallan1', 'App\Http\Controllers\Api\HostelStudentsController@challansrch1');
// Route::get('srchchallan2', 'App\Http\Controllers\Api\HostelStudentsController@challansrch2');
// Route::get('srchchallan3', 'App\Http\Controllers\Api\HostelStudentsController@challansrch3');
// //Route::get('feechallandetails', 'App\Http\Controllers\Api\HostelStudentsController@challandetails');
// Route::get('challanUpdate', 'App\Http\Controllers\Api\HostelStudentsController@updateChallan');
// Route::get('bankr', 'App\Http\Controllers\Api\HostelStudentsController@bankers');
// Route::get('banktyp', 'App\Http\Controllers\Api\HostelStudentsController@bankType');
// //Route::get('challanGen', 'App\Http\Controllers\Api\HostelStudentsController@chalangeric');
// Route::get('dvv', 'App\Http\Controllers\Api\HostelStudentsController@genrateDV');
// // Route::get('dvupdate', 'App\Http\Controllers\Api\HostelStudentsController@updatefees');
// Route::get('feesback', 'App\Http\Controllers\Api\HostelStudentsController@feerolebacks');
// Route::get('feesbackfinal', 'App\Http\Controllers\Api\HostelStudentsController@rolebackfinal');

//****************** */ Waqas Bhai
Route::post('sessionwise-fee-report', [SessionWiseFeeReportController::class, 'sessionWiseFee']);
Route::get('student-missing-information-report', [StudentMissingInformationListController::class, 'studentMissingInfoReport']);
Route::POST('show-campus-fee-infomation', [StudentFeeInformationController::class, 'showcampusfeeinfomation']);
Route::get('predndetails', [StudentFeeInformationController::class, 'showAllChallans']);
Route::get('showfine', [LateFeeFinesController::class, 'showfine']);
Route::POST('updatefine', [LateFeeFinesController::class, 'updatefine']);
Route::POST('save-certificate', [CertificateController::class, 'SaveCertificate']);
Route::POST('campus-class', [CertificateController::class, 'campusClass']); //allclass
Route::POST('student-class', [CertificateController::class, 'StudentClasses']); //allclass1
// Route::POST('add-leaving-certificate', [CertificateController::class, 'LeavingCertificate']);
Route::POST('view-certificate', [CertificateController::class, 'viewCerificate']);
Route::GET('paid-challan-search', [CertificateController::class, 'paidchallansearch']);
Route::GET('all-challan-search-for-certificate', [CertificateController::class, 'allchallansearchforcertificate']);
Route::get('certificate-max-id', [CertificateController::class, 'Certificate_id']);
Route::POST('all-certificates', [CertificateController::class, 'CertificateAll']);
Route::POST('campus-vise-fee-status', [StudentFeeStatusController::class, 'CampusViseFeeStatus']);
Route::POST('class-vise-fee-status', [StudentFeeStatusController::class, 'ClassViseFeeStatus']);
Route::POST('section-vise-fee-status', [StudentFeeStatusController::class, 'SectionViseFeeStatus']);
Route::POST('yearly-fee-report', [StudentFeeStatusController::class, 'YearlyFeeReport']);
Route::POST('employee-search-for-gpfund', [GPFundReturn::class, 'EmployeeSearch']);
Route::POST('genrate-gpfund-return', [GPFundReturn::class, 'GenrateGPFundReturn']);
Route::POST('single-student-attendance-details', [AttendanceSummaryController::class, 'SingleStudentAttendanceDetails']);
Route::POST('monthlyAttendance-details', [AttendanceSummaryController::class, 'MonthlyAttendance']);
Route::POST('SingleStudentMonthlyAttendance-details', [AttendanceSummaryController::class, 'SingleStudentMonthlyAttendance']);
Route::get('closingYearView', [YearEndController::class, 'closingYearView']);
Route::POST('sessionClosed', [YearEndController::class, 'sessionClosed']);
Route::GET('firmview', [FirmWiseReportController::class, 'firmview']);

//************ */ New Updated Urls


Route::get('get-all-banks', [PartialFeeReceiveController::class, 'getAllBanks']);
Route::get('get-fee-challans-details', [PartialFeeReceiveController::class, 'getFeeChallanDetails']);


Route::post('update-challan-fee', [PartialFeeReceiveController::class, 'updateChallanFee']);
Route::post('search-student-challans', [PartialFeeReceiveController::class, 'SearchStudentChallans']);
Route::post('search-rollback-challans', [PartialFeeReceiveController::class, 'SearchRollBackChallans']);
Route::post('receive-partial-fee', [PartialFeeReceiveController::class, 'ReceivePartialFee']);
Route::post('receive-sub-partial-fee', [PartialFeeReceiveController::class, 'ReceiveSubPartialFee']);
Route::post('fee-challan-details', [PartialFeeReceiveController::class, 'getFeeChallanDetails']);
Route::post('get-student-challans', [PartialFeeReceiveController::class, 'GetStudentChallans']);


Route::post('roll-back-challan', [RollBackController::class, 'RollBackChallan']);
Route::post('update-challan', [RollBackController::class, 'UpdateChallan']);
Route::post('search-bank-wise-challans', [RollBackController::class, 'SearchBankWiseChallans']);

Route::post('update-liable-fee', [StudentController::class, 'UpdateLiableFees']);

// Route::post('search-challan-admission-number', [PartialFeeReceiveController::class, 'SearchChallanAdmissionNumber']);
Route::get('get-settings', [SettingController::class, 'getSetting']);
Route::post('update-settings', [SettingController::class, 'updateSettings']);

// Route::get('search-challan-registration-number', [PartialFeeReceiveController::class, 'SearchChallanViaRegistrationNumber']);
Route::get('bank-sub-category', [PartialFeeReceiveController::class, 'bankSubCategory']);


// ***************************
Route::group(['middleware' => ['cors']], function () {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('search_relevant_domain', [DomainController::class, 'emailDomain']);
});

Route::group(['middleware' => ['auth:sanctum', 'verified', 'cors']], function () {
    Route::post('logout', [LoginController::class, 'logout']);
    Route::group(['middleware' => ['school']], function () {

        Route::group(['namespace' => 'App\Http\Controllers\Api'], function () {
            Route::get('challan_detail_filling_student_id_for_all_challans', 'FeesChallanController@runit');


            Route::resource('courses', 'CourseController');
            Route::resource('student', 'StudentController');
            Route::resource('subject', 'SubjectController');
            Route::resource('campus', 'CampusController');
            Route::resource('hostel', 'HostelController');
            Route::resource('branch', 'BranchController');

            Route::get('remove-campus-code-to-admission-id', 'StudentController@removeCampusCodeToAdmissionId');
            Route::get('add-campus-code-to-admission-id', 'StudentController@addCampusCodeToAdmissionId');
            Route::get('add-notification-ids-to-students', 'StudentController@addNotificationIdToStudents');
            Route::post('subjects_assign_student', 'SubjectController@subjectsAssignStudent');
            Route::post('remove-subjects-student', 'SubjectController@removeAssignedSubjects');
            Route::post('subjects-assign-to-classes', 'SubjectController@subjectAssignToClasses');
            Route::get('assigned-subjects-of-class/{class_id}', 'SubjectController@assignedSubjectOfClass');
            Route::post('student_search_reg_id', 'StudentController@searchStudentByReg');
            Route::post('subjects_assign_student_without_detach', 'SubjectController@subjectsAssignStudentWithoutDetach');
            Route::post('search_by_name_or_adm_id', 'StudentController@searchNameId');
            Route::post('search_by_reg_id_name_adm_id', 'StudentController@regIdNameAdmId');
            Route::post('student_search_name', 'StudentController@searchStudentName');

            //define liable fees for student during registration for each stdent idividually
            Route::resource('vehicle', 'VehicleController');
            Route::resource('bank', 'GlobalBankController');
            Route::resource('concession', 'ConcessionController');
            Route::resource('headoffice', 'HeadOfficeController');
            Route::resource('student_liable_fee', 'StudentLiableFeeController');
            Route::get('student_all_liable_fees/{student}', 'StudentLiableFeeController@studentFees');

            Route::resource('staffmember', 'StaffMemberController');
            Route::get('staff-member/campus/{campus_id}', 'StaffMemberController@campusWiseStaff');
            Route::put('update/staff-member/{user_id}', 'StaffMemberController@updateStaffMember');

            Route::resource('campusclass', 'CampusClassController');
            Route::get('campus_class_delete/{campus_id}/{student_class_id}', 'CampusClassController@campusClassDelete');
            Route::get('campusclass/all/{campus_id}', 'CampusClassController@index');

            Route::resource('studentclass', 'StudentClassController');

            Route::resource('feestructure', 'FeeStructureController');
            Route::post('feestructure_amount', 'FeeStructureController@getAmount');
            Route::get('classfeetypes/{campus_id}/{student_class_id}/{year_id}', 'FeeStructureController@classfeetypes');
            Route::get('campus/campus-fees/{campus_id}/{year_id}', 'FeeStructureController@campusFees');

            Route::resource('feestypes', 'FeesTypeController');


            // employee
            Route::resource('designations', 'DesignationController');
            Route::resource('employees', 'EmployeeController'); //employee registraion only
            Route::get('employees_index/{campus}', 'EmployeeController@Index');
            Route::post('employees_all_campus', 'EmployeeController@allEmpForStatus'); // against job-status-id
            Route::get('employees_search_by_code/{emp_code}', 'EmployeeController@showByCode');
            Route::get('emp_appointed/{campus}/{job_status_id?}', 'EmployeeController@empIndex'); //collction employee appointed
            Route::post('appointed-employees/filter/name-code', 'EmployeeController@CampusEmployees'); //collction employee appointed
            Route::get('emp_appointed-show/{employee}', 'EmployeeController@empShow'); // employee appointed show single
            Route::put('emp_appointed/{employee}', 'EmployeeController@empStore'); // employee appointed show single
            Route::put('update_emp_appointed/{employee}', 'EmployeeController@empUpdate'); // employee appointed show single
            Route::resource('jobstatuses', 'JobStatusController');
            Route::resource('payscales', 'PayScaleController');

            Route::put('define-salary/{employee}', 'EmployeeController@defineSalary'); // employee appointed show single
            Route::get('emp-salary-detail/{employee}', 'EmployeeController@empSalaryDetailShow'); // employee appointed show single
            // salary salary salary
            // Student Fee Graphs
            Route::get('/total_paid_unpaid_fee', [GraphController::class, 'totalPaidUnpaidFeeGraph']);
            Route::get('/total-student-graph', [GraphController::class, 'totalStudentGraph']);
            Route::get('/student_liable_fee_graph', [GraphController::class, 'studentLiableFeeGraph']);
            Route::get('/total_concession_student_graph', [GraphController::class, 'totalConcessionStudent']);
            Route::get('/total_employees_salaries_graph', [GraphController::class, 'totalEmployeesSalaries']);
            Route::get('/total_employees_graph', [GraphController::class, 'totalEmployees']);
            Route::get('/employees_type_graph', [GraphController::class, 'employeeTypes']);
            Route::get('/total_amount_balance', [GraphController::class, 'totalAccountBalance']);
            // Search Staff
            Route::post('search_appointed_employees_by_code', [SearchController::class, 'SearchEmployeesByCode']); //collction employee appointed by Code
            Route::post('search_appointed_employees_by_name', [SearchController::class, 'SearchEmployeesByName']); //collction employee appointed by Name
            Route::post('search_appointed_employees_by_father', [SearchController::class, 'SearchEmployeesByFather']); //collction employee appointed by Father Name
            Route::post('search_appointed_employees_by_id', [SearchController::class, 'SearchEmployeesByID']); //collction employee appointed by ID Card

            // Search Student
            Route::post('search_by_adm_id', [SearchController::class, 'SearchStudentByAdmID']);
            Route::post('search_by_name', [SearchController::class, 'SearchStudentByName']);
            Route::post('search_by_father_name', [SearchController::class, 'SearchStudentByFather']);
            Route::post('search_by_id_card', [SearchController::class, 'SearchStudentByID']);
            Route::post('search_by_address', [SearchController::class, 'SearchStudentByAddress']);
            Route::post('search_by_mobile_no', [SearchController::class, 'SearchStudentByMobile']);

            //employee salary generate and create and set
            Route::post('employeeSalary-create/{employee}', 'EmployeeSalaryController@singleEmpSalary');
            Route::get('employeeSalary-get/{employee}', 'EmployeeSalaryController@getEmpSalary');
            Route::post('employeeSalary_update', 'EmployeeSalaryController@updateEmpSalary');
            Route::post('get_salaries_filter', 'EmployeeSalaryController@GetSalariesFilter');
            Route::put('update_gpfund/{employee}', 'EmployeeSalaryController@updateGPFund');

            // Route::post('bulk_salary_generate', 'EmployeeSalaryController@bulkSalaryGenerate');
            Route::post('get_employee_list', 'EmployeeSalaryController@getEmployeeList');
            Route::post('bulk_salary_generate_by_list', 'EmployeeSalaryController@bulkSalaryGenerateByList');

            //salary payment letter
            Route::post('get_salaries_for_payment_letter', 'EmployeeSalaryController@salariesBankWise');
            Route::post('pay_salaries', 'EmployeeSalaryController@paySalaries');

            Route::resource('session', 'SessionController');
            Route::resource('bank_account_category', 'BankAccountCategoryController');
            Route::resource('bank_account', 'BankAccountController');
            Route::resource('bank_account', 'BankAccountController');

            Route::resource('printaccountno', 'PrintAccountNoController');

            Route::resource('registrationcard', 'RegistrationCardController');
            Route::get('campus/issue-cards/{campus_id}', 'RegistrationCardController@issueCards');
            Route::get('campus/not-issue-cards/{campus_id}', 'RegistrationCardController@notIssueCards');

            //student_list_to_jump jump student
            Route::post('student_list_to_jump', 'StudentController@stdJumpList');
            Route::post('student_jump', 'StudentController@stdJump');
            Route::post('student_pass_out', 'StudentController@stdPassOut');
            Route::post('student_struct_off', 'StudentController@stdStructOff');

            //campus students and fees through given campus/id
            Route::get('campus/campus-students/{campus_id}', 'StudentController@campusStudents');
            Route::post('studentstatus_change', 'StudentController@changeStatus');
            Route::post('student_concession_change', 'StudentController@changeConcession');
            Route::post('admission_after_registration/{student_id}', 'StudentController@admAfterRegister');

            Route::post('student_filter_list', 'StudentController@studentFilterList');
            Route::post('all_registered_or_admitted', 'StudentController@allRegisteredOrAdmitted');

            //fee generation campus/campus-students/1
            Route::get('get_student_byid/{adm_id}', 'StudentController@getByAdmissionId');
            Route::post('upload_std_picture', 'StudentController@stdPictureUploading');


            Route::post('all_fee_generate_std_list', 'FeesGeneratorController@allFeeStdList');
            Route::post('all_fee_generate_with_std_list', 'FeesGeneratorController@feeGenerateByStdList');
            Route::post('all_fee_generate_with_std_list_New', 'FeesGeneratorController@feeGenerateByStdListNew');
            Route::post('custom_fees_generate/{student}', 'FeesGeneratorController@CustomFeesGenerator'); // latest

            Route::get('show_student_challans/{std_id}', 'FeesGeneratorController@showStdChallans');
            Route::post('student/challan_history', 'FeesGeneratorController@showStdChallanHistory');

            Route::get('show_all_challans', 'FeesChallanController@showAllChallans');
            Route::get('show_all_paid_challans', 'FeesChallanController@showAllPaidChallans');

            Route::get('show_campus_challans/{campus}/{status?}', 'FeesChallanController@campusChallan');
            Route::get('show_campus_class_challans/{campus}/{class_id}/{education_type}/{status?}', 'FeesChallanController@classChallan');
            Route::get('show_campus_section_challans/{campus}/{class_id}/{section_id}/{status?}', 'FeesChallanController@sectionChallan');
            Route::post('show_g_l_challans', 'FeesChallanController@ChallansGL');

            Route::get('class_section/{campus_id}/{education_type}/{student_class_id?}', 'ClassSectionController@campusClassSections');
            Route::get('global_section_list', 'ClassSectionController@globalSections');
            Route::post('store_class_sections', 'ClassSectionController@addSectionToClass');

            Route::delete('delete_class_section/{campus_id}/{student_class_id}/{global_section_id}', 'ClassSectionController@destroy');

            Route::get('show_challan/{challan_no}', 'FeesChallanController@getChallanByNo');
            Route::post('search_challan_no', 'FeesChallanController@searchByChallanNo');
            Route::put('fee_receiving/{feeChallan}', 'FeesChallanController@feeReceiving');
            Route::put('fee_roleback/{feeChallan}', 'FeesChallanController@feeRoleback');
            Route::delete('fee-destroy/{feeChallan}', 'FeesChallanController@destroy');
            Route::post('fee_challan_edit', 'FeesChallanController@ChallanEdit');
            Route::post('get_challan_for_spliting', 'FeesChallanController@getChallanSplit');
            Route::post('fee_challan_split', 'FeesChallanController@challanSplit');
            Route::post('student_chllan_by_admission_id', 'FeesChallanController@studentUnpaidChllans');
            Route::post('student_chllan_by_admission_id_submit', 'FeesChallanController@submitStudentUnpaidChllans');
            Route::post('edit_challan_detail_in_feesubmit', 'FeesChallanController@editChallanDetailFeesubmit');

            Route::post('unpaid_challan_combine', 'FeesChallanController@unpaidChallanCombine'); //student wise
            Route::post('unpaid_challan_search_std_wise', 'FeesChallanController@searchStudentWiseChallan'); //search a student wise challan 4 print
            Route::post('fee_challan_month_wise', 'FeesChallanController@feeChallanMonthWise');
            Route::post('fee-challan-month-wise-single-student', 'FeesChallanController@singleStudentFeeChallanMonthWise');
            Route::post('challan_detail_unpaid', 'FeesChallanController@challanDetailUnpaid');

            Route::post('student_signup', 'StudentController@studentSignup');
            Route::post('student_auth_update', 'StudentController@studentAuthUpdate');
            Route::post('emp_lecture_assign', 'EmpLectureController@empLectureAssign');
            Route::get('emp_lecture_list/{employee_id}', 'EmpLectureController@empLectureList');

            Route::post('emp_assign_to_class', 'EmpLectureController@empAssignToClass');
            Route::post('class_lecture_list', 'EmpLectureController@classLectureList');
            Route::delete('emp_lecture/{emp_lecture}', 'EmpLectureController@destroy');

            Route::post('laon_installment_against_salary', 'LoanController@salaryLoan');
            Route::post('loan_employee_show', 'LoanController@employeeLoan');

            Route::post('get_student_for_fee_return', 'LoanController@feeReturnStudent');
            //Route::post('fee_return', 'LoanController@feeRetrun');
            Route::post('fee_return', 'LoanController@feeReturn');
            Route::get('fee_return_index', 'LoanController@feeReturnIndex');
            Route::get('/student/{student}/fee-return-history', 'LoanController@studentFeeReturnHistory');
            Route::post('fee_return_edit', 'LoanController@feeReturnEdit');
            Route::delete('fee_return/{fee_return}', 'LoanController@feeReturnDestroy');

            Route::post('mark_students_absent', 'AttendanceController@markAttendance');
            Route::post('single_day_attendances', 'AttendanceController@singleDayAttendances');
            Route::get('attendance_status', 'AttendanceStatusController@index');

            Route::post('hostel/student/search', [HostelManagementController::class, 'hostelStudentNameSearch']);
            Route::post('students-by-hostel', [HostelManagementController::class, 'hostelStudents']);
            Route::post('assign-hostel/student', [HostelManagementController::class, 'assignHostelStudent']);
            Route::post('remove-hostel/student', [HostelManagementController::class, 'removeHostelStudent']);
            Route::post('/paid-pending/fees', [FeesController::class, 'paidPendingStudents']);

            Route::post('/staff-loan-report', [LoanReportController::class, 'staffLoanReport']);

            Route::get('/sms-types', [SmsTypesController::class, 'index']);

            Route::post('/student-custom-sms', [StudentCustomSMSController::class, 'studentCustomSMS']);

            Route::post('/student-due-fee-sms', [StudentDueFeeSMS::class, 'studentDueFeeSMS']);

            Route::post('/student-result-sms', [StudentResultSMSController::class, 'studentResultSMS']);

            Route::post('/employee-custom-sms', [EmployeeCustomSMSController::class, 'employeeCustomSms']);

            Route::apiResource('notification-types', 'Notification\NotificationTypesController')->only('index', 'show');

            Route::apiResource('notifications', 'Notification\NotificationsController')->except('show');

            Route::post('/staff-pay-detail-report', [StaffPayDetailReportController::class, 'staffPayDetailReport']);
            Route::post('/staff_gross_salary_report', [StaffGrossSalaryReportController::class, 'staffGrossSalaryReport']);
            Route::post('/concession-report', [ConcessionReportController::class, 'concessionReport']);

            Route::post('/activate-session', [SessionController::class, 'activateSession']);

            Route::apiResource('supports', 'SupportController')->only('index', 'store', 'destroy');
        });

        Route::group(['namespace' => 'App\Http\Controllers\Api\Account'], function () {
            Route::resource('baseaccount', 'BaseAccountController');
            Route::resource('subaccount', 'SubAccountController');
            Route::get('subaccount_contra', 'SubAccountController@contra'); //for voucher dropdown
            Route::get('show_employee_loan_accounts', 'SubAccountController@loanAccounts'); //for loan dropdown
            Route::get('show_fees_account_head', 'SubAccountController@FeesAccounts'); //for refund fees account head

            Route::get('sub_account_only_banks', 'SubAccountController@subAccountBanks'); //for voucher dropdown

            Route::resource('accountchart', 'AccountChartController');
            Route::resource('voucher', 'VoucherController');
            Route::resource('vouchertype', 'VoucherTypeController');
            Route::post('voucher_filter', 'VoucherController@filter');
            Route::post('voucher_summary', 'VoucherController@voucherSummary');

            Route::post('submitted_challan_list', 'VoucherController@submittedChallans');
            Route::post('createchllans_voucher', 'VoucherController@challansToVoucher');

            route::get('payed_salaries_list', 'VoucherController@payedSalaries');
            route::post('day_end_voucher', 'VoucherController@dayEndVoucher');
            route::post('account_balance_sheet', 'BaseAccountController@balanceSheet');
            route::post('account_fees_report', 'BaseAccountController@feesReport');
            route::post('account_salary_report', 'BaseAccountController@salaryReport');
            route::post('account_expenses_report', 'BaseAccountController@expensReport');
            route::post('sub-account-balance', 'SubAccountController@subAccountBalance');
        });

        Route::get('notifications/campus/{campus}', [NotificationsController::class, 'campusNotifications']);
    }); //school middlwware

    Route::group(['namespace' => 'App\Http\Controllers\Api\Exam'], function () {
        Route::resource('exam_type', 'ExamTypeController');
        Route::resource('exam', 'ExamController');
        Route::delete('delete-exam/{id}', 'ExamController@destroy');

        Route::post('update_exam_classes', 'ExamController@updateExamClass');
        Route::post('add_exam_student', 'ExamController@addExamStudent');
        Route::post('exam_for_session', 'ExamController@examForSession');
        Route::get('add-exam-names', 'ExamController@addExamNames');
        Route::post('update-exam-name', 'ExamController@updateExamName');
        Route::post('remove-exam-classes', 'ExamController@removeExamClasses');
        Route::post('remove-exam-student', 'ExamController@removeExamStudent');

        Route::resource('date_sheet', 'DateSheetController');
        Route::delete('delete-date-sheet-subject/{id}', 'DateSheetController@deleteDateSheetSubject');
        Route::post('note-date-sheet', 'DateSheetController@addNoteInDateSheet');
        Route::put('date_sheet_update', 'DateSheetController@updateDateSheet');
        Route::post('exam_class_datesheet', 'DateSheetController@examClassDatesheet');
        Route::post('date-sheet-exam-list', 'DateSheetController@DateSheetExamList');

        Route::post('result_get', 'ResultController@resultGet');
        Route::post('result_student_wise', 'ResultController@resultStudentWise');
        Route::post('result_sequence', 'ResultController@resultSequanceSeting');

        Route::post('result_update', 'ResultController@resultUpdate');
        Route::post('student_result_get', 'ResultController@studentResultGet');
        Route::post('student_result_adm', 'ResultController@studentResultGetAdm');
        Route::post('student_exam_list', 'ResultController@stdExamList');
        Route::post('exam_status_update', 'ExamController@updateStatus');

        Route::post('student_paid_fee_history', 'ResultController@stdPaidFeeHistory');
        Route::get('student_unpaid_fee_history', 'ResultController@stdUnPaidFeeHistory');
    });
    Route::group(['namespace' => 'App\Http\Controllers\Api'], function () {
        Route::get('student_attendance', 'AttendanceController@studentAttendance'); //student
    });

    Route::get('notifications/students', [NotificationsController::class, 'studentsNotifications']);
    Route::get('staff_principal_student_campus_id', [NotificationsController::class, 'databaseRefactor']);
    Route::get('campus-support', [SupportController::class, 'campusSupport']);

    Route::get('student-sessions-list', [SessionController::class, 'examsSessionsList']);

    Route::post('student-notification_ids', [StudentController::class, 'getNotificationIds']);
    Route::get('dashboard', [DashboardController::class, 'index']);
    // Route::get('student-by-reg-id', 'App\Http\Controllers\Api\StudentMetaDataController@studentByRegID');
}); //auth middleware
