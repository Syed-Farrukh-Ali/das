<?php

namespace App\Repository;

use App\Http\Resources\HostelResource;
use App\Models\Hostel;
use App\Repository\Interfaces\HostelRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HostelRepository extends BaseRepository implements HostelRepositoryInterface
{
    /**$userRepository
    * ProfileRepository constructor.
    *
    * @param User $model
    */
    public function __construct(Hostel $model)
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
         return HostelResource::collection(Hostel::all());
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
             $hostel = Hostel::create([
                 'campus_id' => $request->campus_id,
                 'name' => $request->name,
                 'address_1' => $request->address_1,
                 'address_2' => $request->address_2,
                 'latitude' => $request->latitude,
                 'longitude' => $request->longitude,

             ]);
         } catch (\Throwable $e) {
             dd($e);
             DB::rollBack();

             return false;
         }
         DB::commit();

         return new HostelResource($hostel);
     }

     public function show(Hostel $hostel)
     {
         return new HostelResource($hostel);
     }

     /**
      * @param  illuminate\Http\Request  $request
      * @return bool
      *
      * @throws \Throwable
      */
     public function update(Request $request, Hostel $hostel)
     {
         DB::beginTransaction();
         try {
             $hostel->update([
                 'name' => $request->name,
                 'address_1' => $request->address_1,
                 'address_2' => $request->address_2,
                 'latitude' => $request->latitude,
                 'longitude' => $request->longitude,

             ]);
         } catch (\Throwable $e) {
             DB::rollBack();

             return false;
         }
         DB::commit();

         return new HostelResource($hostel);
     }

     public function destroy(Hostel $hostel)
     {
         $hostel->delete();

         return response()->json('hostel successfully deleted');
     }
}
