<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchange_currency', function (Blueprint $table) {
            $table->id();
            $table->string('country', 100);
            $table->string('currency', 100);
            $table->string('code', 25);
            $table->string('symbol', 25);
            $table->decimal('exchange_rate',15,8)->unsigned();
            $table->tinyInteger("is_use")->default(0);
            $table->unsignedBigInteger("business_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchange_currency');
        
    }
};
