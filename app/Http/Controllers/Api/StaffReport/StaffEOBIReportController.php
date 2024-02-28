<?php

namespace App\Http\Controllers\Api\StaffReport;

use App\Http\Controllers\Api\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StaffEOBIReportController extends BaseController
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campus_id' => 'nullable|integer|exists:campuses,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        if (!$request->campus_id)
            $campuses = Campus::get()->toArray();
        else
            $campuses = Campus::where('id', $request->campus_id)->get()->toArray();


        $data = [];

        foreach ($campuses as $campus) {
            $campusData = [
                'Campus' => $campus['name'],
                'employees' => Employee::with(['salaryDeduction', 'designation', 'bankAccount', 'payScale'])
                    ->where('campus_id', $campus['id'])
                    ->where('eobi_no', '!=', null)
                    ->get(),
            ];

            $data[] = $campusData;
        }

        return $this->sendResponse($data, '', 200);
    }
}
