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
        'draft' => __('stockcount::lang.draft'),
        'active' => __('stockcount::lang.active'),
        'completed' => __('stockcount::lang.completed'),
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

            @can('stock_count.create')
                <a class="btn btn-primary"
                    href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'create']) }}">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </a>
            @endcan
        </div>
        @endslot

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="stock_count_table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>@lang('stockcount::lang.reference_no')</th>
                        <th>@lang('stockcount::lang.session_name')</th>
                        <th>@lang('stockcount::lang.location')</th>
                        <th>@lang('stockcount::lang.status')</th>
                        <th>Total Items</th>
                        <th>Items Counted</th>
                        <th>@lang('stockcount::lang.blind_count')</th>
                        <th>Added By</th>
                        <th>Created At</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
        @endcomponent
    </section>

@endsection

@section('javascript')
    <script>
        $(document).ready(function () {
            var stock_count_table = $('#stock_count_table').DataTable({
                processing: true,
                serverSide: true,
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
                    { data: 'reference_no', name: 'reference_no' },
                    { data: 'name', name: 'name' },
                    { data: 'location.name', name: 'location.name', defaultContent: '' },
                    { data: 'status', name: 'status', searchable: false },
                    { data: 'total_items', name: 'total_items', orderable: false, searchable: false },
                    { data: 'items_counted', name: 'items_counted', orderable: false, searchable: false },
                    { data: 'blind_count', name: 'blind_count', orderable: false, searchable: false },
                    { data: 'creator.first_name', name: 'creator.first_name', defaultContent: '' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
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

            $(document).on('change', '#filter_location_id, #filter_status, #filter_created_by', function () {
                stock_count_table.ajax.reload();
            });

            $(document).on('click', 'a.delete_stock_count', function (e) {
                e.preventDefault();
                var url = $(this).data('href');
                swal({
                    title: LANG.sure,
                    text: "You won't be able to revert this count session!",
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
        });
    </script>
@endsection