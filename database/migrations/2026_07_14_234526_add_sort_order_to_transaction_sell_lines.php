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
        if (!Schema::hasColumn('transaction_sell_lines', 'sort_order')) {
            Schema::table('transaction_sell_lines', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('tax_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('transaction_sell_lines', 'sort_order')) {
            Schema::table('transaction_sell_lines', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
