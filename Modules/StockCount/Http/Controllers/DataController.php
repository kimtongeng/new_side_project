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
            // Access & Visibility
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
                'value' => 'stock_count.audit_log',
                'label' => __('stockcount::lang.view_audit_logs'),
                'default' => false,
            ],

            // Session Management & Operations
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
                'value' => 'stock_count.update_status',
                'label' => 'Update Stock Count Status',
                'default' => false,
            ],

            // Counting & Worksheet Actions
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

            // Printing & Exporting
            [
                'value' => 'stock_count.export',
                'label' => __('stockcount::lang.export_excel_pdf'),
                'default' => false,
            ],
            [
                'value' => 'stock_count.print_blind',
                'label' => 'Print Blind Count Worksheet (User Level)',
                'default' => false,
            ],
            [
                'value' => 'stock_count.print_all',
                'label' => 'Print All / Full Worksheet (Admin & Permitted Users)',
                'default' => false,
            ],

            // Settings & Configuration
            [
                'value' => 'stock_count.settings',
                'label' => 'Stock Count Settings (Access Page)',
                'default' => false,
            ],
            [
                'value' => 'stock_count.settings_auto_adjust',
                'label' => 'Setting: Change Auto Adjust Stock',
                'default' => false,
            ],
            [
                'value' => 'stock_count.settings_approval',
                'label' => 'Setting: Change Approval & Locking',
                'default' => false,
            ],
            [
                'value' => 'stock_count.settings_counting',
                'label' => 'Setting: Change Counting & Blind Count Rules',
                'default' => false,
            ],
            [
                'value' => 'stock_count.settings_notifications',
                'label' => 'Setting: Change Notification Alerts',
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

        if (auth()->user()->can('stock_count.view_all') || auth()->user()->can('stock_count.view_own')) {
            $menu = Menu::instance('admin-sidebar-menu');

            $menu->url(
                action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'index']),
                __('stockcount::lang.stock_count'),
                [
                    'icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"></path>
                    <path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z"></path>
                    <path d="M9 14l2 2l4 -4"></path>
                  </svg>',
                    'active' => request()->segment(1) == 'stock-counts'
                ]
            )->order(36);
        }
    }
}
