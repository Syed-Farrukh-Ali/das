<?php

namespace App\Repository;

use App\Http\Resources\CampusClassResource;
use App\Http\Resources\StudentClassResource;
use App\Models\CampusClass;
use App\Models\StudentClass;
use App\Models\User;
use App\Repository\Interfaces\CampusClassRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CampusClassRepository extends BaseRepository implements CampusClassRepositoryInterface
{
    /**
     * ProfileRepository constructor.
     *
     * @param  User  $model
     */
    public function __construct(CampusClass $model)
    {
        parent::__construct($model);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function index($campus_id)
    {
        $ids = CampusClass::where('campus_id', $campus_id)->pluck('student_class_id')->toArray();

        return StudentClassResource::collection(StudentClass::find($ids));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $count = count($request->student_class_id);
        if (auth()->user()->hasRole('Campus')) {
            $campus_id = auth()->user()->campus->id;
        } else {
            $campus_id = $request->campus_id;
        }
        DB::beginTransaction();
        try {
            for ($i = 0; $i < $count; $i++) {
                $campusclass = CampusClass::create([
                    'campus_id' => $campus_id,
                    'student_class_id' => $request->student_class_id[$i],
                ]);
            }
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }

        DB::commit();

        return new CampusClassResource($campusclass);
    }

    public function show($campusclass)
    {
        $ids = CampusClass::where('campus_id', $campusclass)->pluck('student_class_id')->toArray();

        return StudentClassResource::collection(StudentClass::find($ids));
    }

    /**
     * @param  illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Throwable
     */
    public function update(Request $request, Campusclass $campusclass)
    {
        DB::beginTransaction();
        try {
        } catch (\Throwable $e) {
            dd($e);
            DB::rollBack();

            return false;
        }

        DB::commit();

        return new CampusClassResource($campus);
    }

    public function destroy(Campusclass $campusclass)
    {
        $campusclass->delete();

        return response()->json('campusclass successfully deleted');
    }
}
