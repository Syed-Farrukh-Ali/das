<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Course;
use App\Repository\CourseRepository;

class CourseController extends BaseController
{
    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }




    public function index()
    {
        return $this->sendResponse($this->courseRepository->index(), []);
    }

    public function show(Course $course)
    {
        return $this->sendResponse($this->courseRepository->show($course), []);
    }

//    public function index()
//    {
//        return $this->sendResponse($this->courseRepository->index(), []);
//    }

}
