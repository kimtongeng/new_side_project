<?php

namespace Modules\Loan\Http\Controllers;

use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Menu;

class DataController extends Controller
{
    /**
     * Superadmin package permissions
     *
     * @return array
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'Loan_module',
                'label' => __('Loan::lang.Loan_module'),
                'default' => false,
            ],
        ];
    }

    /**
     * Adds Loan menus
     *
     * @return null
     */
    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        $module_util = new ModuleUtil();

        $is_Loan_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'Loan_module');

        $commonUtil = new Util();
        $is_admin = $commonUtil->is_admin(auth()->user(), $business_id);

        if ($is_Loan_enabled) {
            $menu = Menu::instance('admin-sidebar-menu');
            $menu->url(
                action([\Modules\Loan\Http\Controllers\LoanController::class, 'dashboard']),
                __('Loan::lang.Loan_module'),
                ['icon' => 'fa fas fa-dollar-sign', 'active' => request()->segment(1) == 'Loan']
            )->order(70);
        }
    }
}