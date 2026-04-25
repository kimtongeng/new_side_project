<?php

namespace Modules\Repair\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class MigrateDataController extends Controller
{
    public function migrate()
    {
        // Run only one specific migration file
        $path = base_path('Modules/Repair/Database/Migrations/2025_10_26_020016_add_more_permission.php');

        if (file_exists($path)) {
            Artisan::call('migrate', [
                '--path' => 'Modules/Repair/Database/Migrations/2025_10_26_020016_add_more_permission.php',
                '--force' => true,
            ]);

            return response()->json(['message' => 'Migration executed successfully']);
        }

        return response()->json(['error' => 'Migration file not found'], 404);
    }

    public function rollback()
    {
        // Rollback only this specific migration file
        $path = base_path('Modules/Repair/Database/Migrations/2025_10_26_020016_add_more_permission.php');

        if (file_exists($path)) {
            Artisan::call('migrate:rollback', [
                '--path' => 'Modules/Repair/Database/Migrations/2025_10_26_020016_add_more_permission.php',
                '--force' => true,
            ]);


            return response()->json(['message' => 'Rollback executed successfully']);
        }

        return response()->json(['error' => 'Migration file not found'], 404);
    }
}
