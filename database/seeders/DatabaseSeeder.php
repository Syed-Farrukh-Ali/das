<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RolesAndPermissionsTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(CampusSeeder::class);
        $this->call(StudentClassSeeder::class);
        $this->call(FeesTypeSeeder::class);
        $this->call(CourseSeeder::class);
        $this->call(GlobalSectionsSeeder::class);
        $this->call(SessionsSeeder::class);
        $this->call(FeeStructureSeeder::class);
        $this->call(DesignationSeeder::class);
        $this->call(JobStatusSeeder::class);
        $this->call(GlobalBankSeeder::class);
        $this->call(BaseAccountSeeder::class);
        $this->call(AccountGroupSeeder::class);
        $this->call(AccountChartSeeder::class);
        $this->call(VoucherTypeSeeder::class);
        $this->call(PayScaleSeeder::class);
        $this->call(ExamTypeSeeder::class);
        $this->call(SubjectSeeder::class);
        $this->call(AttendanceStatusSeeder::class);
        $this->call(NotificationTypeSeeder::class);
        $this->call(SmsTypeSeeder::class);
    }
}
