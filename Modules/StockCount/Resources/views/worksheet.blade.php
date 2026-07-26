@extends('layouts.app')

@section('title', __('stockcount::lang.worksheet') . ' - ' . $session->name)

@section('css')
    <style>
        .save-status-container {
            position: fixed;
            bottom: 25px;
            right: 25px;
            z-index: 1050;
            background: #2c3e50;
            color: #fff;
            padding: 12px 24px;
            border-radius: 30px;
            font-weight: bold;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            display: none;
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 13px;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .save-status-container i {
            margin-right: 8px;
        }

        .scan-highlight {
            background-color: #dff0d8 !important;
            transition: background-color 1s ease;
        }

        .worksheet-header {
            background: #f7f7f7;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* ── Barcode scanner input-group fix ─────────────────────────────
                                           Forces the text input + buttons to render as one seamless
                                           control regardless of which Bootstrap version is loaded
                                           (fixes the "floating pill buttons with a gap" look). */
        #barcode_scanner_group {
            display: flex;
            align-items: stretch;
            width: 100%;
        }

        #barcode_scanner_group .form-control {
            flex: 1 1 auto;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border-right: none;
            box-shadow: none;
            height: 40px;
        }

        #barcode_scanner_group .input-group-btn-fixed {
            display: flex;
        }

        #barcode_scanner_group .btn {
            border-radius: 0;
            border-left: none;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        #barcode_scanner_group .btn:last-child {
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
            border-left: 1px solid rgba(0, 0, 0, 0.15);
        }

        #barcode_scanner_group .btn:focus,
        #barcode_scanner_group .form-control:focus {
            z-index: 2;
        }

        /* Camera Scanner Modal */
        #camera_preview_container {
            width: 100%;
            min-height: 280px;
            background: #000;
            border-radius: 6px;
            overflow: hidden;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #camera_preview_container #reader {
            width: 100% !important;
            border: none !important;
        }

        #camera_preview_container #reader video {
            width: 100% !important;
            border-radius: 6px;
        }

        .camera-scan-line {
            position: absolute;
            left: 10%;
            right: 10%;
            height: 2px;
            background: rgba(255, 80, 80, 0.85);
            box-shadow: 0 0 8px 2px rgba(255, 80, 80, 0.6);
            animation: scanline 4s ease-in-out infinite;
            pointer-events: none;
            z-index: 10;
        }

        @keyframes scanline {
            0% {
                top: 15%;
            }

            50% {
                top: 80%;
            }

            100% {
                top: 15%;
            }
        }

        #camera_result_feedback {
            margin-top: 10px;
            font-size: 14px;
            min-height: 22px;
        }

        /* Mobile usability enhancements */
        @media (max-width: 768px) {
            .box-tools {
                margin-top: 15px;
                justify-content: flex-start !important;
            }

            .box-tools .btn {
                flex: 1 1 auto;
            }

            #barcode_scanner_group .form-control,
            #barcode_scanner_group .btn {
                height: 46px;
            }

            #barcode_scanner {
                font-size: 16px;
                /* Prevents auto-zoom on iOS */
            }

            .select-type,
            .input-adjust-qty,
            .input-new-qoh,
            .input-note {
                height: 40px !important;
                font-size: 15px !important;
            }

            .btn-qty {
                height: 40px !important;
                width: 40px !important;
                padding: 0 !important;
            }
        }

        /* Hide HTML5 Up/Down Spinners */
        .input-adjust-qty::-webkit-outer-spin-button,
        .input-adjust-qty::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .input-adjust-qty {
            -moz-appearance: textfield;
            /* Firefox */
        }

        /* jQuery UI Autocomplete z-index fix */
        .ui-autocomplete {
            z-index: 2000 !important;
            max-height: 250px;
            overflow-y: auto;
            overflow-x: hidden;
            border-radius: 4px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        /* Worksheet Summary Info Boxes style */
        .worksheet-info-box {
            border-radius: 12px !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
            color: #fff !important;
            padding: 15px !important;
            text-align: center;
            margin-bottom: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .worksheet-info-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .worksheet-info-box .info-number {
            font-size: 28px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 5px;
        }

        .worksheet-info-box .info-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
        }

        .worksheet-info-box.bg-total {
            background: linear-gradient(135deg, #1e3c72, #2a5298) !important;
        }

        .worksheet-info-box.bg-counted {
            background: linear-gradient(135deg, #11998e, #38ef7d) !important;
        }

        .worksheet-info-box.bg-pending {
            background: linear-gradient(135deg, #f57c00, #ffb74d) !important;
        }

        .worksheet-info-box.bg-match {
            background: linear-gradient(135deg, #00b0ff, #80d8ff) !important;
        }

        .worksheet-info-box.bg-surplus {
            background: linear-gradient(135deg, #009688, #4db6ac) !important;
        }

        .worksheet-info-box.bg-shortage {
            background: linear-gradient(135deg, #d32f2f, #ef5350) !important;
        }

        /* ── Autocomplete Dropdown Suggestions Styling ─────────────────── */
        .ui-autocomplete {
            max-height: 280px;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 99999 !important;
            border-radius: 4px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            border: 1px solid #ccc;
            background: #ffffff;
        }

        .ui-autocomplete .ui-menu-item {
            padding: 8px 12px;
            font-size: 13px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }

        .ui-autocomplete .ui-menu-item:last-child {
            border-bottom: none;
        }

        .ui-autocomplete .ui-menu-item:hover,
        .ui-autocomplete .ui-menu-item.ui-state-focus,
        .ui-autocomplete .ui-menu-item-wrapper.ui-state-active {
            background-color: #3c8dbc !important;
            color: #ffffff !important;
            border: none;
            margin: 0;
        }
    </style>
@endsection

@section('content')
    <section class="content-header no-print">
        <div class="row">
            <div class="col-md-8 col-xs-12">
                <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"
                    style="margin: 0; line-height: 1.3; display: flex; align-items: center; flex-wrap: wrap; gap: 6px;">
                    <span>@lang('stockcount::lang.worksheet'):</span>
                    <span id="session_name_display" title="Click to edit session name"
                        style="cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                        <span id="session_name_text"
                            style="border-bottom: 2px dashed #3c8dbc; padding-bottom: 2px;">{{ $session->name }}</span>
                        <button type="button" class="btn btn-default btn-xs"
                            style="color: #3c8dbc; border-color: #3c8dbc; border-radius: 4px; font-size: 13px; font-weight: bold; padding: 3px 8px; display: inline-flex; align-items: center; gap: 4px;"
                            title="Click to edit session name">
                            <i class="fa fa-edit" style="font-size: 14px;"></i> Edit
                        </button>
                    </span>
                    <span id="session_name_edit_box" style="display: none; align-items: center; gap: 6px;">
                        <input type="text" id="session_name_input" class="form-control" value="{{ $session->name }}"
                            style="display: inline-block; width: 280px; font-size: 18px; font-weight: bold; height: 38px; vertical-align: middle;">
                        <button type="button" class="btn btn-success btn-sm btn-flat" id="btn_save_session_name"
                            style="height: 38px; padding: 6px 12px; font-weight: bold;" title="Save"><i
                                class="fa fa-check"></i> Save</button>
                        <button type="button" class="btn btn-default btn-sm btn-flat" id="btn_cancel_session_name"
                            style="height: 38px; padding: 6px 12px;" title="Cancel"><i class="fa fa-times"></i></button>
                    </span>
                    @if(!empty($session->reference_no))
                        <small class="text-muted" style="font-size: 14px; display: block; width: 100%; margin-top: 5px;">Ref No:
                            {{ $session->reference_no }}</small>
                    @endif
                </h1>
            </div>
            <div class="col-md-4 col-xs-12 text-right">
                <div style="margin-top: 8px;">
                    <span
                        class="label @if($session->status == 'completed') bg-green @elseif($session->status == 'reconciled' || $session->status == 'reconcile') bg-purple @elseif($session->status == 'active' || $session->status == 'in_progress') bg-blue @elseif($session->status == 'reviewed') bg-purple @elseif($session->status == 'approved') bg-navy @elseif($session->status == 'rejected' || $session->status == 'cancelled') bg-red @else bg-gray @endif"
                        style="font-size: 14px; padding: 6px 12px; border-radius: 4px; display: inline-block; font-weight: bold;">
                        Status: {{ __('stockcount::lang.' . $session->status) }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <!-- Completion Progress Bar -->
        <div class="row no-print" style="margin-bottom: 20px;">
            <div class="col-md-12">
                <div
                    style="background: #fff; padding: 15px 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #eaeaea;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <strong style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.8px; color: #6d4c41;">%
                            COUNT COMPLETION</strong>
                        <span id="stat_completion_percent"
                            style="font-size: 14px; font-weight: 800; color: #1aa784;">0%</span>
                    </div>
                    <div style="background-color: #1aa784; height: 8px; border-radius: 4px; width: 100%; overflow: hidden;">
                        <div id="stat_completion_bar"
                            style="width: 0%; background-color: #2eff8c; height: 100%; border-radius: 4px; transition: width 0.4s ease;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Summary Section -->
        <div class="row no-print">
            <div class="col-md-2 col-sm-4 col-xs-6">
                <div class="worksheet-info-box bg-total">
                    <div class="info-number" id="stat_total_items">0</div>
                    <div class="info-label">Total Items</div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                <div class="worksheet-info-box bg-counted">
                    <div class="info-number" id="stat_counted">0</div>
                    <div class="info-label">Counted</div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                <div class="worksheet-info-box bg-pending">
                    <div class="info-number" id="stat_pending">0</div>
                    <div class="info-label">Pending</div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                <div class="worksheet-info-box bg-match">
                    <div class="info-number" id="stat_match">0</div>
                    <div class="info-label">Match</div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                <div class="worksheet-info-box bg-surplus">
                    <div class="info-number" id="stat_surplus">0</div>
                    <div class="info-label">Surplus</div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                <div class="worksheet-info-box bg-shortage">
                    <div class="info-number" id="stat_shortage">0</div>
                    <div class="info-label">Shortage</div>
                </div>
            </div>
        </div>

        <!-- Barcode Scan Card -->
        @component('components.widget', ['class' => 'box-solid'])
        <div class="row" style="display: flex; align-items: center; flex-wrap: wrap;">
            <div class="col-md-5 col-xs-12">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="barcode_scanner"><strong><i class="fa fa-barcode"></i>
                            @lang('stockcount::lang.scan_barcode') / SKU:</strong></label>
                    <div id="barcode_scanner_group">
                        <input type="text" id="barcode_scanner" class="form-control"
                            placeholder="Scan barcode or type SKU and press Enter" autofocus>
                        <div class="input-group-btn-fixed">
                            <button class="btn btn-primary" type="button" id="btn_search_barcode">
                                <i class="fa fa-search"></i>
                            </button>
                            <button class="btn btn-default" type="button" id="btn_clear_search"
                                title="Clear search & show all">
                                <i class="fa fa-times text-danger"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-7 col-xs-12 text-right">
                <div class="box-tools"
                    style="display: flex; gap: 5px; justify-content: flex-end; align-items: center; flex-wrap: wrap;">
                    <a href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'index']) }}"
                        class="btn btn-default btn-flat">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'show'], [$session->id]) }}"
                        class="btn btn-info btn-flat">
                        <i class="fa fa-eye"></i> Back to Session
                    </a>
                    <a href="#"
                        data-href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'printWorksheet'], [$session->id]) }}"
                        class="btn btn-default btn-flat print-invoice">
                        <i class="fa fa-print"></i> Print Worksheet
                    </a>
                    <button type="button" class="btn btn-warning btn-flat" id="btn_camera_scan">
                        <i class="fa fa-camera"></i> Scan Camera
                    </button>
                    <button type="button" class="btn btn-success btn-flat" data-toggle="modal"
                        data-target="#importExcelModal">
                        <i class="fa fa-file-excel-o"></i> Import Excel
                    </button>
                </div>
            </div>
        </div>
        @endcomponent
        <!-- Worksheet Filters -->
        @component('components.filters', ['title' => __('report.filters')])
        <div class="row">
            <div class="{{ !$session->blind_count ? 'col-md-4' : 'col-md-8' }}">
                <div class="form-group">
                    <label for="filter_worksheet_status">Count Status:</label>
                    <select id="filter_worksheet_status" class="form-control select2" style="width:100%;">
                        <option value="all">All Items</option>
                        <option value="pending">Pending Items Only</option>
                        <option value="counted">Counted Items Only</option>
                    </select>
                </div>
            </div>
            @if(!$session->blind_count)
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="filter_worksheet_variance">Variance Filter:</label>
                        <select id="filter_worksheet_variance" class="form-control select2" style="width:100%;">
                            <option value="all">All Items</option>
                            <option value="match">Match (No Variance)</option>
                            <option value="surplus">Surplus (+)</option>
                            <option value="shortage">Shortage (-)</option>
                            <option value="discrepancy">Discrepancies Only</option>
                        </select>
                    </div>
                </div>
            @endif
            <div class="col-md-4" style="padding-top: 25px;">
                <button type="button" id="btn_reset_worksheet_filters" class="btn btn-default btn-block">
                    <i class="fa fa-refresh"></i> Reset Filters
                </button>
            </div>
        </div>
        @endcomponent

        <!-- Worksheet Table -->
        @component('components.widget', ['class' => 'box-primary'])
        <div class="row"
            style="margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div class="col-sm-3 col-xs-12">
                <label style="font-weight: normal; margin-bottom: 0;">Show
                    <select id="worksheet_page_length" class="form-control input-sm"
                        style="display: inline-block; width: auto; height: 30px; padding: 5px 10px; margin: 0 5px;">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="-1">All</option>
                    </select> entries
                </label>
            </div>
            <div class="col-sm-6 col-xs-12 text-center"
                style="display: flex; gap: 5px; justify-content: center; flex-wrap: wrap;">
                <button type="button" id="btn_export_worksheet_csv"
                    class="tw-dw-btn-xs tw-dw-btn tw-dw-btn-outline tw-mx-1"><i class="fa fa-file-csv"></i> Export
                    CSV</button>
                <button type="button" id="btn_export_worksheet_excel"
                    class="tw-dw-btn-xs tw-dw-btn tw-dw-btn-outline tw-mx-1"><i class="fa fa-file-excel"></i> Export
                    Excel</button>
                <button type="button" id="btn_print_worksheet_table"
                    class="tw-dw-btn-xs tw-dw-btn tw-dw-btn-outline tw-mx-1 print-invoice"
                    data-href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'printWorksheet'], [$session->id]) }}"><i
                        class="fa fa-print"></i> Print</button>
                <a href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'export'], [$session->id]) }}"
                    class="tw-dw-btn-xs tw-dw-btn tw-dw-btn-outline tw-mx-1"><i class="fa fa-file-pdf"></i> Export PDF</a>
            </div>
            <div class="col-sm-3 col-xs-12 text-right">
                <span id="worksheet_table_info" class="text-muted" style="font-size: 13px;"></span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="worksheet_table">
                <thead>
                    <tr>
                        <th>Product Name (Product Code)</th>
                        @if(!$session->blind_count)
                            <th>QOH</th>
                            <th style="min-width: 80px;">Type</th>
                            <th style="min-width: 150px;">Quantity</th>
                            <th>Unit</th>
                        @endif
                        <th style="min-width: 100px;">{{ $session->blind_count ? 'Counted Qty' : 'New QOH' }}</th>
                        <th>Unit</th>
                        <th style="min-width: 180px;">Note</th>
                        <th style="min-width: 90px; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody id="worksheet_body">
                    @foreach($lines as $line)
                        @include('stockcount::partials.worksheet_row', ['line' => $line, 'session' => $session])
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="row" style="margin-top: 15px;">
            <div class="col-xs-12 text-center">
                <ul class="pagination pagination-sm" id="worksheet_pagination" style="margin: 0;"></ul>
            </div>
        </div>
        <hr>
        <div class="row"
            style="display: flex; gap: 12px; justify-content: center; align-items: center; flex-wrap: wrap; margin-top: 15px;">
            <a href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'index']) }}"
                class="btn btn-default btn-lg btn-flat" style="font-weight: bold;">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
            <button type="button" class="btn btn-primary btn-lg btn-flat" id="btn_save_submit"
                style="min-width: 160px; font-weight: bold;">
                <i class="fa fa-save"></i> Count All & Submit
            </button>
            <button type="button" class="btn btn-danger btn-lg btn-flat" id="btn_reset_worksheet"
                style="min-width: 160px; font-weight: bold;">
                <i class="fa fa-refresh"></i> Reset All Counts
            </button>
        </div>
        @endcomponent
    </section>

    <!-- Floating Auto Save Status Indicator -->
    <div class="save-status-container" id="save_status">
        <span id="save_status_text"><i class="fa fa-spinner fa-spin"></i> Saving...</span>
    </div>

    <!-- Camera Scanner Modal -->
    <div class="modal fade" id="cameraScanModal" tabindex="-1" role="dialog" aria-labelledby="cameraScanModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background:#2c3e50; color:#fff; border-radius:4px 4px 0 0;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        style="color:#fff; opacity:1;"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="cameraScanModalLabel"><i class="fa fa-camera"></i> Scan Barcode with Camera
                    </h4>
                </div>
                <div class="modal-body">
                    <p class="text-muted" style="font-size:13px;">Point your camera at a barcode. It will be detected and
                        added automatically.</p>
                    <div id="camera_preview_container">
                        <div id="reader"></div>
                        <div class="camera-scan-line"></div>
                    </div>
                    <div id="camera_result_feedback" class="text-center"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="btn_stop_camera"><i class="fa fa-stop"></i> Stop
                        Camera</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Excel Modal -->
    <div class="modal fade" id="importExcelModal" tabindex="-1" role="dialog" aria-labelledby="importExcelModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['url' => action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'importExcel'], [$session->id]), 'method' => 'post', 'files' => true]) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="importExcelModalLabel">Import Count Worksheet from Excel</h4>
                </div>
                <div class="modal-body">
                    <p>You can upload an Excel sheet to bulk import counted quantities for this session.</p>
                    <div class="form-group">
                        <label for="excel_file">Select Excel/CSV File:</label>
                        <input type="file" name="file" id="excel_file" required accept=".xlsx, .xls, .csv">
                        <p class="help-block">Supported formats: .xlsx, .xls, .csv</p>
                    </div>
                    <div class="well well-sm">
                        <strong>Instructions:</strong>
                        <ul>
                            <li>Make sure the sheet has columns for <strong>SKU</strong> and <strong>Counted Qty</strong>
                                (notes column is optional).</li>
                            <li>You can download a template pre-filled with all active session items to fill in:</li>
                        </ul>
                        <a href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'downloadTemplate'], [$session->id]) }}"
                            class="btn btn-info btn-xs">
                            <i class="fa fa-download"></i> Download Pre-filled Template
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Upload & Import</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

    <!-- Mobile Count Modal -->
    <div class="modal fade" id="mobileCountModal" tabindex="-1" role="dialog" aria-labelledby="mobileCountModalLabel">
        <div class="modal-dialog modal-sm" role="document" style="margin: 10px auto; max-width: 95%;">
            <div class="modal-content"
                style="border-radius: 8px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
                <div class="modal-header" style="background-color: #3c8dbc; color: #fff; padding: 12px 15px;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        style="color: #fff; opacity: 1;"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="mobileCountModalLabel"
                        style="font-weight: bold; font-size: 16px; margin: 0;"><i class="fa fa-pencil-square-o"></i> Enter
                        Count</h4>
                </div>
                <div class="modal-body" style="padding: 15px 20px;">
                    <!-- Product Information -->
                    <div style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                        <h5 id="modal_product_name"
                            style="font-weight: bold; margin: 0 0 5px 0; font-size: 15px; color: #333;">Product Name</h5>
                        <span id="modal_product_sku" class="text-muted" style="font-size: 13px;">SKU: 12345</span>
                    </div>

                    <!-- Count Input Block -->
                    <div class="form-group text-center" style="margin-bottom: 20px;">
                        <label id="modal_qty_label"
                            style="font-size: 14px; font-weight: bold; display: block; margin-bottom: 10px;">Counted
                            Quantity</label>
                        <div class="input-group input-group-lg" style="max-width: 220px; margin: 0 auto;">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default btn-flat" id="modal_btn_minus"
                                    style="height: 46px; width: 46px; font-size: 18px; padding: 0;"><i
                                        class="fa fa-minus"></i></button>
                            </span>
                            <input type="number" id="modal_qty_input" class="form-control text-center"
                                style="height: 46px; font-size: 20px; font-weight: bold;" step="any" min="0">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default btn-flat" id="modal_btn_plus"
                                    style="height: 46px; width: 46px; font-size: 18px; padding: 0;"><i
                                        class="fa fa-plus"></i></button>
                            </span>
                        </div>
                    </div>

                    <!-- Note Input Block -->
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="modal_note_input" style="font-size: 13px; font-weight: bold;">Note</label>
                        <input type="text" id="modal_note_input" class="form-control" placeholder="Add note here...">
                    </div>
                </div>
                <div class="modal-footer" style="padding: 10px 15px; background-color: #f9f9f9; display: flex; gap: 10px;">
                    <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"
                        style="flex: 1; height: 40px; font-weight: bold; margin: 0;">Cancel</button>
                    <button type="button" class="btn btn-primary btn-flat" id="modal_btn_save"
                        style="flex: 2; height: 40px; font-weight: bold; margin: 0;">Save & Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Line Status Change Modal -->
    <div class="modal fade" id="lineStatusModal" tabindex="-1" role="dialog" aria-labelledby="lineStatusModalLabel">
        <div class="modal-dialog modal-sm" role="document" style="margin: 30px auto; max-width: 95%;">
            <div class="modal-content"
                style="border-radius: 8px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
                <div class="modal-header" style="background-color: #3c8dbc; color: #fff; padding: 12px 15px;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        style="color: #fff; opacity: 1;"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="lineStatusModalLabel"
                        style="font-weight: bold; font-size: 16px; margin: 0;">
                        <i class="fa fa-tag"></i> Change Item Status
                    </h4>
                </div>
                <div class="modal-body" style="padding: 15px 20px;">
                    <input type="hidden" id="status_modal_line_id">

                    <div style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                        <h5 id="status_modal_product_name"
                            style="font-weight: bold; margin: 0 0 5px 0; font-size: 15px; color: #333;">Product Name</h5>
                        <span id="status_modal_product_sku" class="text-muted" style="font-size: 13px;">SKU</span>
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="status_modal_select" style="font-size: 13px; font-weight: bold;">Status:</label>
                        <select id="status_modal_select" class="form-control"
                            style="font-weight: bold; height: 40px; font-size: 14px;">
                            <option value="counted">Counted (Marked as counted)</option>
                            <option value="pending">Pending (Not counted, Qty set to 0)</option>
                        </select>
                    </div>

                    <p id="status_modal_help_text" class="help-block" style="font-size: 12px; margin-bottom: 0;">
                        Changing to Pending will reset the count quantity to 0 and remove counted status.
                    </p>
                </div>
                <div class="modal-footer" style="padding: 10px 15px; background-color: #f9f9f9; display: flex; gap: 10px;">
                    <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"
                        style="flex: 1; height: 40px; font-weight: bold; margin: 0;">Cancel</button>
                    <button type="button" class="btn btn-primary btn-flat" id="status_modal_btn_save"
                        style="flex: 2; height: 40px; font-weight: bold; margin: 0;">Save & Update</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <!-- html5-qrcode library for camera barcode scanning -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        $(document).ready(function () {
            // ── Inline Edit Session Name ──────────────────────────────────
            $('#session_name_display').on('click', function () {
                $('#session_name_display').hide();
                $('#session_name_edit_box').css('display', 'inline-flex');
                $('#session_name_input').focus().select();
            });

            $('#btn_cancel_session_name').on('click', function () {
                var currentName = $('#session_name_text').text().trim();
                $('#session_name_input').val(currentName);
                $('#session_name_edit_box').hide();
                $('#session_name_display').show();
            });

            function saveSessionName() {
                var newName = $('#session_name_input').val().trim();
                var currentName = $('#session_name_text').text().trim();

                if (newName === '') {
                    toastr.error('Session name cannot be empty.');
                    return;
                }

                if (newName === currentName) {
                    $('#session_name_edit_box').hide();
                    $('#session_name_display').show();
                    return;
                }

                var btn = $('#btn_save_session_name');
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                $.ajax({
                    method: 'POST',
                    url: "{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'updateName'], [$session->id]) }}",
                    dataType: 'json',
                    data: {
                        name: newName,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (result) {
                        btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save');
                        if (result.success) {
                            $('#session_name_text').text(result.name);
                            $('#session_name_input').val(result.name);
                            $('#session_name_edit_box').hide();
                            $('#session_name_display').show();
                            toastr.success(result.message || 'Session name updated successfully.');
                        } else {
                            toastr.error(result.message || 'Failed to update session name.');
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save');
                        var msg = 'Error updating session name.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        toastr.error(msg);
                    }
                });
            }

            $('#btn_save_session_name').on('click', function () {
                saveSessionName();
            });

            $('#session_name_input').on('keydown', function (e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    saveSessionName();
                } else if (e.which === 27) { // Escape key
                    e.preventDefault();
                    $('#btn_cancel_session_name').trigger('click');
                }
            });

            // ── Barcode text input scanning & filtering ────────────────────
            $('#barcode_scanner').on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    var val = $('#barcode_scanner').val().trim();
                    applyWorksheetFilters();
                    if (val !== '') {
                        scanBarcode(val);
                    }
                }
            });

            $('#barcode_scanner').on('keyup input', function () {
                applyWorksheetFilters();
            });

            $('#btn_search_barcode').on('click', function () {
                var val = $('#barcode_scanner').val().trim();
                applyWorksheetFilters();
                if (val !== '') {
                    scanBarcode(val);
                }
            });

            $('#btn_clear_search').on('click', function () {
                $('#barcode_scanner').val('');
                $('#worksheet_filter_search').val('');
                applyWorksheetFilters();
                $('#barcode_scanner').focus();
            });

            function filterTableRows(query) {
                applyWorksheetFilters();
            }

            var currentPage = 1;
            var pageSize = 25;

            function updatePagination() {
                var visibleRows = $('#worksheet_body tr').filter(function () {
                    return $(this).data('filtered-out') !== true;
                });

                var totalEntries = visibleRows.length;

                if (pageSize === -1) {
                    visibleRows.show();
                    $('#worksheet_pagination').empty();
                    $('#worksheet_table_info').text('Showing 1 to ' + totalEntries + ' of ' + totalEntries + ' entries');
                    return;
                }

                var totalPages = Math.ceil(totalEntries / pageSize) || 1;
                if (currentPage > totalPages) {
                    currentPage = totalPages;
                }
                if (currentPage < 1) {
                    currentPage = 1;
                }

                var start = (currentPage - 1) * pageSize;
                var end = start + pageSize;
                if (end > totalEntries) {
                    end = totalEntries;
                }

                $('#worksheet_body tr').hide();
                visibleRows.slice(start, end).show();

                if (totalEntries === 0) {
                    $('#worksheet_table_info').text('Showing 0 to 0 of 0 entries');
                } else {
                    $('#worksheet_table_info').text('Showing ' + (start + 1) + ' to ' + end + ' of ' + totalEntries + ' entries');
                }

                var paginationUl = $('#worksheet_pagination');
                paginationUl.empty();

                if (totalPages <= 1) {
                    return;
                }

                var prevClass = (currentPage === 1) ? 'disabled' : '';
                paginationUl.append('<li class="' + prevClass + '"><a href="#" class="page-link" data-page="' + (currentPage - 1) + '">&laquo;</a></li>');

                for (var i = 1; i <= totalPages; i++) {
                    var activeClass = (currentPage === i) ? 'active' : '';
                    paginationUl.append('<li class="' + activeClass + '"><a href="#" class="page-link" data-page="' + i + '">' + i + '</a></li>');
                }

                var nextClass = (currentPage === totalPages) ? 'disabled' : '';
                paginationUl.append('<li class="' + nextClass + '"><a href="#" class="page-link" data-page="' + (currentPage + 1) + '">&raquo;</a></li>');
            }

            $(document).on('click', '#worksheet_pagination .page-link', function (e) {
                e.preventDefault();
                var parentLi = $(this).parent();
                if (parentLi.hasClass('disabled') || parentLi.hasClass('active')) {
                    return;
                }
                currentPage = parseInt($(this).data('page'));
                updatePagination();
            });

            $(document).on('change', '#worksheet_page_length', function () {
                pageSize = parseInt($(this).val());
                currentPage = 1;
                updatePagination();
            });

            // Worksheet filter handlers
            $('#filter_worksheet_status, #filter_worksheet_variance').on('change', function () {
                applyWorksheetFilters();
            });

            $('#btn_reset_worksheet_filters').on('click', function () {
                $('#filter_worksheet_status').val('all').trigger('change');
                if ($('#filter_worksheet_variance').length) {
                    $('#filter_worksheet_variance').val('all').trigger('change');
                }
                $('#barcode_scanner').val('');
                applyWorksheetFilters();
            });

            function applyWorksheetFilters() {
                var statusFilter = $('#filter_worksheet_status').val() || 'all';
                var varianceFilter = $('#filter_worksheet_variance').val() || 'all';
                var query = ($('#barcode_scanner').val() || '').toLowerCase().trim();

                var rows = $('#worksheet_body tr');
                rows.each(function () {
                    var row = $(this);
                    var isCounted = row.hasClass('is-counted');
                    var text = row.text().toLowerCase();

                    var showStatus = true;
                    if (statusFilter === 'pending' && isCounted) showStatus = false;
                    if (statusFilter === 'counted' && !isCounted) showStatus = false;

                    var showSearch = true;
                    if (query !== '' && text.indexOf(query) === -1) showSearch = false;

                    var showVariance = true;
                    if (varianceFilter !== 'all') {
                        var newQohInput = row.find('.input-new-qoh');
                        var newQoh = parseFloat(newQohInput.val()) || 0;
                        var bookQty = parseFloat(newQohInput.data('book-qty')) || 0;

                        if (varianceFilter === 'match' && newQoh !== bookQty) showVariance = false;
                        if (varianceFilter === 'surplus' && newQoh <= bookQty) showVariance = false;
                        if (varianceFilter === 'shortage' && newQoh >= bookQty) showVariance = false;
                        if (varianceFilter === 'discrepancy' && newQoh === bookQty) showVariance = false;
                    }

                    if (showStatus && showSearch && showVariance) {
                        row.data('filtered-out', false);
                    } else {
                        row.data('filtered-out', true);
                    }
                });

                currentPage = 1;
                updatePagination();
            }

            $('#btn_export_worksheet_csv, #btn_export_worksheet_excel').on('click', function () {
                var isExcel = $(this).attr('id') === 'btn_export_worksheet_excel';
                var csv = [];
                var headers = [];

                $('#worksheet_table thead th').each(function () {
                    headers.push('"' + $(this).text().trim().replace(/"/g, '""') + '"');
                });
                csv.push(headers.join(','));

                $('#worksheet_body tr').filter(function () { return !$(this).data('filtered-out'); }).each(function () {
                    var row = [];
                    $(this).find('td').each(function () {
                        var text = '';
                        var input = $(this).find('input, select');
                        if (input.length) {
                            text = input.val();
                        } else {
                            text = $(this).text().trim();
                        }
                        row.push('"' + text.replace(/"/g, '""') + '"');
                    });
                    csv.push(row.join(','));
                });

                var blob = new Blob([csv.join('\n')], { type: isExcel ? 'application/vnd.ms-excel;charset=utf-8;' : 'text/csv;charset=utf-8;' });
                var link = document.createElement('a');
                var url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', 'Worksheet_' + "{{ $session->name }}" + (isExcel ? '.xls' : '.csv'));
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            function filterTableRowsForScannedId(line_id) {
                var rows = $('#worksheet_body tr');
                rows.each(function () {
                    var row = $(this);
                    if (row.attr('id') === 'line_' + line_id) {
                        row.data('filtered-out', false);
                    } else {
                        row.data('filtered-out', true);
                    }
                });
                currentPage = 1;
                updatePagination();
            }

            // ── Camera scanning ───────────────────────────────────────────
            var html5QrCode = null;
            var cameraActive = false;
            var lastScanned = '';
            var scanCooldown = false;

            $('#btn_camera_scan').on('click', function () {
                $('#cameraScanModal').modal('show');
            });

            $('#cameraScanModal').on('shown.bs.modal', function () {
                startCamera();
            });

            $('#cameraScanModal').on('hidden.bs.modal', function () {
                stopCamera();
            });

            $('#btn_stop_camera').on('click', function () {
                stopCamera();
                $('#cameraScanModal').modal('hide');
            });

            function startCamera() {
                if (cameraActive) return;
                $('#camera_result_feedback').html('<span class="text-muted"><i class="fa fa-spinner fa-spin"></i> Starting camera...</span>');

                html5QrCode = new Html5Qrcode('reader');

                Html5Qrcode.getCameras().then(function (devices) {
                    if (!devices || devices.length === 0) {
                        $('#camera_result_feedback').html('<span class="text-danger"><i class="fa fa-times"></i> No camera found on this device.</span>');
                        return;
                    }

                    // Prefer back camera on mobile
                    var cameraId = devices[devices.length - 1].id;

                    html5QrCode.start(
                        cameraId,
                        { fps: 10, qrbox: { width: 250, height: 150 } },
                        function (decodedText) {
                            // Prevent duplicate scans within 2 seconds
                            if (scanCooldown || decodedText === lastScanned) return;
                            lastScanned = decodedText;
                            scanCooldown = true;

                            $('#camera_result_feedback').html(
                                '<span class="text-success"><i class="fa fa-check-circle"></i> Scanned: <strong>' +
                                $('<span>').text(decodedText).html() +
                                '</strong> — processing...</span>'
                            );

                            // Feed into main scan function
                            scanBarcode(decodedText, function () {
                                // Reset cooldown after 2s so same barcode can be scanned again
                                setTimeout(function () {
                                    scanCooldown = false;
                                    lastScanned = '';
                                    $('#camera_result_feedback').html('<span class="text-muted">Ready to scan next item...</span>');
                                }, 2000);
                            });
                        },
                        function () { /* scan frame errors — ignore */ }
                    ).then(function () {
                        cameraActive = true;
                        $('#camera_result_feedback').html('<span class="text-muted">Camera active — point at a barcode.</span>');
                    }).catch(function (err) {
                        $('#camera_result_feedback').html('<span class="text-danger"><i class="fa fa-times"></i> Cannot access camera: ' + err + '</span>');
                    });
                }).catch(function (err) {
                    $('#camera_result_feedback').html('<span class="text-danger"><i class="fa fa-times"></i> Camera permission denied or unavailable.</span>');
                });
            }

            function stopCamera() {
                if (html5QrCode && cameraActive) {
                    html5QrCode.stop().then(function () {
                        html5QrCode.clear();
                        cameraActive = false;
                        lastScanned = '';
                        scanCooldown = false;
                    }).catch(function () {
                        cameraActive = false;
                    });
                }
            }

            // ── Core scan function (shared by text input & camera) ────────
            function scanBarcode(barcode, callback) {
                if (!barcode || barcode === '') return;

                showSaveStatus('Searching...', 'spinner');

                $.ajax({
                    method: "POST",
                    url: "{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'scanBarcode'], [$session->id]) }}",
                    dataType: "json",
                    data: {
                        barcode: barcode,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (result) {
                        if (result.success) {
                            var allowRecount = {{ (isset(session('business.common_settings')['stock_count_allow_recount']) ? session('business.common_settings')['stock_count_allow_recount'] : true) ? 'true' : 'false' }};
                            var isCounted = $('#line_' + result.line_id).hasClass('is-counted');
                            if (!allowRecount && isCounted) {
                                $('#barcode_scanner').val('').focus();
                                showSaveStatus('Disabled ✖', 'times');
                                toastr.warning('Recounting is disabled in settings.');
                                if (typeof callback === 'function') callback(result);
                                return;
                            }

                            showSaveStatus('Saved ✔', 'check');
                            if (result.appended) {
                                $('#worksheet_body').prepend(result.row_html);
                                updateWorksheetProductsList();
                            }
                            // Update qty in table either way
                            var input = $('#new_qoh_' + result.line_id);
                            if (input.length) {
                                updateRowInputs(result.line_id, result.new_qty);
                            }
                            $('#line_' + result.line_id).addClass('is-counted');
                            recalculateStats();

                            // Set value, select & focus so the user can easily scan the next barcode to overwrite it
                            $('#barcode_scanner').val(barcode).select().focus();

                            // Show only this scanned product row
                            filterTableRowsForScannedId(result.line_id);

                            highlightRow(result.line_id);

                            // Open count modal for easy phone counting
                            openCountModal(result.line_id);
                        } else {
                            applyWorksheetFilters();
                            showSaveStatus('Filtered ✔', 'check');
                        }
                        if (typeof callback === 'function') callback(result);
                    },
                    error: function () {
                        applyWorksheetFilters();
                        showSaveStatus('Filtered ✔', 'check');
                        if (typeof callback === 'function') callback(null);
                    }
                });
            }

            function updateRowInputs(line_id, new_qoh) {
                var bookQty = parseFloat($('#new_qoh_' + line_id).data('book-qty')) || 0;
                var diff = new_qoh - bookQty;
                var type = diff >= 0 ? '+' : '-';
                var adjustQty = Math.abs(diff);

                $('#type_' + line_id).val(type);
                $('#qty_' + line_id).val(adjustQty.toFixed(4));
                $('#new_qoh_' + line_id).val(new_qoh.toFixed(4));
            }

            // ── Highlight scanned row ──────────────────────────────────────
            function highlightRow(line_id) {
                var row = $('#line_' + line_id);
                if (!row.length) return;
                row.addClass('scan-highlight');
                $('html, body').animate({ scrollTop: row.offset().top - 150 }, 500);
                setTimeout(function () { row.removeClass('scan-highlight'); }, 1500);
            }

            // ── Quantity +/- buttons ───────────────────────────────────────
            $(document).on('click', '.btn-qty', function () {
                var id = $(this).data('id');
                var action = $(this).data('action');
                var input = $('#qty_' + id);
                var val = parseFloat(input.val()) || 0;
                if (action === 'plus') {
                    input.val(val + 1).trigger('change');
                } else if (action === 'minus' && val > 0) {
                    input.val(val - 1).trigger('change');
                }
            });

            // ── Auto-save on qty / note change ────────────────────────────────────────────
            var saveTimeouts = {};
            function saveProgress(line_id) {
                var qty = $('#new_qoh_' + line_id).val();
                var note = $('#note_' + line_id).val();
                showSaveStatus('Saving...', 'spinner');
                if (saveTimeouts[line_id]) clearTimeout(saveTimeouts[line_id]);
                saveTimeouts[line_id] = setTimeout(function () {
                    $.ajax({
                        method: "POST",
                        url: "{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'saveWorksheetProgress'], [$session->id]) }}",
                        dataType: "json",
                        data: { line_id: line_id, quantity: qty, note: note, _token: "{{ csrf_token() }}" },
                        success: function (result) {
                            showSaveStatus(result.success ? 'Saved ✔' : 'Error ✖', result.success ? 'check' : 'times');
                            if (!result.success) toastr.error('Failed to save progress.');
                        },
                        error: function () {
                            showSaveStatus('Error ✖', 'times');
                            toastr.error('Network error during auto-save.');
                        }
                    });
                }, 500);
            }

            $(document).on('change keyup', '.select-type, .input-adjust-qty', function () {
                var id = $(this).data('id');
                var type = $('#type_' + id).val();
                var adjustQty = parseFloat($('#qty_' + id).val()) || 0;
                var bookQty = parseFloat($('#new_qoh_' + id).data('book-qty')) || 0;

                var newQoh = bookQty;
                if (type === '+') {
                    newQoh = bookQty + adjustQty;
                } else {
                    newQoh = bookQty - adjustQty;
                    if (newQoh < 0) newQoh = 0;
                }

                $('#new_qoh_' + id).val(newQoh.toFixed(4));
                $('#line_' + id).addClass('is-counted');
                recalculateStats();
                saveProgress(id);
            });

            $(document).on('change keyup', '.input-note', function () {
                var id = $(this).data('id');
                saveProgress(id);
            });

            $(document).on('change keyup', '.input-new-qoh', function () {
                if ($(this).attr('readonly')) return;
                var id = $(this).data('id');
                $('#line_' + id).addClass('is-counted');
                recalculateStats();
                saveProgress(id);
            });

            // ── Save-status floating toast ────────────────────────────────
            function showSaveStatus(text, icon) {
                var statusDiv = $('#save_status');
                var statusText = $('#save_status_text');
                var spin = (icon === 'spinner') ? ' fa-spin' : '';
                statusText.html('<i class="fa fa-' + icon + spin + '"></i> ' + text);
                statusDiv.fadeIn(300);
                if (icon !== 'spinner') {
                    setTimeout(function () { statusDiv.fadeOut(300); }, 2000);
                }
            }

            function recalculateStats() {
                var total = $('#worksheet_body tr.worksheet-row').length;
                var counted = $('#worksheet_body tr.is-counted').length;
                var pending = total - counted;

                $('#worksheet_body tr.worksheet-row').each(function () {
                    var lineId = $(this).attr('id').replace('line_', '');
                    var cell = $('#status_cell_' + lineId);
                    if ($(this).hasClass('is-counted')) {
                        cell.addClass('btn_toggle_line_status').attr('data-status', 'counted');
                        cell.html('<span class="label label-success">Counted</span>');
                    } else {
                        cell.addClass('btn_toggle_line_status').attr('data-status', 'pending');
                        cell.html('<span class="label label-warning">Pending</span>');
                    }
                });

                var match = 0;
                var surplus = 0;
                var shortage = 0;

                $('#worksheet_body tr.is-counted').each(function () {
                    var newQohInput = $(this).find('.input-new-qoh');
                    var newQoh = parseFloat(newQohInput.val()) || 0;
                    var bookQty = parseFloat(newQohInput.data('book-qty')) || 0;

                    if (newQoh === bookQty) {
                        match++;
                    } else if (newQoh > bookQty) {
                        surplus++;
                    } else {
                        shortage++;
                    }
                });

                $('#stat_total_items').text(total);
                $('#stat_counted').text(counted);
                $('#stat_pending').text(pending);
                $('#stat_match').text(match);
                $('#stat_surplus').text(surplus);
                $('#stat_shortage').text(shortage);

                var percent = total > 0 ? Math.round((counted / total) * 100) : 0;
                $('#stat_completion_percent').text(percent + '%');
                $('#stat_completion_bar').css('width', percent + '%');
            }

            recalculateStats();
            updatePagination();

            // ── Mobile / Smart Phone Popup Counting Logic ───────────────────
            var worksheetProducts = [];
            function updateWorksheetProductsList() {
                worksheetProducts = [];
                $('#worksheet_body tr').each(function () {
                    var row = $(this);
                    var id = row.attr('id').replace('line_', '');
                    var productTd = row.find('td:eq(1)');
                    var nameText = productTd.find('strong').text().trim();
                    var fullText = productTd.text().trim();
                    var skuMatch = fullText.match(/\(([^)]+)\)/);
                    var skuText = skuMatch ? skuMatch[1].trim() : '';

                    if (nameText !== '') {
                        worksheetProducts.push({
                            label: nameText + (skuText ? ' (' + skuText + ')' : ''),
                            value: nameText,
                            sku: skuText,
                            line_id: id,
                            name: nameText
                        });
                    }
                });
            }

            updateWorksheetProductsList();

            $('#barcode_scanner').autocomplete({
                source: function (request, response) {
                    var term = $.ui.autocomplete.escapeRegex(request.term);
                    var matcher = new RegExp(term, "i");
                    response($.grep(worksheetProducts, function (value) {
                        return matcher.test(value.name) || matcher.test(value.sku) || matcher.test(value.label);
                    }));
                },
                minLength: 1,
                select: function (event, ui) {
                    event.preventDefault();
                    $('#barcode_scanner').val(ui.item.name);
                    applyWorksheetFilters();

                    var allowRecount = {{ (isset(session('business.common_settings')['stock_count_allow_recount']) ? session('business.common_settings')['stock_count_allow_recount'] : true) ? 'true' : 'false' }};
                    var isCounted = $('#line_' + ui.item.line_id).hasClass('is-counted');
                    if (!allowRecount && isCounted) {
                        toastr.warning('Recounting is disabled in settings.');
                        return;
                    }

                    openCountModal(ui.item.line_id);
                }
            });

            // Tap row to open popup (skips status cell)
            $(document).on('click', '#worksheet_body tr', function (e) {
                if ($(e.target).closest('input, select, button, a, .btn_toggle_line_status, [id^="status_cell_"], td:last-child').length) {
                    return;
                }
                var line_id = $(this).attr('id').replace('line_', '');

                var allowRecount = {{ (isset(session('business.common_settings')['stock_count_allow_recount']) ? session('business.common_settings')['stock_count_allow_recount'] : true) ? 'true' : 'false' }};
                var isCounted = $(this).hasClass('is-counted');
                if (!allowRecount && isCounted) {
                    toastr.warning('Recounting is disabled in settings.');
                    return;
                }

                openCountModal(line_id);
            });

            // Click status badge to open status change modal
            $(document).on('click', '.btn_toggle_line_status', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var line_id = $(this).data('id');
                var currentStatus = $(this).data('status') || 'pending';
                var row = $('#line_' + line_id);

                var nameText = row.find('td:eq(0) strong').text() || 'Product';
                var fullText = row.find('td:eq(0)').text();
                var skuMatch = fullText.match(/\(([^)]+)\)/);
                var skuText = skuMatch ? skuMatch[1] : '';

                $('#status_modal_line_id').val(line_id);
                $('#status_modal_product_name').text(nameText);
                $('#status_modal_product_sku').text(skuText ? 'SKU: ' + skuText : '');

                $('#status_modal_select').val(currentStatus);
                $('#status_modal_select').trigger('change');
                $('#lineStatusModal').modal('show');
            });

            $('#status_modal_select').on('change', function () {
                var val = $(this).val();
                if (val === 'pending') {
                    $('#status_modal_help_text').text('Changing to Pending will reset count quantity to 0 and remove counted status.');
                } else {
                    $('#status_modal_help_text').text('Changing to Counted will mark this product as counted.');
                }
            });

            $('#status_modal_btn_save').on('click', function () {
                var line_id = $('#status_modal_line_id').val();
                var newStatus = $('#status_modal_select').val();
                var row = $('#line_' + line_id);
                var cell = $('#status_cell_' + line_id);

                if (newStatus === 'pending') {
                    // Mark as pending: remove is-counted class, set qty to 0
                    row.removeClass('is-counted');

                    $('#qty_' + line_id).val(0);
                    $('#type_' + line_id).val('+');

                    var bookQty = parseFloat($('#new_qoh_' + line_id).data('book-qty')) || 0;
                    var isBlind = {{ $session->blind_count ? 'true' : 'false' }};
                    $('#new_qoh_' + line_id).val(isBlind ? 0 : bookQty);

                    recalculateStats();

                    // Send AJAX to backend so database sets counted_by to null
                    $.ajax({
                        url: '{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, "saveWorksheetProgress"], [$session->id]) }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            line_id: line_id,
                            is_pending: true
                        },
                        success: function (res) {
                            toastr.success('Item marked as Pending (Qty reset to 0)');
                            updateWorksheetProductsList();
                            applyWorksheetFilters();
                        }
                    });
                } else {
                    // Mark as counted
                    row.addClass('is-counted');

                    recalculateStats();

                    var note = $('#note_' + line_id).val() || '';

                    $.ajax({
                        url: '{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, "saveWorksheetProgress"], [$session->id]) }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            line_id: line_id,
                            quantity: $('#new_qoh_' + line_id).val(),
                            note: note
                        },
                        success: function (res) {
                            toastr.success('Item marked as Counted');
                            updateWorksheetProductsList();
                            applyWorksheetFilters();
                        }
                    });
                }

                $('#lineStatusModal').modal('hide');
            });

            var activeLineId = null;
            function openCountModal(line_id) {
                activeLineId = line_id;
                var row = $('#line_' + line_id);
                if (!row.length) return;

                var productName = row.find('td:first strong').text().trim();
                var fullText = row.find('td:first').text();
                var skuMatch = fullText.match(/\(([^)]+)\)/);
                var productSku = skuMatch ? skuMatch[1] : '';

                var countedQty = parseFloat($('#new_qoh_' + line_id).val()) || 0;
                var note = $('#note_' + line_id).val() || '';

                $('#modal_product_name').text(productName);
                $('#modal_product_sku').text('SKU: ' + productSku);
                $('#modal_qty_input').val(countedQty);
                $('#modal_note_input').val(note);

                $('#mobileCountModal').modal('show');

                setTimeout(function () {
                    $('#modal_qty_input').focus().select();
                }, 500);
            }

            $('#modal_btn_plus').on('click', function () {
                var val = parseFloat($('#modal_qty_input').val()) || 0;
                $('#modal_qty_input').val(val + 1);
            });

            $('#modal_btn_minus').on('click', function () {
                var val = parseFloat($('#modal_qty_input').val()) || 0;
                if (val > 0) {
                    $('#modal_qty_input').val(val - 1);
                }
            });

            $('#modal_btn_save').on('click', function () {
                if (activeLineId === null) return;

                var qty = parseFloat($('#modal_qty_input').val()) || 0;
                var note = $('#modal_note_input').val() || '';

                var isBlindCount = {{ $session->blind_count ? 'true' : 'false' }};

                if (isBlindCount) {
                    $('#new_qoh_' + activeLineId).val(qty.toFixed(4));
                } else {
                    var bookQty = parseFloat($('#new_qoh_' + activeLineId).data('book-qty')) || 0;
                    var diff = qty - bookQty;
                    var type = diff >= 0 ? '+' : '-';
                    var adjustQty = Math.abs(diff);

                    $('#type_' + activeLineId).val(type);
                    $('#qty_' + activeLineId).val(adjustQty.toFixed(4));
                    $('#new_qoh_' + activeLineId).val(qty.toFixed(4));
                }

                $('#note_' + activeLineId).val(note);
                $('#line_' + activeLineId).addClass('is-counted');
                recalculateStats();

                saveProgress(activeLineId);

                $('#mobileCountModal').modal('hide');

                setTimeout(function () {
                    applyWorksheetFilters();
                    $('#barcode_scanner').focus();
                }, 300);
            });

            // Handle Save & Submit bulk post
            $('#btn_save_submit').on('click', function () {
                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
                showSaveStatus('Saving worksheet...', 'spinner');

                var lines = [];
                $('#worksheet_body tr').each(function () {
                    var row = $(this);
                    var line_id = row.attr('id').replace('line_', '');
                    var qty = $('#new_qoh_' + line_id).val();
                    var note = $('#note_' + line_id).val();

                    lines.push({
                        line_id: line_id,
                        quantity: qty,
                        note: note
                    });
                });

                $.ajax({
                    method: "POST",
                    url: "{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'saveWorksheetProgress'], [$session->id]) }}",
                    dataType: "json",
                    data: {
                        lines: lines,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (result) {
                        if (result.success) {
                            showSaveStatus('Saved ✔', 'check');
                            toastr.success('Worksheet saved successfully.');
                            window.location.href = "{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'show'], [$session->id]) }}";
                        } else {
                            btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save & Submit');
                            showSaveStatus('Error ✖', 'times');
                            toastr.error(result.message || 'Failed to save worksheet.');
                        }
                    },
                    error: function () {
                        btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save & Submit');
                        showSaveStatus('Error ✖', 'times');
                        toastr.error('Network error while saving worksheet.');
                    }
                });
            });

            $('#modal_qty_input, #modal_note_input').on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#modal_btn_save').trigger('click');
                }
            });

            // Handle Reset All Counts
            $('#btn_reset_worksheet').on('click', function () {
                swal({
                    title: "Are you sure you want to reset all counted quantities?",
                    text: "This will set all your counted quantities on this worksheet back to 0.",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willReset) => {
                    if (willReset) {
                        showSaveStatus('Resetting...', 'spinner');

                        var rows = $('#worksheet_body tr');
                        var totalRows = rows.length;
                        var processed = 0;

                        if (totalRows === 0) {
                            showSaveStatus('Reset ✔', 'check');
                            return;
                        }

                        rows.each(function () {
                            var row = $(this);
                            var line_id = row.attr('id').replace('line_', '');

                            // Reset inputs in DOM
                            row.removeClass('is-counted');
                            var isBlindCount = {{ $session->blind_count ? 'true' : 'false' }};
                            if (isBlindCount) {
                                $('#new_qoh_' + line_id).val('0.0000');
                            } else {
                                var bookQty = parseFloat($('#new_qoh_' + line_id).data('book-qty')) || 0;
                                $('#type_' + line_id).val('+');
                                $('#qty_' + line_id).val('0');
                                $('#new_qoh_' + line_id).val(bookQty.toFixed(4));
                            }
                            $('#note_' + line_id).val('');

                            // Call AJAX to save progress
                            $.ajax({
                                method: "POST",
                                url: "{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'saveWorksheetProgress'], [$session->id]) }}",
                                dataType: "json",
                                data: {
                                    line_id: line_id,
                                    quantity: 0,
                                    note: '',
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function () {
                                    processed++;
                                    if (processed === totalRows) {
                                        showSaveStatus('Reset Completed ✔', 'check');
                                        toastr.success('All counted quantities reset to 0.');
                                        recalculateStats();
                                        updatePagination();
                                    }
                                },
                                error: function () {
                                    processed++;
                                    if (processed === totalRows) {
                                        showSaveStatus('Reset Error ✖', 'times');
                                        toastr.error('Some lines failed to reset.');
                                        recalculateStats();
                                        updatePagination();
                                    }
                                }
                            });
                        });
                    }
                });
            });
        });
    </script>
@endsection