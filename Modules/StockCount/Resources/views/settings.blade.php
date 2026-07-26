@extends('layouts.app')
@section('title', 'Stock Count Settings')

@section('css')
    <style>
        .settings-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            border: 1px solid #eaeaea;
            overflow: hidden;
        }

        .settings-card-header {
            padding: 18px 24px;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
        }

        .settings-card-header i {
            margin-right: 12px;
            font-size: 20px;
        }

        .header-blue {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
        }

        .header-green {
            background: linear-gradient(135deg, #11998e, #38ef7d);
        }

        .header-red {
            background: linear-gradient(135deg, #d32f2f, #ef5350);
        }

        .settings-card-body {
            padding: 10px 24px;
        }

        .setting-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .setting-info {
            flex: 1;
            padding-right: 25px;
        }

        .setting-title {
            margin: 0 0 6px 0;
            font-size: 16px;
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: center;
        }

        .setting-title i {
            margin-right: 10px;
            font-size: 17px;
            width: 20px;
            text-align: center;
        }

        .setting-desc {
            margin: 0;
            font-size: 13px;
            color: #7f8c8d;
            line-height: 1.4;
        }

        .setting-divider {
            margin: 0;
            border-top: 1px solid #f1f2f6;
        }

        /* Pure CSS Toggle Switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
            margin: 0;
        }

        .switch input { 
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #dcdde1;
            transition: .3s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        }

        input:checked + .slider {
            background-color: #2ecc71;
        }

        input:checked + .slider:before {
            transform: translateX(24px);
        }

        /* Form Controls */
        .settings-select {
            width: 180px;
            height: 38px;
            border-radius: 8px;
            border: 1px solid #dcdde1;
            padding: 0 12px;
            font-size: 14px;
            color: #2c3e50 !important;
            background-color: #ffffff !important;
            outline: none;
            transition: border-color 0.2s;
        }

        .settings-select option {
            background-color: #ffffff !important;
            color: #2c3e50 !important;
        }

        .settings-select:focus {
            border-color: #3498db;
        }

        .settings-input-group {
            display: flex;
            align-items: center;
        }

        .settings-input {
            width: 100px;
            height: 38px;
            border-radius: 8px 0 0 8px;
            border: 1px solid #dcdde1;
            border-right: none;
            padding: 0 12px;
            font-size: 14px;
            text-align: center;
            color: #2c3e50 !important;
            background-color: #ffffff !important;
            outline: none;
        }

        .settings-input-addon {
            height: 38px;
            padding: 0 12px;
            background: #f1f2f6;
            border: 1px solid #dcdde1;
            border-radius: 0 8px 8px 0;
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #7f8c8d;
            font-weight: bold;
        }

        .btn-save-settings {
            background: #1a73e8;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            padding: 12px 30px;
            border-radius: 30px;
            border: none;
            box-shadow: 0 4px 15px rgba(26, 115, 232, 0.3);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-save-settings:hover:not([disabled]) {
            background: #1557b0;
            box-shadow: 0 6px 20px rgba(26, 115, 232, 0.4);
            transform: translateY(-1px);
        }

        .btn-save-settings[disabled] {
            background: #95a5a6 !important;
            box-shadow: none !important;
            cursor: not-allowed !important;
            opacity: 0.6;
            transform: none !important;
        }

        .switch input[disabled] + .slider {
            background-color: #e0e0e0 !important;
            cursor: not-allowed !important;
            opacity: 0.6;
        }
        
        .switch input[disabled]:checked + .slider {
            background-color: #a8e6cf !important;
            cursor: not-allowed !important;
        }

        .settings-select[disabled],
        .settings-input[disabled] {
            background-color: #f5f5f5 !important;
            color: #95a5a6 !important;
            cursor: not-allowed !important;
        }

        .setting-help-icon {
            margin-left: 8px;
            font-size: 15px;
            color: #3498db;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-block;
        }

        .setting-help-icon:hover {
            color: #1d6fa5;
            transform: scale(1.2);
        }

        .popover {
            box-shadow: 0 6px 25px rgba(0,0,0,0.15);
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            max-width: 320px;
        }

        .popover-title {
            font-weight: 700;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
            font-size: 14px;
            padding: 10px 14px;
        }

        .popover-content {
            font-size: 13px;
            color: #475569;
            line-height: 1.5;
            padding: 12px 14px;
        }
    </style>
@endsection

@section('content')
    @php
        $business_id = session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#' . $business_id) || auth()->user()->can('superadmin');
        $has_master_settings = $is_admin || auth()->user()->can('stock_count.settings');

        $can_edit_auto_adjust = $has_master_settings || auth()->user()->can('stock_count.settings_auto_adjust');
        $can_edit_approval = $has_master_settings || auth()->user()->can('stock_count.settings_approval');
        $can_edit_counting = $has_master_settings || auth()->user()->can('stock_count.settings_counting');
        $can_edit_notifications = $has_master_settings || auth()->user()->can('stock_count.settings_notifications');

        $can_save_settings = $can_edit_auto_adjust || $can_edit_approval || $can_edit_counting || $can_edit_notifications;

        $auto_adjust = isset($settings['stock_count_auto_adjust_stock']) ? $settings['stock_count_auto_adjust_stock'] : false;
        $require_approval = isset($settings['stock_count_require_approval']) ? $settings['stock_count_require_approval'] : true;
        $allow_delete_completed = isset($settings['stock_count_allow_delete_completed']) ? $settings['stock_count_allow_delete_completed'] : false;
        $allow_recount = isset($settings['stock_count_allow_recount']) ? $settings['stock_count_allow_recount'] : true;
        $show_expected = isset($settings['stock_count_show_expected_qty']) ? $settings['stock_count_show_expected_qty'] : true;
        $default_blind_count = isset($settings['stock_count_default_blind_count']) ? $settings['stock_count_default_blind_count'] : false;
        $auto_complete_on_100 = isset($settings['stock_count_auto_complete_on_100']) ? $settings['stock_count_auto_complete_on_100'] : false;
        $skip_zero = isset($settings['stock_count_skip_zero_stock']) ? $settings['stock_count_skip_zero_stock'] : false;

        $notify_created = isset($settings['stock_count_telegram_notify_created']) ? $settings['stock_count_telegram_notify_created'] : true;
        $notify_completed = isset($settings['stock_count_telegram_notify_completed']) ? $settings['stock_count_telegram_notify_completed'] : true;
        $notify_reconciled = isset($settings['stock_count_telegram_notify_reconciled']) ? $settings['stock_count_telegram_notify_reconciled'] : true;
        $notify_cancelled = isset($settings['stock_count_telegram_notify_cancelled']) ? $settings['stock_count_telegram_notify_cancelled'] : true;
    @endphp

    <section class="content-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Stock Count Settings</h1>
        <a href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'index']) }}" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </section>

    <section class="content">
        {!! Form::open(['url' => action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'postSettings']), 'method' => 'post', 'id' => 'stock_count_settings_form']) !!}
        
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                
                <!-- 1. Stock Adjustment Settings -->
                <div class="settings-card">
                    <div class="settings-card-header header-blue">
                        <i class="fa fa-exchange-alt"></i> Stock Adjustment Settings
                    </div>
                    <div class="settings-card-body">
                        <!-- Auto Adjust Stock -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title">
                                    <i class="fa fa-magic text-primary"></i> Auto Adjust Stock
                                    <i class="fa fa-question-circle setting-help-icon" data-toggle="popover" data-trigger="click" data-placement="auto top" data-html="true" title="Auto Adjust Stock" data-content="When enabled, system inventory levels will be automatically updated and stock adjustment transactions created immediately upon stocktake approval (or completion if approval is disabled)."></i>
                                </h4>
                                <p class="setting-desc">Automatically adjust stock quantities when a stocktake is approved based on detected discrepancies</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_auto_adjust_stock" value="1" @if($auto_adjust) checked @endif @if(!$can_edit_auto_adjust) disabled @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="setting-divider"></div>

                        <!-- Require Approval -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title">
                                    <i class="fa fa-user-shield text-warning"></i> Require Approval
                                    <i class="fa fa-question-circle setting-help-icon" data-toggle="popover" data-trigger="click" data-placement="auto top" data-html="true" title="Require Approval" data-content="Requires a manager or supervisor to review and approve the count session before inventory quantities are adjusted in the system."></i>
                                </h4>
                                <p class="setting-desc">Manager approval required before stock adjustment</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_require_approval" value="1" @if($require_approval) checked @endif @if(!$can_edit_approval) disabled @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="setting-divider"></div>

                        <!-- Allow Deleting Completed Sessions -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title">
                                    <i class="fa fa-trash-alt text-danger"></i> Allow Deleting Completed Sessions
                                    <i class="fa fa-question-circle setting-help-icon" data-toggle="popover" data-trigger="click" data-placement="auto top" data-html="true" title="Allow Deleting Completed Sessions" data-content="When enabled, authorized users can delete stock count sessions that are in Completed status. When disabled, Completed sessions are protected from deletion."></i>
                                </h4>
                                <p class="setting-desc">Allow stock count sessions with Completed status to be deleted</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_allow_delete_completed" value="1" @if($allow_delete_completed) checked @endif @if(!$can_edit_approval) disabled @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Counting Settings -->
                <div class="settings-card">
                    <div class="settings-card-header header-green">
                        <i class="fa fa-calculator"></i> Counting Settings
                    </div>
                    <div class="settings-card-body">
                        <!-- Allow Recount -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title">
                                    <i class="fa fa-sync text-info"></i> Allow Recount
                                    <i class="fa fa-question-circle setting-help-icon" data-toggle="popover" data-trigger="click" data-placement="auto top" data-html="true" title="Allow Recount" data-content="Permits counting staff to edit and recount previously entered item quantities on the worksheet while the session is active."></i>
                                </h4>
                                <p class="setting-desc">Allow users to recount previously counted items</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_allow_recount" value="1" @if($allow_recount) checked @endif @if(!$can_edit_counting) disabled @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="setting-divider"></div>

                        <!-- Show Expected Qty -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title">
                                    <i class="fa fa-eye text-muted"></i> Show Expected Qty
                                    <i class="fa fa-question-circle setting-help-icon" data-toggle="popover" data-trigger="click" data-placement="auto top" data-html="true" title="Show Expected Qty" data-content="Displays the current system stock on hand (QOH) on the counting worksheet for counters to see expected quantities."></i>
                                </h4>
                                <p class="setting-desc">Show system quantity during counting</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_show_expected_qty" value="1" @if($show_expected) checked @endif @if(!$can_edit_counting) disabled @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="setting-divider"></div>

                        <!-- Auto Complete When 100% Counted -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title">
                                    <i class="fa fa-check-double text-success"></i> Auto Complete When 100% Counted
                                    <i class="fa fa-question-circle setting-help-icon" data-toggle="popover" data-trigger="click" data-placement="auto top" data-html="true" title="Auto Complete When 100% Counted" data-content="Automatically transitions the session status from In Progress to Completed as soon as physical counts have been entered for all items."></i>
                                </h4>
                                <p class="setting-desc">Automatically set session status to Completed when all items have been counted</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_auto_complete_on_100" value="1" @if($auto_complete_on_100) checked @endif @if(!$can_edit_counting) disabled @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="setting-divider"></div>

                        <!-- Blind Count Mode -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title">
                                    <i class="fa fa-eye-slash text-danger"></i> Blind Count Mode
                                    <i class="fa fa-question-circle setting-help-icon" data-toggle="popover" data-trigger="click" data-placement="auto top" data-html="true" title="Blind Count Mode" data-content="Enables blind counting by default, hiding system stock quantities from counting staff so they conduct unbiased physical counts without seeing expected numbers."></i>
                                </h4>
                                <p class="setting-desc">Enable Blind Count Mode by default for new stocktakes (hides system QOH from counting staff to ensure unbiased results)</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_default_blind_count" value="1" @if($default_blind_count) checked @endif @if(!$can_edit_counting) disabled @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="setting-divider"></div>

                        <!-- Skip Zero Stock Products -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title">
                                    <i class="fa fa-filter text-warning"></i> Skip Zero Stock Products
                                    <i class="fa fa-question-circle setting-help-icon" data-toggle="popover" data-trigger="click" data-placement="auto top" data-html="true" title="Skip Zero Stock Products" data-content="Excludes products with zero stock on hand from automatically appearing in newly generated full count worksheets."></i>
                                </h4>
                                <p class="setting-desc">Exclude products with zero stock when creating a new stocktake</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_skip_zero_stock" value="1" @if($skip_zero) checked @endif @if(!$can_edit_counting) disabled @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Telegram Notification Settings -->
                <div class="settings-card">
                    <div class="settings-card-header header-red">
                        <i class="fa fa-paper-plane"></i> Telegram Notification Settings
                    </div>
                    <div class="settings-card-body">
                        <!-- Notify on Session Created -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title">
                                    <i class="fa fa-plus-circle text-primary"></i> Notify on Session Created
                                    <i class="fa fa-question-circle setting-help-icon" data-toggle="popover" data-trigger="click" data-placement="auto top" data-html="true" title="Notify on Session Created" data-content="Sends a Telegram message alert whenever a new stock count session is created."></i>
                                </h4>
                                <p class="setting-desc">Send Telegram notification when a new stock count session is created</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_telegram_notify_created" value="1" @if($notify_created) checked @endif @if(!$can_edit_notifications) disabled @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="setting-divider"></div>

                        <!-- Notify on Session Completed -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title">
                                    <i class="fa fa-check-circle text-success"></i> Notify on Session Completed
                                    <i class="fa fa-question-circle setting-help-icon" data-toggle="popover" data-trigger="click" data-placement="auto top" data-html="true" title="Notify on Session Completed" data-content="Sends a Telegram message alert when a stock count session status changes to Completed."></i>
                                </h4>
                                <p class="setting-desc">Send Telegram notification when a stock count session is completed</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_telegram_notify_completed" value="1" @if($notify_completed) checked @endif @if(!$can_edit_notifications) disabled @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="setting-divider"></div>

                        <!-- Notify on Session Reconciled -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title">
                                    <i class="fa fa-sync text-info"></i> Notify on Session Reconciled
                                    <i class="fa fa-question-circle setting-help-icon" data-toggle="popover" data-trigger="click" data-placement="auto top" data-html="true" title="Notify on Session Reconciled" data-content="Sends a Telegram message alert when stock reconciliation is finalized and stock levels are updated."></i>
                                </h4>
                                <p class="setting-desc">Send Telegram notification when stock count adjustments are reconciled</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_telegram_notify_reconciled" value="1" @if($notify_reconciled) checked @endif @if(!$can_edit_notifications) disabled @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="setting-divider"></div>

                        <!-- Notify on Session Cancelled -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title">
                                    <i class="fa fa-times-circle text-danger"></i> Notify on Session Cancelled
                                    <i class="fa fa-question-circle setting-help-icon" data-toggle="popover" data-trigger="click" data-placement="auto top" data-html="true" title="Notify on Session Cancelled" data-content="Sends a Telegram message alert when a stock count session is cancelled or rejected."></i>
                                </h4>
                                <p class="setting-desc">Send Telegram notification when a stock count session is cancelled</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_telegram_notify_cancelled" value="1" @if($notify_cancelled) checked @endif @if(!$can_edit_notifications) disabled @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit & Back Buttons -->
                <div class="text-center" style="margin-top: 10px; margin-bottom: 50px; display: flex; justify-content: center; align-items: center; gap: 15px;">
                    <a href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'index']) }}" class="btn btn-default" style="padding: 10px 24px; font-size: 15px; font-weight: 600; border-radius: 6px;">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn-save-settings" @if(!$can_save_settings) disabled @endif>
                        <i class="fa fa-save"></i> Save Settings
                    </button>
                </div>

            </div>
        </div>

        {!! Form::close() !!}
    </section>
@endsection

@section('javascript')
<script>
$(document).ready(function(){
    $('[data-toggle="popover"]').popover({
        trigger: 'click',
        placement: 'auto top',
        html: true,
        container: 'body'
    });

    // Close popovers when clicking outside
    $(document).on('click', function (e) {
        $('[data-toggle="popover"]').each(function () {
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                $(this).popover('hide');
            }
        });
    });
});
</script>
@endsection
