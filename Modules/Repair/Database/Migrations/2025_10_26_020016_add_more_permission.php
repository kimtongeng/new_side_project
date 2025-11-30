<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'repair.request_and_save',
            'repair.confirm',
            'repair.reject_and_note',
            'repair.show_only_available_part',
            'repair.view_part',
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $permission,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            'repair.request_and_save',
            'repair.confirm',
            'repair.reject_and_note',
            'repair.show_only_available_part',
            'repair.view_part',
        ])->delete();
    }
};
