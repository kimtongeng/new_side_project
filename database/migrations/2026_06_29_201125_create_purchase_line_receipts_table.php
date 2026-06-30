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
        Schema::create('purchase_line_receipts', function (Blueprint $table) {
            $table->id();
            $table->integer('transaction_id')->unsigned();
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->integer('purchase_line_id')->unsigned();
            $table->foreign('purchase_line_id')->references('id')->on('purchase_lines')->onDelete('cascade');
            $table->decimal('quantity', 22, 4);
            $table->dateTime('received_date');
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
        Schema::dropIfExists('purchase_line_receipts');
    }
};
