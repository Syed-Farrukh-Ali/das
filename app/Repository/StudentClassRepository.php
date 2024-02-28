<?php

namespace App\Repository;

use App\Http\Resources\StudentClassResource;
use App\Models\StudentClass;
use App\Repository\Interfaces\StudentClassRepositoryInterface;

class StudentClassRepository extends BaseRepository implements StudentClassRepositoryInterface
{
    /**$userRepository
     * ProfileRepository constructor.
     *
     * @param User $model
     */
    public function __construct(StudentClass $model)
    {
        parent::__construct($model);
    }

    public function index()
    {
        return StudentClassResource::collection(StudentClass::all());
    }

    public function show(StudentClass $studentclass)
    {
        return new StudentClassResource($studentclass);
    }
}
