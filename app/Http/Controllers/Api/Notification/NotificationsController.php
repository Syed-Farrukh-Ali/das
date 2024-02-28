<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Notification\NotificationRequest;
use App\Http\Resources\Notification\NotificationResource;
use App\Models\Campus;
use App\Models\HeadOffice;
use App\Models\Notification\Notification;
use App\Models\StaffMember;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\TryCatch;

class NotificationsController extends BaseController
{
    public function index()
    {
        $notifications = Notification::latest()->get();
        $resource = NotificationResource::collection($notifications);

        return $this->sendResponse($resource,[]);
    }

    public function store(NotificationRequest $request)
    {
        $notification = Notification::create([
            'notification_type_id' => $request->notification_type_id,
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'date_time' => Carbon::now(),
            'campus_ids' => $request->campus_ids,
        ]);
        $notification->campuses()->attach($request->campus_ids);
        $response = new NotificationResource($notification);

        return $this->sendResponse($response, 'Notification created successfully.');
    }

    public function update(NotificationRequest $request, Notification  $notification)
    {
        $notification->update([
            'notification_type_id' => $request->notification_type_id,
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'date_time' => Carbon::now(),
            'campus_ids' => $request->campus_ids,
        ]);
        $notification->campuses()->attach($request->campus_ids);
        $response = new NotificationResource($notification);

        return $this->sendResponse($response, 'Notification updated successfully.');
    }

    public function destroy(Notification  $notification)
    {
        $notification->delete();

        return $this->sendResponse([],  'Notification deleted successfully.');
    }

    public function campusNotifications(Campus $campus)
    {
        $notifications = $campus->notifications()->latest()->get();

        $response = NotificationResource::collection($notifications);

        return $this->sendResponse($response, []);
    }

    public function studentsNotifications()
    {
        $user = Auth::user();

        if ($user->role('student'))
        {
            if ($user->student->campus_id)
            {
                $campus = Campus::find($user->student->campus_id);
                $notifications = $campus->notifications()->latest()->get();

                $response = NotificationResource::collection($notifications);

                return $this->sendResponse($response, []);
            } else{
                return $this->sendResponse([],  'Sorry! you do not have campus');
            }
        } else {
            return $this->sendResponse([],  'Sorry! you are not a student');
        }
    }
    public function databaseRefactor()
    {
        DB::beginTransaction();
        try {



            $students = Student::where('user_id','!=',null)->get();
            foreach ($students as $key => $student) {
             $student->user()->update(['campus_id'=>$student->campus_id]);
            }


            $campuses = Campus::get();
            foreach ($campuses as $key => $campus)
            {
             $campus->user()->update(['campus_id'=> $campus->id]);
            }

            $staffmembers =  StaffMember::get();
            foreach ($staffmembers as $key => $staffmember) {
             $staffmember->user()->update(['campus_id'=>$staffmember->campus_id]);
            }

            $headoffices = HeadOffice::get();
            foreach ($headoffices as $key => $headoffice) {
             $headoffice->user()->update(['campus_id'=>null]);
            }

        } catch (\Throwable $th) {

            DB::rollBack();
            return dd($th) ;
        }

        DB::commit();
        return true;
    }
}
