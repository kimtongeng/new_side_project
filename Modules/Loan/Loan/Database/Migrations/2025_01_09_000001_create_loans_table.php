<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();

            // الربط مع الجداول الأخرى
            $table->unsignedInteger('business_id');
            $table->foreign('business_id')
                  ->references('id')->on('business')
                  ->onDelete('cascade');

            $table->unsignedInteger('customer_id');
            $table->foreign('customer_id')
                  ->references('id')->on('contacts')
                  ->onDelete('cascade');

            $table->unsignedInteger('location_id');
            $table->foreign('location_id')
                  ->references('id')->on('business_locations')
                  ->onDelete('cascade');

            $table->unsignedInteger('account_id');
            $table->foreign('account_id')
                  ->references('id')->on('accounts')
                  ->onDelete('cascade');

            // بيانات القرض
            $table->date('start_date');                  // تاريخ بداية القرض
            $table->decimal('amount', 15, 2);            // مبلغ القرض
            $table->integer('duration');                 // مدة القرض بالأشهر
            $table->decimal('interest_rate', 5, 2);      // نسبة الفائدة
            $table->string('loan_type');                 // نوع القرض
            $table->text('description')->nullable();     // وصف القرض
            $table->string('status');                    // حالة القرض

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
        Schema::dropIfExists('loans');
    }
}
