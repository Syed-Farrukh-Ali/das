<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\FeeStructureResource;
use App\Http\Resources\FeesTypeResource;
use App\Models\FeeStructure;
use App\Models\FeesType;
use App\Repository\FeeStructureRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeeStructureController extends BaseController
{
    public function __construct(FeeStructureRepository $feeStructureRepository)
    {
        $this->feeStructureRepository = $feeStructureRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse($this->feeStructureRepository->index(), []);
    }

    public function campusFees($campus_id, $year_id)
    {
        return $this->sendResponse($this->feeStructureRepository->campusFees($campus_id, $year_id), []);
    }

    public function getAmount(Request $request)
    {
        $feestructure = $this->feeStructureRepository->getAmount($request);

        if ($feestructure) {
            return $this->sendResponse(new FeeStructureResource($feestructure), []);
        }

        return $this->sendError([], $this->serverErrorMessage(), 500);
    }

    public function classfeetypes($campus_id, $student_class_id, $year_id)
    {
        $feetypeids = FeeStructure::where('campus_id', $campus_id)
            ->where('student_class_id', $student_class_id)
            ->where('session_id', $year_id)
            ->pluck('fee_type_id')->unique();

        $fee_types = FeesType::whereIn('id',$feetypeids)->get();

        $result = FeesTypeResource::collection($fee_types);

        $response = [
            'metadata' => [
                'responseCode' => 200,
                'success' => true,
                'message' => '',
            ],
            'payload' => $result,
        ];

        return response()->json($response, 200);
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
        $validator = $this->validateFees($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        if ($this->isEmptyFeeStructure($request)) {
            // code...
            return $this->sendResponse($this->feeStructureRepository->store($request), []);
        } else {
            return $this->sendError('fee for this type and class is already exists', [], 442);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(FeeStructure $feestructure)
    {
        return $this->sendResponse($this->feeStructureRepository->show($feestructure), []);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FeeStructure $feestructure)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->feeStructureRepository->update($request, $feestructure), []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(FeeStructure $feestructure)
    {
        return $this->sendResponse($this->feeStructureRepository->destroy($feestructure), []);
    }

    private function validateFees(Request $request)
    {
        return Validator::make($request->all(), [
            'amount' => 'required|integer',
            'campus_id' => 'required|integer',
            'fee_type_id' => 'required|integer',
            'student_class_id' => 'required|integer',
            'year_id' => ['required', 'integer', 'exists:sessions,id'],

        ]);
    }

    private function isEmptyFeeStructure(Request $request)
    {
        return FeeStructure::where([
            'campus_id' => $request->campus_id,
            'fee_type_id' => $request->fee_type_id,
            'student_class_id' => $request->student_class_id,
            'session_id' => $request->year_id,

        ])->get()->isEmpty();
    }
}
