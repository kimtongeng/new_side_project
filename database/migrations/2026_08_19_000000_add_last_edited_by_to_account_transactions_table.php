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
        Schema::table('account_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('account_transactions', 'last_edited_by')) {
                $table->integer('last_edited_by')->nullable()->after('created_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('account_transactions', 'last_edited_by')) {
                $table->dropColumn('last_edited_by');
            }
        });
    }
};
