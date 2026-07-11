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
        if (!Schema::hasColumn('product_variations', 'sort_order')) {
            Schema::table('product_variations', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('is_dummy');
            });
        }
        if (!Schema::hasColumn('variations', 'sort_order')) {
            Schema::table('variations', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('sell_price_inc_tax');
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
        if (Schema::hasColumn('product_variations', 'sort_order')) {
            Schema::table('product_variations', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
        if (Schema::hasColumn('variations', 'sort_order')) {
            Schema::table('variations', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
