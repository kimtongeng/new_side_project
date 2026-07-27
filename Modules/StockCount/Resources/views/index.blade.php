@extends('layouts.app')

@section('title', __('stockcount::lang.stock_counts'))

@section('css')
    <style>
        .info-box-summary {
            border-radius: 8px !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
            overflow: hidden;
            border: none !important;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .info-box-summary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .info-box-summary.bg-blue {
            background: linear-gradient(135deg, #1e3c72, #2a5298) !important;
        }

        .info-box-summary.bg-green {
            background: linear-gradient(135deg, #11998e, #38ef7d) !important;
        }

        .info-box-summary.bg-aqua {
            background: linear-gradient(135deg, #00c6ff, #0072ff) !important;
        }

        .info-box-summary .info-box-icon {
            background: rgba(0, 0, 0, 0.1) !important;
            color: #fff !important;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 90px;
            flex-shrink: 0;
            float: none !important;
            height: auto !important;
            line-height: normal !important;
        }

        .info-box-summary .info-box-text {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            font-size: 11px !important;
            letter-spacing: 0.8px !important;
            margin-bottom: 4px;
        }

        .info-box-summary .info-box-number {
            color: #ffffff !important;
            font-size: 22px !important;
            font-weight: 700 !important;
        }

        .info-box-summary .progress-description {
            color: rgba(255, 255, 255, 0.9) !important;
            font-size: 13px !important;
            margin-top: 5px;
        }

        .info-box-summary .progress-description strong {
            color: #ffffff !important;
        }

        /* Equal-height summary boxes regardless of content length */
        .summary-boxes-row {
            display: flex;
            flex-wrap: wrap;
        }

        .summary-boxes-row>[class*="col-"] {
            display: flex;
        }

        .summary-boxes-row .info-box-summary {
            width: 100%;
            display: flex;
            align-items: stretch;
            min-height: 90px;
        }

        .summary-boxes-row .info-box-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Prevent dropdowns from being clipped by responsive table container on desktop */
        @media (min-width: 768px) {
            .table-responsive {
                overflow: visible !important;
            }
        }
        
        /* Ensure first action column is not squished */
        #stock_count_table th:first-child,
        #stock_count_table td:first-child {
            width: 85px !important;
            min-width: 85px !important;
            text-align: center;
        }

        /* Custom Status Filter Nav Tabs */
        .stock-count-nav-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 18px;
            padding: 5px;
            background: #f1f5f9;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            list-style: none;
        }

        .stock-count-nav-tabs .nav-item {
            list-style: none;
            margin: 0;
        }

        .stock-count-nav-tabs .stock-count-tab-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            background: transparent;
            border-radius: 7px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .stock-count-nav-tabs .stock-count-tab-link:hover {
            color: #1e293b;
            background: rgba(255, 255, 255, 0.7);
        }

        .stock-count-nav-tabs .stock-count-tab-link.active {
            color: #0f172a;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            font-weight: 700;
        }

        .stock-count-nav-tabs .stock-count-tab-link .tab-icon {
            font-size: 14px;
        }

        .stock-count-nav-tabs .stock-count-tab-link.active[data-status="pending"] .tab-icon {
            color: #f59e0b;
        }
        .stock-count-nav-tabs .stock-count-tab-link.active[data-status="in_progress"] .tab-icon {
            color: #3b82f6;
        }
        .stock-count-nav-tabs .stock-count-tab-link.active[data-status="completed"] .tab-icon {
            color: #10b981;
        }
        .stock-count-nav-tabs .stock-count-tab-link.active[data-status="reconciled"] .tab-icon {
            color: #8b5cf6;
        }
    </style>
@endsection

@section('content')
    <section class="content-header no-print">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            @lang('stockcount::lang.stock_counts')
        </h1>
    </section>

    <section class="content no-print">
        @component('components.filters', ['title' => __('report.filters'), 'closed' => false])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('filter_location_id', __('purchase.business_location') . ':') !!}
                {!! Form::select('filter_location_id', $business_locations, null, [
        'class' => 'form-control select2',
        'style' => 'width:100%',
        'placeholder' => __('lang_v1.all'),
    ]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('filter_status', __('sale.status') . ':') !!}
                {!! Form::select('filter_status', [
                    'pending' => __('stockcount::lang.pending'),
                    'in_progress' => __('stockcount::lang.in_progress'),
                    'completed' => __('stockcount::lang.completed'),
                    'reconciled' => __('stockcount::lang.reconciled'),
                    'cancelled' => __('stockcount::lang.cancelled')
                ], null, [
                    'class' => 'form-control select2',
                    'style' => 'width:100%',
                    'placeholder' => __('lang_v1.all'),
                ]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('filter_created_by', 'Added By:') !!}
                {!! Form::select('filter_created_by', $users, null, [
        'class' => 'form-control select2',
        'style' => 'width:100%',
        'placeholder' => __('lang_v1.all'),
    ]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('filter_date_range', null, [
        'placeholder' => __('lang_v1.select_a_date_range'),
        'class' => 'form-control',
        'readonly'
    ]) !!}
            </div>
        </div>
        @endcomponent

        <!-- Summary Stats Boxes -->
        <div class="row summary-boxes-row">
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="info-box bg-blue text-white info-box-summary">
                    <span class="info-box-icon"><i class="fa fa-sync"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Active Sessions</span>
                        <span class="info-box-number" id="stat_active_sessions"><i class="fa fa-spinner fa-spin"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="info-box bg-green text-white info-box-summary">
                    <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Completed Sessions</span>
                        <span class="info-box-number" id="stat_completed_sessions"><i
                                class="fa fa-spinner fa-spin"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="info-box bg-aqua text-white info-box-summary">
                    <span class="info-box-icon"><i class="fa fa-chart-pie"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Items Counted</span>
                        <span class="info-box-number" id="stat_total_progress"><i class="fa fa-spinner fa-spin"></i></span>
                        <span class="progress-description text-white">
                            Overall Progress: <strong id="stat_progress_percent">0%</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        @component('components.widget', ['class' => 'box-primary', 'title' => __('stockcount::lang.all_stock_counts')])
        @slot('tool')
        <div class="box-tools" style="display: flex; gap: 5px; align-items: center;">
            @if(auth()->user()->hasRole('Admin#' . session()->get('user.business_id')) || auth()->user()->can('superadmin') || auth()->user()->can('stock_count.settings'))
                <a class="btn btn-default btn-sm"
                    href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'getSettings']) }}">
                    <i class="fa fa-cog"></i> Settings
                </a>
            @endif
            @can('stock_count.create')
                <a class="btn btn-primary btn-sm"
                    href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'create']) }}">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </a>
            @endcan
        </div>
        @endslot

        <!-- Status Filter Tabs -->
        <ul class="stock-count-nav-tabs" id="status_tabs_nav">
            <li class="nav-item">
                <a class="stock-count-tab-link active" data-status="">
                    <i class="fa fa-th-large tab-icon"></i> @lang('lang_v1.all')
                </a>
            </li>
            <li class="nav-item">
                <a class="stock-count-tab-link" data-status="pending">
                    <i class="fa fa-clock tab-icon"></i> @lang('stockcount::lang.pending')
                </a>
            </li>
            <li class="nav-item">
                <a class="stock-count-tab-link" data-status="in_progress">
                    <i class="fa fa-sync tab-icon"></i> @lang('stockcount::lang.in_progress')
                </a>
            </li>
            <li class="nav-item">
                <a class="stock-count-tab-link" data-status="completed">
                    <i class="fa fa-check-circle tab-icon"></i> @lang('stockcount::lang.completed')
                </a>
            </li>
            <li class="nav-item">
                <a class="stock-count-tab-link" data-status="reconciled">
                    <i class="fa fa-balance-scale tab-icon"></i> @lang('stockcount::lang.reconciled')
                </a>
            </li>
            <li class="nav-item">
                <a class="stock-count-tab-link" data-status="cancelled">
                    <i class="fa fa-times-circle tab-icon"></i> @lang('stockcount::lang.cancelled')
                </a>
            </li>
        </ul>

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="stock_count_table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>@lang('messages.action')</th>
                        <th>@lang('stockcount::lang.reference_no')</th>
                        <th>@lang('stockcount::lang.session_name')</th>
                        <th>@lang('stockcount::lang.location')</th>
                        <th>@lang('stockcount::lang.status')</th>
                        <th>Completion</th>
                        <th>Total Items</th>
                        <th>Items Counted</th>
                        <th>@lang('stockcount::lang.blind_count')</th>
                        <th>Added By</th>
                        <th>Created At</th>
                    </tr>
                </thead>
            </table>
        </div>
        @endcomponent

        <!-- Update Status Modal -->
        <div class="modal fade" id="update_status_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open(['url' => '', 'method' => 'post', 'id' => 'update_status_modal_form']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Update Status</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="session_id" id="modal_session_id">
                        <div class="form-group">
                            {!! Form::label('modal_status', 'Status:') !!}
                            <select name="status" id="modal_status" class="form-control" style="width: 100%;">
                                <option value="pending">@lang('stockcount::lang.pending')</option>
                                <option value="in_progress">@lang('stockcount::lang.in_progress')</option>
                                <option value="completed">@lang('stockcount::lang.completed')</option>
                                <option value="reconciled">@lang('stockcount::lang.reconciled')</option>
                                <option value="cancelled">@lang('stockcount::lang.cancelled')</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
        <!-- Compare Sessions Modal -->
        <div class="modal fade" id="compare_sessions_modal" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open(['url' => action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'compare']), 'method' => 'get', 'id' => 'compare_sessions_form']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Compare Worksheet Sessions</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Session A (Current):</label>
                            <input type="text" id="compare_session_a_name" class="form-control" readonly style="background-color: #eee;">
                            <input type="hidden" name="session_1" id="compare_session_a_id">
                        </div>
                        <div class="form-group">
                            <label for="compare_session_b_id">Select Session B to Compare with:*</label>
                            <select name="session_2" id="compare_session_b_id" class="form-control select2" style="width: 100%;">
                                <option value="">Please Select</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Compare</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>

@endsection

@section('javascript')
    <script>
        $(document).ready(function () {
            var stock_count_table = $('#stock_count_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [[10, 'desc']],
                dom: '<"row margin-bottom-20 text-center"<"col-sm-2"l><"col-sm-7"B><"col-sm-3"f> r>tip',
                buttons: [
                    {
                        extend: 'csv',
                        text: '<i class="fa fa-file-csv"></i> Export CSV',
                        className: 'tw-dw-btn-xs tw-dw-btn tw-dw-btn-outline tw-mx-1'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fa fa-file-excel"></i> Export Excel',
                        className: 'tw-dw-btn-xs tw-dw-btn tw-dw-btn-outline tw-mx-1'
                    },
                    {
                        text: '<i class="fa fa-print"></i> Print',
                        className: 'tw-dw-btn-xs tw-dw-btn tw-dw-btn-outline tw-mx-1',
                        action: function (e, dt, node, config) {
                            triggerPrintPdfAll();
                        }
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fa fa-columns"></i> Column visibility',
                        className: 'tw-dw-btn-xs tw-dw-btn tw-dw-btn-outline tw-mx-1'
                    },
                    {
                        text: '<i class="fa fa-file-pdf"></i> Export PDF',
                        className: 'tw-dw-btn-xs tw-dw-btn tw-dw-btn-outline tw-mx-1',
                        action: function (e, dt, node, config) {
                            triggerDownloadPdfAll();
                        }
                    }
                ],
                ajax: {
                    url: "{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'index']) }}",
                    data: function (d) {
                        d.location_id = $('#filter_location_id').val();
                        d.status = $('#filter_status').val();
                        d.created_by = $('#filter_created_by').val();
                        if ($('#filter_date_range').val()) {
                            var start = $('#filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                            var end = $('#filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                    }
                },
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'reference_no', name: 'reference_no' },
                    { data: 'name', name: 'name' },
                    { data: 'location.name', name: 'location.name', defaultContent: '' },
                    { data: 'status', name: 'status', searchable: false },
                    { data: 'completion', name: 'completion', orderable: false, searchable: false },
                    { data: 'total_items', name: 'total_items', orderable: false, searchable: false },
                    { data: 'items_counted', name: 'items_counted', orderable: false, searchable: false },
                    { data: 'blind_count', name: 'blind_count', orderable: false, searchable: false },
                    { data: 'creator.first_name', name: 'creator.first_name', defaultContent: '' },
                    { data: 'created_at', name: 'created_at' }
                ],
                rawColumns: ['action', 'status', 'created_at']
            });

            // Update stats dynamically when DataTable AJAX completes
            stock_count_table.on('xhr.dt', function (e, settings, json, xhr) {
                if (json && json.stats) {
                    $('#stat_active_sessions').text(json.stats.active_sessions);
                    $('#stat_completed_sessions').text(json.stats.completed_sessions);
                    $('#stat_total_progress').text(json.stats.total_counted + ' / ' + json.stats.total_items);
                    $('#stat_progress_percent').text(json.stats.progress_percent + '%');
                }
            });

            $('#filter_date_range').daterangepicker(
                dateRangeSettings,
                function (start, end) {
                    $('#filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                    stock_count_table.ajax.reload();
                }
            );
            $('#filter_date_range').on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
                stock_count_table.ajax.reload();
            });

            $(document).on('change', '#filter_location_id, #filter_created_by', function () {
                stock_count_table.ajax.reload();
            });

            $(document).on('change', '#filter_status', function () {
                var val = $(this).val() || '';
                $('.stock-count-tab-link').removeClass('active');
                $('.stock-count-tab-link[data-status="' + val + '"]').addClass('active');
                stock_count_table.ajax.reload();
            });

            $(document).on('click', '.stock-count-tab-link', function (e) {
                e.preventDefault();
                var status = $(this).data('status');
                $('.stock-count-tab-link').removeClass('active');
                $(this).addClass('active');
                $('#filter_status').val(status).trigger('change');
            });

            function triggerPrintPdfAll() {
                var params = {
                    location_id: $('#filter_location_id').val() || '',
                    status: $('#filter_status').val() || '',
                    created_by: $('#filter_created_by').val() || ''
                };
                if ($('#filter_date_range').val()) {
                    params.start_date = $('#filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    params.end_date = $('#filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                }
                var url = "{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'printPdfAll']) }}?" + $.param(params);

                $.ajax({
                    method: 'GET',
                    url: url,
                    dataType: 'json',
                    success: function (result) {
                        if (result.success == 1 && result.receipt && result.receipt.html_content) {
                            if ($('#receipt_section').length === 0) {
                                $('body').append('<div id="receipt_section"></div>');
                            }
                            $('#receipt_section').html(result.receipt.html_content);
                            if (typeof __currency_convert_recursively === 'function') {
                                __currency_convert_recursively($('#receipt_section'));
                            }

                            var title = document.title;
                            if (result.print_title) {
                                document.title = result.print_title;
                            }

                            if (typeof __print_receipt === 'function') {
                                __print_receipt('receipt_section');
                            } else {
                                window.print();
                            }

                            setTimeout(function () {
                                document.title = title;
                            }, 1500);
                        } else {
                            toastr.error('Failed to load print template');
                        }
                    },
                });
            }

            function triggerDownloadPdfAll() {
                var params = {
                    location_id: $('#filter_location_id').val() || '',
                    status: $('#filter_status').val() || '',
                    created_by: $('#filter_created_by').val() || '',
                    download: 1
                };
                if ($('#filter_date_range').val()) {
                    params.start_date = $('#filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    params.end_date = $('#filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                }
                var url = "{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'printPdfAll']) }}?" + $.param(params);
                window.location.href = url;
            }

            $(document).on('click', '#btn_print_pdf_all', function (e) {
                e.preventDefault();
                triggerPrintPdfAll();
            });

            $(document).on('click', 'a.delete_stock_count', function (e) {
                e.preventDefault();
                var url = $(this).data('href');
                var status = $(this).data('status');

                var warningText = "You won't be able to revert this count session!";
                if (status === 'completed' || status === 'approved') {
                    warningText = "⚠️ WARNING: This stock count is COMPLETED. Deleting it will remove the session record, but live product stock levels will NOT be reverted!";
                }

                swal({
                    title: LANG.sure,
                    text: warningText,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            method: "DELETE",
                            url: url,
                            dataType: "json",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function (result) {
                                if (result.success) {
                                    toastr.success(result.msg);
                                    stock_count_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });

            $('#update_status_modal').on('show.bs.modal shown.bs.modal hidden.bs.modal', function () {
                var $btn = $(this).find('button[type="submit"]');
                if (typeof __enable_submit_button === 'function') {
                    __enable_submit_button($btn);
                } else {
                    $btn.removeAttr('disabled').removeAttr('disable').prop('disabled', false);
                }
            });

            $(document).on('click', '.btn_update_status', function (e) {
                e.preventDefault();
                var session_id = $(this).data('session_id');
                var status = $(this).data('status');
                var action = $(this).data('href');
                
                $('#modal_session_id').val(session_id);

                if ($('#modal_status option[value="' + status + '"]').length > 0) {
                    $('#modal_status').val(status);
                } else if (status === 'active' || status === 'draft') {
                    $('#modal_status').val('pending');
                } else if (status === 'approved' || status === 'reviewed' || status === 'reconcile') {
                    $('#modal_status').val('reconciled');
                } else {
                    $('#modal_status').val($('#modal_status option:first').val());
                }

                if ($('#modal_status').hasClass('select2-hidden-accessible')) {
                    $('#modal_status').trigger('change');
                }

                $('#update_status_modal_form').attr('action', action);
                var $btn = $('#update_status_modal_form').find('button[type="submit"]');
                if (typeof __enable_submit_button === 'function') {
                    __enable_submit_button($btn);
                } else {
                    $btn.removeAttr('disabled').removeAttr('disable').prop('disabled', false);
                }
                
                $('#update_status_modal').modal('show');
            });
            
            $(document).on('submit', '#update_status_modal_form', function(e) {
                e.preventDefault();
                var $form = $(this);
                var data = $form.serialize();
                var url = $form.attr('action');
                var statusVal = $('#modal_status').val();
                var statusText = $('#modal_status option:selected').text().trim();
                var $btn = $form.find('button[type="submit"]');

                function enableBtn() {
                    if (typeof __enable_submit_button === 'function') {
                        __enable_submit_button($btn);
                    } else {
                        $btn.removeAttr('disabled').removeAttr('disable').prop('disabled', false);
                    }
                }

                function disableBtn() {
                    if (typeof __disable_submit_button === 'function') {
                        __disable_submit_button($btn);
                    } else {
                        $btn.attr('disabled', 'disabled').prop('disabled', true);
                    }
                }

                var needsConfirmation = ['completed', 'cancelled', 'reconciled', 'reconcile'].indexOf(statusVal) !== -1;

                function executeSubmit() {
                    disableBtn();
                    $.ajax({
                        method: 'POST',
                        url: url,
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            enableBtn();
                            if (result.success) {
                                toastr.success(result.msg);
                                $('#update_status_modal').modal('hide');
                                if (typeof stock_count_table !== 'undefined') {
                                    stock_count_table.ajax.reload();
                                }
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                        error: function(jqXHR) {
                            enableBtn();
                            var msg = (jqXHR.responseJSON && jqXHR.responseJSON.msg) ? jqXHR.responseJSON.msg : 'Something went wrong';
                            toastr.error(msg);
                        }
                    });
                }

                if (needsConfirmation) {
                    swal({
                        title: typeof LANG !== 'undefined' && LANG.sure ? LANG.sure : "Are you sure?",
                        text: "Are you sure you want to change status to " + statusText + "?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willProceed) => {
                        if (willProceed) {
                            executeSubmit();
                        } else {
                            enableBtn();
                        }
                    });
                } else {
                    executeSubmit();
                }
            });

            $(document).on('click', '.btn_compare_worksheet', function (e) {
                e.preventDefault();
                var session_id = $(this).data('session_id');
                var session_name = $(this).data('session_name');

                $('#compare_session_a_id').val(session_id);
                $('#compare_session_a_name').val(session_name);

                // Fetch other sessions to compare
                $.ajax({
                    url: "{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'getAllSessionsJson']) }}",
                    data: { exclude_id: session_id },
                    dataType: 'json',
                    success: function (data) {
                        var select = $('#compare_session_b_id');
                        select.empty();
                        select.append('<option value="">Please Select</option>');
                        $.each(data, function (index, item) {
                            var text = item.name + (item.reference_no ? ' (' + item.reference_no + ')' : '');
                            select.append('<option value="' + item.id + '">' + text + '</option>');
                        });
                        if (select.hasClass('select2-hidden-accessible')) {
                            select.select2('destroy');
                        }
                        select.select2();
                        select.trigger('change');
                        $('#compare_sessions_modal').modal('show');
                    }
                });
            });

            $(document).on('submit', '#compare_sessions_form', function (e) {
                var session_2 = $('#compare_session_b_id').val();
                if (!session_2) {
                    e.preventDefault();
                    toastr.error('Please select Session B to compare.');
                    return false;
                }
            });
        });
    </script>
@endsection