<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\SupportRequest;
use App\Http\Resources\SupportResource;
use App\Models\Support;
use Illuminate\Support\Facades\Storage;

class SupportController extends BaseController
{
    public function index()
    {
        $supports = Support::latest()->get();

        $response = SupportResource::collection($supports);

        return $this->sendResponse($response,[],200);
    }

    public function store(SupportRequest $request)
    {

        try {
            $support = Support::updateOrCreate([
                'campus_id' => $request->campus_id
            ], [
                'email' => $request->email,
                'phone' => $request->phone,
            ]);

            if ($request->has('file')){
                if (Storage::exists('campus_support')) {
                    Storage::delete('campus_support'.'/'.$support->file);
                }else{
                    Storage::makeDirectory('campus_support');
                }

                $pathname = Storage::disk('campus_support')->put('', $request->file);

                $support->update(['file' => $pathname]);
            }

            $response = new SupportResource($support);

            return $this->sendResponse($response,'Support added successfully',200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function destroy(Support $support){
        $support->delete();

        return $this->sendResponse([],'Support deleted successfully');
    }

    public function campusSupport(){
        $user = _user();

        $support = null;

        if ($user->campus_id){
            $support = Support::where('campus_id',$user->campus_id)->first();
        }

        if ($support){
            $response = new SupportResource($support);
        }else{
            $response = [];
        }

        return $this->sendResponse($response,[],200);
    }
}
