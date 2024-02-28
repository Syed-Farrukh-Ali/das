<?php

namespace App\Http\Controllers\Api;

use App\Repository\StudentRepository;

class StudentMetaDataController extends BaseController
{
    /**
     * StudentMetaDataController constructor.
     *
     * @param  StudentRepository  $studentRepository
     */
    public function __construct(StudentRepository $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }

     /**
      * @return \Illuminate\Http\Response
      */
     public function studentByRegID()
     {
         return $this->sendResponse($this->studentRepository->getStudentByRegistrationID(), []);
     }
}
