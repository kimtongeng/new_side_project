<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockCountLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_count_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_count_session_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('variation_id');
            $table->decimal('book_quantity', 22, 4)->default(0.0000);
            $table->decimal('counted_quantity', 22, 4)->default(0.0000);
            $table->decimal('unit_price', 22, 4)->default(0.0000); // Purchase price for financial impact
            $table->text('note')->nullable();
            $table->unsignedInteger('counted_by')->nullable();
            $table->timestamp('counted_at')->nullable();
            $table->timestamps();

            $table->foreign('stock_count_session_id')->references('id')->on('stock_count_sessions')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('variation_id')->references('id')->on('variations')->onDelete('cascade');
            $table->foreign('counted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_count_lines');
    }
}
