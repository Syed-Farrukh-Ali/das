<?php

namespace App\Http\Controllers\Api\StaffReport;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StaffReport\StaffFiguresReportRequest;
use App\Models\Campus;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\PayScale;
use Illuminate\Support\Facades\DB;

class StaffFiguresReportController extends BaseController
{
    public function staffFiguresReport(StaffFiguresReportRequest $request)
    {
        $campuses = Campus::get()->toArray();

        $data = [];

        foreach ($campuses as $campus) {
            $campusData = [
                'Campus' => $campus['name'],
                'employees' => []
            ];

            if ($request->pay_scale_wise && !$request->designation_wise) {
                $pay_scales = Employee::where('pay_scale_id', '!=', null)
                    ->where('campus_id', $campus['id'])
                    ->where('job_status_id', '1')
                    ->select('pay_scale_id', DB::raw('count(*) as count'))
                    ->groupBy('pay_scale_id')
                    ->get();

                foreach ($pay_scales as $pay_scale) {
                    $campusData['employees'][] = [
                        'pay_scale' => 'BPS - ' . PayScale::find($pay_scale->pay_scale_id)->payscale,
                        'total' => $pay_scale->count,
                    ];
                }
            }

            if ($request->designation_wise && !$request->pay_scale_wise) {
                $designation_ids = Employee::where('designation_id', '!=', null)
                    ->where('campus_id', $campus['id'])
                    ->where('job_status_id', '1')
                    ->select('designation_id', DB::raw('count(*) as count'))
                    ->groupBy('designation_id')
                    ->get();

                foreach ($designation_ids as $designation) {
                    $campusData['employees'][] = [
                        'Designation' => Designation::find($designation->designation_id)->name,
                        'total' => $designation->count,
                    ];
                }
            }

            $data[] = $campusData;
        }

        return $this->sendResponse($data, '', 200);
    }
}
