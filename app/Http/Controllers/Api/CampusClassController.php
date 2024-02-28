<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Campus;
use App\Models\CampusClass;
use App\Repository\CampusClassRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CampusClassController extends BaseController
{
    public function __construct(CampusClassRepository $campusClassRepository)
    {
        $this->campusClassRepository = $campusClassRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($campus_id)
    {
        return $this->sendResponse($this->campusClassRepository->index($campus_id), []);
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
        $validator = $this->validateCampusClass($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->campusClassRepository->store($request), []);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($campusclass)
    {
        return $this->sendResponse($this->campusClassRepository->show($campusclass), []);
    }




    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(CampusClass $campusclass)
    {
        return 'this api is changed now,plz use the new one';

        return $this->sendResponse($this->campusClassRepository->destroy($campusclass), []);
    }

    public function campusClassDelete($campus_id, $student_class_id)
    {
        $campusClass = CampusClass::where(['campus_id' => $campus_id, 'student_class_id' => $student_class_id])->first();
        if ($campusClass) {
            $deleted = $campusClass->delete();
            if ($deleted) {
                return $this->sendResponse([], 'class for campus successfully deleted', []);
            }

            return $this->sendError('can not be deleted, server side error', [], 500);
        }

        return $this->sendError('server side error, cannot find the record', [], 500);
    }

    private function validateCampusClass(Request $request)
    {
        return Validator::make($request->all(), [

            // 'campus_id' => 'required',
            'student_class_id' => 'required',
        ]);
    }
}
