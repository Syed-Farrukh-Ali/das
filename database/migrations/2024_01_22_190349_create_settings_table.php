<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->integer('late_fee_fine')->default(15);
            $table->string('unit_name')->nullable();
            $table->integer('gp_fund_years')->default(5);
            $table->string('director_number')->nullable();
            $table->boolean('alphanumeric_adm_no')->default(0);
            $table->boolean('director_sign')->default(0);
            $table->boolean('send_message')->default(0);
            $table->string('sms_api_login')->nullable();
            $table->string('sms_api_password')->nullable();
            $table->string('logo_file')->nullable();
            $table->string('start_logo_file')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        $timestamp = now();

        DB::table('settings')->insert([
            'late_fee_fine' => 15, 'gp_fund_years' => 5, 'unit_name' => NULL, 'director_number' => NULL, 'alphanumeric_adm_no' => 0,
            'director_sign' => 0, 'send_message' => 0, 'sms_api_login' => NULL, 'sms_api_password' => NULL, 'logo_file' => NULL, 'start_logo_file' => NULL, 'deleted_at' => NULL,
            'created_at' => $timestamp, 'updated_at' => $timestamp
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
