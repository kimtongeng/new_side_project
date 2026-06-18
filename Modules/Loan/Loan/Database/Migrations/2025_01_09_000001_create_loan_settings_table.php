<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_settings', function (Blueprint $table) {
            $table->id();

            // استخدم unsignedInteger ليطابق نوع عمود id في جدول business
            $table->unsignedInteger('business_id');
            $table->foreign('business_id')
                  ->references('id')
                  ->on('business')
                  ->onDelete('cascade');

            $table->decimal('interest_rate', 8, 2)->default(0); // نسبة الفائدة
            $table->decimal('loan_limit', 10, 2)->default(0); // الحد الأقصى للقروض
            $table->integer('max_loan_duration')->default(12); // مدة القرض القصوى بالأشهر
            $table->decimal('administrative_fee', 8, 2)->default(0); // الرسوم الإدارية
            $table->enum('interest_type', ['none', 'simple', 'compound'])->default('none'); // نوع الفائدة
            $table->boolean('allow_early_payment')->default(true); // السماح بالدفع المسبق

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
        Schema::dropIfExists('loan_settings');
    }
}
