<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUpdateSessionIdInFeechallanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add the session_id column
        Schema::table('fee_challans', function (Blueprint $table) {
            $table->unsignedBigInteger('session_id')->after('id')->nullable();
            $table->index('session_id');
        });

        // Set the default value of '5' for the session_id column in the feechallan table
        DB::table('fee_challans')->update(['session_id' => 5]);

        // Add a foreign key constraint
        // Schema::table('fee_challans', function (Blueprint $table) {
        //     $table->foreign('session_id')->references('id')->on('sessions')->onDelete('set null');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove the session_id column if needed
        Schema::table('fee_challans', function (Blueprint $table) {
            $table->dropColumn('session_id');
        });
    }
}
