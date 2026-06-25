<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class AddStockCountPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissions = [
            'stock_count.view',
            'stock_count.create',
            'stock_count.edit',
            'stock_count.delete',
            'stock_count.count',
            'stock_count.scan_barcode',
            'stock_count.review',
            'stock_count.reconcile',
            'stock_count.complete',
            'stock_count.export',
            'stock_count.audit_log'
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
            'stock_count.view',
            'stock_count.create',
            'stock_count.edit',
            'stock_count.delete',
            'stock_count.count',
            'stock_count.scan_barcode',
            'stock_count.review',
            'stock_count.reconcile',
            'stock_count.complete',
            'stock_count.export',
            'stock_count.audit_log'
        ];

        foreach ($permissions as $permission) {
            Permission::where('name', $permission)->delete();
        }
    }
}
