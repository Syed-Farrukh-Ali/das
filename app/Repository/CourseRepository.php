<?php

namespace App\Repository;

use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Repository\Interfaces\CourseRepositoryInterface;

class CourseRepository extends BaseRepository implements CourseRepositoryInterface
{
    /**$userRepository
     * ProfileRepository constructor.
     *
     * @param User $model
     */
    public function __construct(Course $model)
    {
        parent::__construct($model);
    }

    public function index()
    {
        return CourseResource::collection(Course::all());
    }

    public function show(Course $course)
    {
        return new CourseResource($course);
    }
}
