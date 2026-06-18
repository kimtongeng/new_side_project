<?php

   use Illuminate\Support\Facades\DB;
   use Illuminate\Database\Migrations\Migration;

   class MakeCustomerIdNullableInLoansTable extends Migration
   {
       /**
        * Run the migrations.
        *
        * @return void
        */
       public function up()
       {
           // تعديل العمود باستخدام SQL مباشر
           DB::statement('ALTER TABLE loans MODIFY customer_id INTEGER UNSIGNED NULL');
       }

       /**
        * Reverse the migrations.
        *
        * @return void
        */
       public function down()
       {
           // إعادة العمود إلى غير اختياري
           DB::statement('ALTER TABLE loans MODIFY customer_id INTEGER UNSIGNED NOT NULL');
       }
   }