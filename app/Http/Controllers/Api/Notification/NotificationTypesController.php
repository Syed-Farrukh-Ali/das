<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Notification\NotificationTypeResource;
use App\Models\Notification\NotificationType;

class NotificationTypesController extends BaseController
{
    public function index()
    {
        $notification_types = NotificationType::all();
        $resource = NotificationTypeResource::collection($notification_types);

        return $this->sendResponse($resource,[]);
    }

    public function show(NotificationType $notification_type)
    {
        $resource = new NotificationTypeResource($notification_type);

        return $this->sendResponse($resource,[]);
    }
}
