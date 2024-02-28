<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Employee\EmployeesFilterByNameRequest;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\StudentResourcePure;
use App\Models\Campus;
use App\Models\Employee;
use App\Models\Student;
use App\Repository\EmployeeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SearchController extends BaseController
{
    public function SearchEmployeesByCode(EmployeesFilterByNameRequest $request)
    {
        $employees = Employee::where('emp_code', 'like', "%{$request->name_code}%")
            ->with('campus')
            ->get();

        $employees->load('designation', 'GPFund', 'jobStatus', 'bankAccount.bank_account_category', 'students.campus', 'students.studentClass', 'payScale');

        $employees = EmployeeResource::collection($employees);
        return $this->sendResponse($employees, []);
    }

    public function SearchEmployeesByName(EmployeesFilterByNameRequest $request)
    {
        $employees = Employee::where('full_name', 'like', "%{$request->name_code}%")
            ->with('campus')
            ->get();

        $employees->load('designation', 'GPFund', 'jobStatus', 'bankAccount.bank_account_category', 'students.campus', 'students.studentClass', 'payScale');

        $employees = EmployeeResource::collection($employees);
        return $this->sendResponse($employees, []);
    }

    public function SearchEmployeesByFather(EmployeesFilterByNameRequest $request)
    {
        $employees = Employee::where('father_name', 'like', "%{$request->name_code}%")
            ->with('campus')
            ->get();

        $employees->load('designation', 'GPFund', 'jobStatus', 'bankAccount.bank_account_category', 'students.campus', 'students.studentClass', 'payScale');

        $employees = EmployeeResource::collection($employees);
        return $this->sendResponse($employees, []);
    }

    public function SearchEmployeesByID(EmployeesFilterByNameRequest $request)
    {
        $employees = Employee::where('cnic_no', 'like', "%{$request->name_code}%")
            ->with('campus')
            ->get();

        $employees->load('designation', 'GPFund', 'jobStatus', 'bankAccount.bank_account_category', 'students.campus', 'students.studentClass', 'payScale');

        $employees = EmployeeResource::collection($employees);
        return $this->sendResponse($employees, []);
    }

    // Student Search Module
    public function SearchStudentByAdmID(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_keyword' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $student = Student::with('campus', 'studentClass', 'globalSection', 'studentLiableFees', 'session')
            ->where('admission_id', 'LIKE', "%{$request->search_keyword}%")
            ->get();

        return $this->sendResponse(StudentResourcePure::collection($student), [], 200);
    }

    public function SearchStudentByName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_keyword' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $student = Student::with('campus', 'studentClass', 'globalSection', 'studentLiableFees', 'session')
            ->where('name', 'LIKE', "%{$request->search_keyword}%")
            ->get();

        return $this->sendResponse(StudentResourcePure::collection($student), [], 200);
    }

    public function SearchStudentByFather(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_keyword' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $student = Student::with('campus', 'studentClass', 'globalSection', 'studentLiableFees', 'session')
            ->where('father_name', 'LIKE', "%{$request->search_keyword}%")
            ->get();

        return $this->sendResponse(StudentResourcePure::collection($student), [], 200);
    }

    public function SearchStudentByID(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_keyword' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $student = Student::with('campus', 'studentClass', 'globalSection', 'studentLiableFees', 'session')
            ->where('father_cnic', 'LIKE', "%{$request->search_keyword}%")
            ->get();

        return $this->sendResponse(StudentResourcePure::collection($student), [], 200);
    }

    public function SearchStudentByAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_keyword' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $student = Student::with('campus', 'studentClass', 'globalSection', 'studentLiableFees', 'session')
            ->where('address', 'LIKE', "%{$request->search_keyword}%")
            ->get();

        return $this->sendResponse(StudentResourcePure::collection($student), [], 200);
    }

    public function SearchStudentByMobile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_keyword' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $student = Student::with('campus', 'studentClass', 'globalSection', 'studentLiableFees', 'session')
            ->where('mobile_no', 'LIKE', "%{$request->search_keyword}%")
            ->get();

        return $this->sendResponse(StudentResourcePure::collection($student), [], 200);
    }
}
