<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class AddDetailedStockCountPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissions = [
            'stock_count.settings_auto_adjust',
            'stock_count.settings_approval',
            'stock_count.settings_counting',
            'stock_count.settings_notifications',
            'stock_count.print_blind',
            'stock_count.print_all',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permissions = [
            'stock_count.settings_auto_adjust',
            'stock_count.settings_approval',
            'stock_count.settings_counting',
            'stock_count.settings_notifications',
            'stock_count.print_blind',
            'stock_count.print_all',
        ];

        foreach ($permissions as $permission) {
            Permission::where('name', $permission)->delete();
        }
    }
}
