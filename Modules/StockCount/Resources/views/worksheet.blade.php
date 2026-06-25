@extends('layouts.app')

@section('title', __('stockcount::lang.worksheet') . ' - ' . $session->name)

@section('css')
<style>
    .save-status-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1050;
        background: rgba(0, 0, 0, 0.8);
        color: #fff;
        padding: 10px 20px;
        border-radius: 5px;
        font-weight: bold;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        display: none;
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
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
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
        animation: scanline 2s linear infinite;
        pointer-events: none;
        z-index: 10;
    }
    @keyframes scanline {
        0%   { top: 15%; }
        50%  { top: 80%; }
        100% { top: 15%; }
    }
    #camera_result_feedback {
        margin-top: 10px;
        font-size: 14px;
        min-height: 22px;
    }
</style>
@endsection

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        @lang('stockcount::lang.worksheet'): {{ $session->name }}
        <small>@lang('stockcount::lang.location'): {{ $session->location->name ?? '' }}</small>
    </h1>
</section>

<section class="content">
    <!-- Barcode Scan Card -->
    <div class="worksheet-header row">
        <div class="col-md-6 col-xs-12">
            <div class="form-group">
                <label for="barcode_scanner"><strong><i class="fa fa-barcode"></i> @lang('stockcount::lang.scan_barcode') / SKU:</strong></label>
                <div class="input-group">
                    <input type="text" id="barcode_scanner" class="form-control input-lg" placeholder="Scan barcode or type SKU and press Enter" autofocus>
                    <span class="input-group-btn">
                        <button class="btn btn-primary btn-lg btn-flat" type="button" id="btn_search_barcode">
                            <i class="fa fa-search"></i>
                        </button>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xs-12 text-right" style="margin-top: 25px;">
            <a href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'index']) }}" class="btn btn-default btn-lg">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
            <a href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'show'], [$session->id]) }}" class="btn btn-primary btn-lg">
                <i class="fa fa-eye"></i> Back to Session
            </a>
            <button type="button" class="btn btn-warning btn-lg" id="btn_camera_scan">
                <i class="fa fa-camera"></i> Scan Camera
            </button>
            <button type="button" class="btn btn-success btn-lg" data-toggle="modal" data-target="#importExcelModal">
                <i class="fa fa-file-excel-o"></i> Import Excel
            </button>
        </div>
    </div>

    <!-- Worksheet Table -->
    @component('components.widget', ['class' => 'box-primary'])
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="worksheet_table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>SKU</th>
                        @if(!$session->blind_count)
                            <th>@lang('stockcount::lang.book_qty')</th>
                        @endif
                        <th style="width: 150px;">@lang('stockcount::lang.counted_qty')</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody id="worksheet_body">
                    @foreach($lines as $line)
                        @include('stockcount::partials.worksheet_row', ['line' => $line, 'session' => $session])
                    @endforeach
                </tbody>
            </table>
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1;"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="cameraScanModalLabel"><i class="fa fa-camera"></i> Scan Barcode with Camera</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted" style="font-size:13px;">Point your camera at a barcode. It will be detected and added automatically.</p>
                <div id="camera_preview_container">
                    <div id="reader"></div>
                    <div class="camera-scan-line"></div>
                </div>
                <div id="camera_result_feedback" class="text-center"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="btn_stop_camera"><i class="fa fa-stop"></i> Stop Camera</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                        <li>Make sure the sheet has columns for <strong>SKU</strong> and <strong>Counted Qty</strong> (notes column is optional).</li>
                        <li>You can download a template pre-filled with all active session items to fill in:</li>
                    </ul>
                    <a href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'downloadTemplate'], [$session->id]) }}" class="btn btn-info btn-xs">
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
@endsection

@section('javascript')
<!-- html5-qrcode library for camera barcode scanning -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    $(document).ready(function() {
        // ── Barcode text input scanning ────────────────────────────────
        $('#barcode_scanner').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                scanBarcode($('#barcode_scanner').val().trim());
            }
        });

        $('#btn_search_barcode').on('click', function() {
            scanBarcode($('#barcode_scanner').val().trim());
        });

        // ── Camera scanning ───────────────────────────────────────────
        var html5QrCode = null;
        var cameraActive = false;
        var lastScanned = '';
        var scanCooldown = false;

        $('#btn_camera_scan').on('click', function() {
            $('#cameraScanModal').modal('show');
        });

        $('#cameraScanModal').on('shown.bs.modal', function() {
            startCamera();
        });

        $('#cameraScanModal').on('hidden.bs.modal', function() {
            stopCamera();
        });

        $('#btn_stop_camera').on('click', function() {
            stopCamera();
            $('#cameraScanModal').modal('hide');
        });

        function startCamera() {
            if (cameraActive) return;
            $('#camera_result_feedback').html('<span class="text-muted"><i class="fa fa-spinner fa-spin"></i> Starting camera...</span>');

            html5QrCode = new Html5Qrcode('reader');

            Html5Qrcode.getCameras().then(function(devices) {
                if (!devices || devices.length === 0) {
                    $('#camera_result_feedback').html('<span class="text-danger"><i class="fa fa-times"></i> No camera found on this device.</span>');
                    return;
                }

                // Prefer back camera on mobile
                var cameraId = devices[devices.length - 1].id;

                html5QrCode.start(
                    cameraId,
                    { fps: 10, qrbox: { width: 250, height: 150 } },
                    function(decodedText) {
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
                        scanBarcode(decodedText, function() {
                            // Reset cooldown after 2s so same barcode can be scanned again
                            setTimeout(function() {
                                scanCooldown = false;
                                lastScanned = '';
                                $('#camera_result_feedback').html('<span class="text-muted">Ready to scan next item...</span>');
                            }, 2000);
                        });
                    },
                    function() { /* scan frame errors — ignore */ }
                ).then(function() {
                    cameraActive = true;
                    $('#camera_result_feedback').html('<span class="text-muted">Camera active — point at a barcode.</span>');
                }).catch(function(err) {
                    $('#camera_result_feedback').html('<span class="text-danger"><i class="fa fa-times"></i> Cannot access camera: ' + err + '</span>');
                });
            }).catch(function(err) {
                $('#camera_result_feedback').html('<span class="text-danger"><i class="fa fa-times"></i> Camera permission denied or unavailable.</span>');
            });
        }

        function stopCamera() {
            if (html5QrCode && cameraActive) {
                html5QrCode.stop().then(function() {
                    html5QrCode.clear();
                    cameraActive = false;
                    lastScanned = '';
                    scanCooldown = false;
                }).catch(function() {
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
                success: function(result) {
                    $('#barcode_scanner').val('').focus();
                    if (result.success) {
                        showSaveStatus('Saved ✔', 'check');
                        if (result.appended) {
                            $('#worksheet_body').prepend(result.row_html);
                        }
                        // Update qty in table either way
                        var input = $('#qty_' + result.line_id);
                        if (input.length) {
                            input.val(result.new_qty);
                        }
                        highlightRow(result.line_id);
                    } else {
                        showSaveStatus('Not Found ✖', 'exclamation-circle');
                        toastr.error(result.message || 'Barcode not found.');
                    }
                    if (typeof callback === 'function') callback(result);
                },
                error: function() {
                    $('#barcode_scanner').val('').focus();
                    showSaveStatus('Error ✖', 'times');
                    toastr.error('Error matching barcode.');
                    if (typeof callback === 'function') callback(null);
                }
            });
        }

        // ── Highlight scanned row ──────────────────────────────────────
        function highlightRow(line_id) {
            var row = $('#line_' + line_id);
            if (!row.length) return;
            row.addClass('scan-highlight');
            $('html, body').animate({ scrollTop: row.offset().top - 150 }, 500);
            setTimeout(function() { row.removeClass('scan-highlight'); }, 1500);
        }

        // ── Quantity +/- buttons ───────────────────────────────────────
        $(document).on('click', '.btn-qty', function() {
            var id     = $(this).data('id');
            var action = $(this).data('action');
            var input  = $('#qty_' + id);
            var val    = parseFloat(input.val()) || 0;
            if (action === 'plus') {
                input.val(val + 1).trigger('change');
            } else if (action === 'minus' && val > 0) {
                input.val(val - 1).trigger('change');
            }
        });

        // ── Auto-save on qty / note change ────────────────────────────
        var saveTimeout = null;
        $(document).on('change', '.input-qty, .input-note', function() {
            saveProgress($(this).data('id'));
        });

        function saveProgress(line_id) {
            var qty  = $('#qty_' + line_id).val();
            var note = $('#note_' + line_id).val();
            showSaveStatus('Saving...', 'spinner');
            if (saveTimeout) clearTimeout(saveTimeout);
            saveTimeout = setTimeout(function() {
                $.ajax({
                    method: "POST",
                    url: "{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'saveWorksheetProgress'], [$session->id]) }}",
                    dataType: "json",
                    data: { line_id: line_id, quantity: qty, note: note, _token: "{{ csrf_token() }}" },
                    success: function(result) {
                        showSaveStatus(result.success ? 'Saved ✔' : 'Error ✖', result.success ? 'check' : 'times');
                        if (!result.success) toastr.error('Failed to save progress.');
                    },
                    error: function() {
                        showSaveStatus('Error ✖', 'times');
                        toastr.error('Network error during auto-save.');
                    }
                });
            }, 500);
        }

        // ── Save-status floating toast ────────────────────────────────
        function showSaveStatus(text, icon) {
            var statusDiv  = $('#save_status');
            var statusText = $('#save_status_text');
            var spin = (icon === 'spinner') ? ' fa-spin' : '';
            statusText.html('<i class="fa fa-' + icon + spin + '"></i> ' + text);
            statusDiv.fadeIn(300);
            if (icon !== 'spinner') {
                setTimeout(function() { statusDiv.fadeOut(300); }, 2000);
            }
        }
    });
</script>
@endsection
