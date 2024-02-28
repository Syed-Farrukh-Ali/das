<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AttendanceStatusResource;
use App\Models\AttendanceStatus;
use Illuminate\Http\Request;

class AttendanceStatusController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $attendance = AttendanceStatus::get();
        $data = [
            'status' => AttendanceStatusResource::collection($attendance),
        ];

        return $this->sendResponse($data, 'attendance statuses', 200);
    }
}
