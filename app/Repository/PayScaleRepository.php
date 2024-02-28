<?php

namespace App\Repository;

use App\Http\Resources\PayScaleResource;
use App\Models\PayScale;
use App\Repository\Interfaces\PayScaleRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayScaleRepository extends BaseRepository implements PayScaleRepositoryInterface
{
    /**$userRepository
    * ProfileRepository constructor.
    *
    * @param User $model
    */
    public function __construct(PayScale $model)
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
         return PayScaleResource::collection(PayScale::all());
     }

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
             $payScale = PayScale::create([
                 'payscale' => $request->payscale,
                 'basic' => $request->basic,
                 'increment' => $request->increment,
                 'maximum' => $request->maximum,
                 'gp_fund' => $request->gp_fund,
                 'welfare_fund' => $request->welfare_fund,
             ]);
         } catch (\Throwable $e) {
             dd($e);
             DB::rollBack();

             return false;
         }
         DB::commit();

         return new PayScaleResource($payScale);
     }

     public function show(PayScale $payscale)
     {
         return new PayScaleResource($payscale);
     }

     /**
      * @param  illuminate\Http\Request  $request
      * @return bool
      *
      * @throws \Throwable
      */
     public function update(Request $request, PayScale $payscale)
     {
         DB::beginTransaction();
         try {
             $payscale->update([
                 'payscale' => $request->payscale,
                 'basic' => $request->basic,
                 'increment' => $request->increment,
                 'maximum' => $request->maximum,
                 'gp_fund' => $request->gp_fund,
                 'welfare_fund' => $request->welfare_fund,

             ]);
         } catch (\Throwable $e) {
             DB::rollBack();

             return false;
         }
         DB::commit();

         return new PayScaleResource($payscale);
     }

     public function destroy(Payscale $payscale)
     {
         $payscale->delete();

         return response()->json('payscale successfully deleted');
     }
}
