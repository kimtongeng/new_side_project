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
        }

        .btn-save-settings:hover {
            background: #1557b0;
            box-shadow: 0 6px 20px rgba(26, 115, 232, 0.4);
            transform: translateY(-1px);
        }
    </style>
@endsection

@section('content')
    @php
        $auto_adjust = isset($settings['stock_count_auto_adjust_stock']) ? $settings['stock_count_auto_adjust_stock'] : false;
        $require_approval = isset($settings['stock_count_require_approval']) ? $settings['stock_count_require_approval'] : true;
        $lock_after = isset($settings['stock_count_lock_after_approval']) ? $settings['stock_count_lock_after_approval'] : true;
        $allow_recount = isset($settings['stock_count_allow_recount']) ? $settings['stock_count_allow_recount'] : true;
        $show_expected = isset($settings['stock_count_show_expected_qty']) ? $settings['stock_count_show_expected_qty'] : true;
        $default_type = isset($settings['stock_count_default_count_type']) ? $settings['stock_count_default_count_type'] : 'full_count';
        $skip_zero = isset($settings['stock_count_skip_zero_stock']) ? $settings['stock_count_skip_zero_stock'] : false;
        $notify_completion = isset($settings['stock_count_notify_on_completion']) ? $settings['stock_count_notify_on_completion'] : false;
        $notify_discrepancies = isset($settings['stock_count_notify_on_large_discrepancies']) ? $settings['stock_count_notify_on_large_discrepancies'] : false;
        $threshold = isset($settings['stock_count_discrepancy_threshold']) ? $settings['stock_count_discrepancy_threshold'] : 0;
    @endphp

    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Stock Count Settings</h1>
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
                                <h4 class="setting-title"><i class="fa fa-magic text-primary"></i> Auto Adjust Stock</h4>
                                <p class="setting-desc">Automatically adjust stock quantities when a stocktake is approved based on detected discrepancies</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_auto_adjust_stock" value="1" @if($auto_adjust) checked @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="setting-divider"></div>

                        <!-- Require Approval -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title"><i class="fa fa-user-shield text-warning"></i> Require Approval</h4>
                                <p class="setting-desc">Manager approval required before stock adjustment</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_require_approval" value="1" @if($require_approval) checked @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="setting-divider"></div>

                        <!-- Lock After Approval -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title"><i class="fa fa-lock text-danger"></i> Lock After Approval</h4>
                                <p class="setting-desc">Prevent any modifications to the stocktake after approval and stock adjustment</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_lock_after_approval" value="1" @if($lock_after) checked @endif>
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
                                <h4 class="setting-title"><i class="fa fa-sync text-info"></i> Allow Recount</h4>
                                <p class="setting-desc">Allow users to recount previously counted items</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_allow_recount" value="1" @if($allow_recount) checked @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="setting-divider"></div>

                        <!-- Show Expected Qty -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title"><i class="fa fa-eye-slash text-muted"></i> Show Expected Qty</h4>
                                <p class="setting-desc">Show system quantity during counting (disabling enables blind count mode)</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_show_expected_qty" value="1" @if($show_expected) checked @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="setting-divider"></div>

                        <!-- Default Count Type -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title"><i class="fa fa-th-list text-primary"></i> Default Count Type</h4>
                                <p class="setting-desc">Default stocktake type selected when creating a new stocktake</p>
                            </div>
                            <div>
                                <select name="stock_count_default_count_type" class="settings-select">
                                    <option value="full_count" @if($default_type == 'full_count') selected @endif>Full Count</option>
                                    <option value="partial_count" @if($default_type == 'partial_count') selected @endif>Partial Count</option>
                                </select>
                            </div>
                        </div>
                        <div class="setting-divider"></div>

                        <!-- Skip Zero Stock Products -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title"><i class="fa fa-filter text-warning"></i> Skip Zero Stock Products</h4>
                                <p class="setting-desc">Exclude products with zero stock when creating a new stocktake</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_skip_zero_stock" value="1" @if($skip_zero) checked @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Notification Settings -->
                <div class="settings-card">
                    <div class="settings-card-header header-red">
                        <i class="fa fa-bell"></i> Notification Settings
                    </div>
                    <div class="settings-card-body">
                        <!-- Notify on Completion -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title"><i class="fa fa-check-circle text-success"></i> Notify on Completion</h4>
                                <p class="setting-desc">Send notification to manager when a stocktake is completed and ready for review</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_notify_on_completion" value="1" @if($notify_completion) checked @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="setting-divider"></div>

                        <!-- Notify on Large Discrepancies -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title"><i class="fa fa-exclamation-triangle text-danger"></i> Notify on Large Discrepancies</h4>
                                <p class="setting-desc">Send alert when item discrepancy percentage exceeds the threshold</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="stock_count_notify_on_large_discrepancies" value="1" @if($notify_discrepancies) checked @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="setting-divider"></div>

                        <!-- Discrepancy Threshold -->
                        <div class="setting-row">
                            <div class="setting-info">
                                <h4 class="setting-title"><i class="fa fa-percent text-info"></i> Discrepancy Threshold</h4>
                                <p class="setting-desc">Allowed discrepancy percentage before triggering an alert (0 = disabled)</p>
                            </div>
                            <div>
                                <div class="settings-input-group">
                                    <input type="number" name="stock_count_discrepancy_threshold" class="settings-input" value="{{ $threshold }}" min="0" max="100">
                                    <span class="settings-input-addon">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="text-center" style="margin-top: 10px; margin-bottom: 50px;">
                    <button type="submit" class="btn-save-settings">
                        <i class="fa fa-save"></i> Save Settings
                    </button>
                </div>

            </div>
        </div>

        {!! Form::close() !!}
    </section>
@endsection
