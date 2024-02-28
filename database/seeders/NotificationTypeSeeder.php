<?php

namespace Database\Seeders;

use App\Models\Notification\NotificationType;
use Illuminate\Database\Seeder;

class NotificationTypeSeeder extends Seeder
{
    public function run()
    {
        NotificationType::create([
            'name' => "general",
        ]);

        NotificationType::create([
            'name' => "vacation",
        ]);

        NotificationType::create([
            'name' => "exam",
        ]);

        NotificationType::create([
            'name' => "test",
        ]);

        NotificationType::create([
            'name' => "result",
        ]);

        NotificationType::create([
            'name' => "fees",
        ]);

        NotificationType::create([
            'name' => "calendar",
        ]);

    }
}
