<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Api\BaseController;
use App\Models\JobStatus;
use App\Repository\JobStatusRepository;
use Illuminate\Http\Request;

class JobStatusController extends BaseController
{
    public function __construct(JobStatusRepository $jobStatusRepository)
    {
        $this->jobStatusRepository = $jobStatusRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse($this->jobStatusRepository->index(), []);
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
     * @param  \App\Models\JobStatus  $jobStatus
     * @return \Illuminate\Http\Response
     */
    public function show(JobStatus $jobstatus)
    {
        return $this->sendResponse($this->jobStatusRepository->show($jobstatus), []);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JobStatus  $jobStatus
     * @return \Illuminate\Http\Response
     */
    public function edit(JobStatus $jobStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\JobStatus  $jobStatus
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, JobStatus $jobStatus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JobStatus  $jobStatus
     * @return \Illuminate\Http\Response
     */
    public function destroy(JobStatus $jobStatus)
    {
        //
    }
}
