<?php

namespace App\Repository;

use App\Http\Resources\JobStatusResource;
use App\Models\JobStatus;
use App\Repository\Interfaces\JobStatusRepositoryInterface;

class JobStatusRepository extends BaseRepository implements JobStatusRepositoryInterface
{
    /**$userRepository
     * ProfileRepository constructor.
     *
     * @param User $model
     */
    public function __construct(JobStatus $model)
    {
        parent::__construct($model);
    }

    public function index()
    {
        return JobStatusResource::collection(JobStatus::all());
    }

    public function show(JobStatus $jobstatus)
    {
        return new JobStatusResource($jobstatus);
    }
}
