<?php

namespace App\Repository;

use App\Http\Resources\ConcessionResource;
use App\Models\Concession;
use App\Repository\Interfaces\ConcessionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConcessionRepository extends BaseRepository implements ConcessionRepositoryInterface
{
    /**$userRepository
    * ProfileRepository constructor.
    *
    * @param User $model
    */
    public function __construct(Concession $model)
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
         return ConcessionResource::collection(Concession::where('is_used', 1)->get());
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
             $concession = Concession::create([
                 'title' => $request->title,
                 'percentage' => $request->percentage,
                 'amount' => $request->amount,

             ]);
         } catch (\Throwable $e) {
             dd($e);
             DB::rollBack();

             return false;
         }
         DB::commit();

         return new ConcessionResource($concession);
     }

     public function show(Concession $concession)
     {
         return new ConcessionResource($concession);
     }

     /**
      * @param  illuminate\Http\Request  $request
      * @return bool
      *
      * @throws \Throwable
      */
     public function update(Request $request, Concession $concession)
     {
         DB::beginTransaction();
         try {
             $concession->update([
                 'amount' => $request->amount,
                 'percentage' => $request->percentage,

             ]);
         } catch (\Throwable $e) {
             DB::rollBack();

             return false;
         }
         DB::commit();

         return true;
     }

     public function destroy(Concession $concession)
     {
         $deleted = $concession->update(['is_used' => false]);
         if ($deleted) {
             return response()->json('concession successfully deleted');
         }
     }
}
