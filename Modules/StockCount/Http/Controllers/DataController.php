<?php

namespace Modules\StockCount\Http\Controllers;

use App\Utils\ModuleUtil;
use Illuminate\Routing\Controller;
use Menu;

class DataController extends Controller
{
    /**
     * Define user permissions for the module.
     *
     * @return array
     */
    public function user_permissions()
    {
        return [
            [
                'value' => 'stock_count.view',
                'label' => __('stockcount::lang.view_stock_count_session'),
                'default' => false,
            ],
            [
                'value' => 'stock_count.view_own',
                'label' => __('stockcount::lang.view_own_stock_count'),
                'default' => false,
            ],
            [
                'value' => 'stock_count.view_all',
                'label' => __('stockcount::lang.view_all_stock_count'),
                'default' => false,
            ],
            [
                'value' => 'stock_count.create',
                'label' => __('stockcount::lang.create_stock_count_session'),
                'default' => false,
            ],
            [
                'value' => 'stock_count.edit',
                'label' => __('stockcount::lang.edit_stock_count_session'),
                'default' => false,
            ],
            [
                'value' => 'stock_count.delete',
                'label' => __('stockcount::lang.delete_stock_count_session'),
                'default' => false,
            ],
            [
                'value' => 'stock_count.count',
                'label' => __('stockcount::lang.count_quantities'),
                'default' => false,
            ],
            [
                'value' => 'stock_count.scan_barcode',
                'label' => __('stockcount::lang.scan_barcode'),
                'default' => false,
            ],
            [
                'value' => 'stock_count.review',
                'label' => __('stockcount::lang.review_count_results'),
                'default' => false,
            ],
            [
                'value' => 'stock_count.reconcile',
                'label' => __('stockcount::lang.reconcile_adjustments'),
                'default' => false,
            ],
            [
                'value' => 'stock_count.complete',
                'label' => __('stockcount::lang.complete_stock_count'),
                'default' => false,
            ],
            [
                'value' => 'stock_count.export',
                'label' => __('stockcount::lang.export_excel_pdf'),
                'default' => false,
            ],
            [
                'value' => 'stock_count.audit_log',
                'label' => __('stockcount::lang.view_audit_logs'),
                'default' => false,
            ],
        ];
    }

    /**
     * Adds Stock Count menus
     *
     * @return null
     */
    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        $module_util = new ModuleUtil();

        if (auth()->user()->can('stock_count.view') || auth()->user()->can('stock_count.view_all') || auth()->user()->can('stock_count.view_own')) {
            $menu = Menu::instance('admin-sidebar-menu');
            $added = false;

            $menu->whereTitle(__('lang_v1.stock_transfers'), function ($sub) use (&$added) {
                if (!empty($sub)) {
                    $sub->url(
                        action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'index']),
                        __('stockcount::lang.stock_count'),
                        ['icon' => '', 'active' => request()->segment(1) == 'stock-counts']
                    );
                    $added = true;
                }
            });

            if (!$added) {
                $menu->url(
                    action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'index']),
                    __('stockcount::lang.stock_count'),
                    ['icon' => 'fa fas fa-clipboard-list', 'active' => request()->segment(1) == 'stock-counts']
                )->order(26);
            }
        }
    }
}
