<?php

namespace App\Repository;

use App\Http\Resources\SessionResource;
use App\Models\Session;
use App\Repository\Interfaces\SessionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionRepository extends BaseRepository implements SessionRepositoryInterface
{
    /**$userRepository
     * ProfileRepository constructor.
     *
     * @param User $model
     */
    public function __construct(Session $model)
    {
        parent::__construct($model);
    }

    public function index()
    {
        return SessionResource::collection(Session::all());
    }

    public function show(Session $session)
    {
        return new SessionResource($session);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            Session::create([
                'year' => $request->year,
            ]);
            // create student registration id
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }
        DB::commit();

        return true;
    }
}
