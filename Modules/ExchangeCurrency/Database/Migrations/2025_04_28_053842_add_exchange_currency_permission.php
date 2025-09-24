<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    
            Permission::create(['name' => 'exchange_currency.create']);
            Permission::create(['name' => 'exchange_currency.update']);
            Permission::create(['name' => 'exchange_currency.view']);
            Permission::create(['name' => 'exchange_currency.delete']);
    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('', function (Blueprint $table) {});
    }
};
