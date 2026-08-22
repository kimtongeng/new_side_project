<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->text('hms_reason_for_trip')->nullable()->after('additional_notes');
            $table->string('hms_means_of_transport')->nullable()->after('hms_reason_for_trip');
            $table->string('hms_vehicle_registration_number')->nullable()->after('hms_means_of_transport');
            $table->string('hms_place_of_origin')->nullable()->after('hms_vehicle_registration_number');
            $table->string('hms_final_destination')->nullable()->after('hms_place_of_origin');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'hms_reason_for_trip',
                'hms_means_of_transport',
                'hms_vehicle_registration_number',
                'hms_place_of_origin',
                'hms_final_destination'
            ]);
        });
    }
};
