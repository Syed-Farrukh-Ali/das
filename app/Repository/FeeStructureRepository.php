<?php

namespace App\Repository;

use App\Http\Resources\FeeStructureResource;
use App\Http\Resources\FeesTypeResource;
use App\Models\Campus;
use App\Models\CampusClass;
use App\Models\FeeStructure;
use App\Models\FeesType;
use App\Models\User;
use App\Repository\Interfaces\FeeStructureRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeeStructureRepository extends BaseRepository implements FeeStructureRepositoryInterface
{
    /**$userRepository
     * ProfileRepository constructor.
     *
     * @param User $model
     */
    public function __construct(FeeStructure $model)
    {
        parent::__construct($model);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return FeeStructureResource::collection(FeeStructure::all());
    }

    public function campusFees($campus_id, $year_id)
    {
        return FeeStructureResource::collection(Campus::find($campus_id)->feeStructures()->where(['campus_id' => $campus_id, 'session_id' => $year_id])->orderBy('fee_type_id')->get());
    }

    public function getAmount(Request $request)
    {
        $feestructure = FeeStructure::where([
            'campus_id' => $request->campus_id,
            'student_class_id' => $request->student_class_id,
            'fee_type_id' => $request->fee_type_id,
            'session_id' => $request->year_id,
        ])->first();

        return $feestructure;
    }

    // ( Campus::find($campus_id)->registrationCards()->where('status',1)->get() );
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            if ($request->student_class_id == '-1') {
                $campusClasses = CampusClass::where('campus_id', $request->campus_id)->pluck('student_class_id');

                foreach ($campusClasses as $campusClass) {
                    $feestructure = FeeStructure::firstOrCreate([
                        'campus_id' => $request->campus_id,
                        'fee_type_id' => $request->fee_type_id,
                        'student_class_id' => $campusClass,
                        'session_id' => $request->year_id,
                    ], [
                        'amount' => $request->amount,
                        // 'course_id' => $request->course_id,

                        // 'shift' => $request->shift,
                    ]);
                }
            } else {
                $feestructure = FeeStructure::create([
                    'campus_id' => $request->campus_id,
                    'fee_type_id' => $request->fee_type_id,
                    'student_class_id' => $request->student_class_id,
                    'amount' => $request->amount,
                    // 'course_id' => $request->course_id,
                    'session_id' => $request->year_id,
                    // 'shift' => $request->shift,
                ]);
            }
        } catch (\Throwable $e) {
            dd($e);
            DB::rollBack();

            return false;
        }
        DB::commit();
        $feestructure->load('session');

        return new FeeStructureResource($feestructure);
    }

    public function show(FeeStructure $feeStructure)
    {
        return new FeeStructureResource($feeStructure);
    }

    /**
     * @param  illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Throwable
     */
    public function update(Request $request, FeeStructure $feeStructure)
    {
        DB::beginTransaction();
        try {
            $feeStructure->update([
                'amount' => $request->amount,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }
        DB::commit();

        return new FeeStructureResource($feeStructure);
    }

    public function destroy(FeeStructure $feeStructure)
    {
        $feeStructure->delete();

        return response()->json('fee successfully deleted');
    }
}
