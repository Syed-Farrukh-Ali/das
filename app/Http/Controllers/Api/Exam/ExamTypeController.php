<?php

namespace App\Http\Controllers\Api\Exam;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\ExamTypeResource;
use App\Models\ExamType;
use Illuminate\Http\Request;

class ExamTypeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse(ExamTypeResource::collection(ExamType::all()), []);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ExamType  $examType
     * @return \Illuminate\Http\Response
     */
    public function show(ExamType $exam_type)
    {
        return $this->sendResponse(new ExamTypeResource($exam_type), []);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ExamType  $examType
     * @return \Illuminate\Http\Response
     */
    public function edit(ExamType $examType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ExamType  $examType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ExamType $examType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ExamType  $examType
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExamType $examType)
    {
        //
    }
}
