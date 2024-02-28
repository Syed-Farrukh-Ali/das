<?php


namespace App\Http\Controllers\Api;

use App\Models\LateFeeFine;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LateFeeFinesController extends BaseController
{
    public function showfine()
    {
        $fineData = LateFeeFine::all();
        return $this->sendResponse($fineData, '', 200);
    }

    public function updatefine(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $fineAmount = $request->amount;
        //latefeefine::where('id','1')->update([['amount', $fineAmount]]);
        LateFeeFine::where('id', '=', '1')->update(['amount' => $fineAmount]);
        $fineData = LateFeeFine::all();
        return $this->sendResponse($fineData, '', 200);
    }
}
