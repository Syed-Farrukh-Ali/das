<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Api\BaseController;
use App\Models\StudentClass;
use App\Repository\StudentClassRepository;

class StudentClassController extends BaseController
{
    public function __construct(StudentClassRepository $studentClassRepository)
    {
        $this->studentClassRepository = $studentClassRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse($this->studentClassRepository->index(), []);
    }

    public function show(StudentClass $studentclass)
    {
        return $this->sendResponse($this->studentClassRepository->show($studentclass), []);
    }
}
