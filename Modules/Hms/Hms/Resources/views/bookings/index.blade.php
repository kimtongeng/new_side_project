@extends('layouts.app')
@section('title', __('hms::lang.bookings'))
@section('content')
    @include('hms::layouts.nav')
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"> @lang('hms::lang.bookings')
        </h1>
        <p><i class="fa fa-info-circle"></i> @lang('hms::lang.bookings_help_text') </p>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('customer_id', __('contact.customer') . ':') !!}
                    {!! Form::select('customer_id', $customers, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('lang_v1.all'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('status', __('hms::lang.status') . ':') !!}
                    {!! Form::select('status', $status, null, [
                        'class' => 'form-control',
                        'style' => 'width:100%',
                        'placeholder' => __('lang_v1.all'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('filter_payment_status', __('hms::lang.payment_status') . ':') !!}
                    {!! Form::select(
                        'filter_payment_status',
                        [
                            'paid' => __('lang_v1.paid'),
                            'due' => __('lang_v1.due'),
                            'partial' => __('lang_v1.partial'),
                            'overdue' => __('lang_v1.overdue'),
                        ],
                        null,
                        ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')],
                    ) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('user_id', __('user.users') . ':') !!}
                    {!! Form::select('user_id', $users, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('lang_v1.all'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('date_range_filter', __('messages.filter_by_date') . ':') !!}
                    {!! Form::text('date_range_filter', null, [
                        'class' => 'form-control',
                        'placeholder' => __('messages.filter_by_date'),
                        'readonly' => true,
                    ]) !!}
                </div>
            </div>
        @endcomponent
        @component('components.widget')
            <div class="box-tools tw-flex tw-justify-end tw-gap-2.5 tw-mb-4">
                @can('hms.add_booking')
                        <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                            href="{{ action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'create']) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 5l0 14" />
                                <path d="M5 12l14 0" />
                            </svg> @lang('messages.add')
                        </a>
                @endcan
            </div>
            <table class="table table-bordered table-striped" id="bookings_table">
                <thead>
                    <tr>
                        <th>
                            @lang('hms::lang.booking_Id')
                        </th>

                        <th>
                            @lang('hms::lang.stay')
                        </th>
                        <th>
                            @lang('hms::lang.customer')
                        </th>
                        <th>
                            @lang('user.users')
                        </th>
                        <th>
                            @lang('hms::lang.status')
                        </th>
                        <th>
                            @lang('hms::lang.payment_status')
                        </th>
                        <th>
                            @lang('lang_v1.payment_method')
                        </th>
                        <th>
                            @lang('hms::lang.total_amount')
                        </th>
                        <th>
                            @lang('hms::lang.total_paid')
                        </th>
                        <th>
                            @lang('hms::lang.due')
                        </th>
                        <th>
                            @lang('lang_v1.created_at')
                        </th>
                        <th>
                            @lang('messages.action')
                        </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr class="bg-gray font-17 footer-total text-center">
                        <td colspan="7"><strong>@lang('sale.total'):</strong></td>
                        <td class="footer_total_amount"></td>
                        <td class="footer_total_paid"></td>
                        <td class="footer_total_remaining"></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        @endcomponent
        <!-- Add HMS Extra Modal -->
        <div class="modal fade check_in_out" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
        </div>
    </section>
    <div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        <!-- /.content -->
    @endsection

    @section('javascript')
        <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                bookings_table = $('#bookings_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    ajax: {
                        url: "{{ action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'index']) }}",
                        "data": function(d) {
                            d.customer_id = $('#customer_id').val();
                            d.status = $('#status').val();
                            d.payment_status = $('#filter_payment_status').val();
                            d.user_id = $('#user_id').val();
                            
                            // Add date range filter
                            if ($('#date_range_filter').val()) {
                                d.start_date = $('#date_range_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                                d.end_date = $('#date_range_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
                            }
                        },
                    },
                    aaSorting: [
                        [11, 'desc']
                    ],
                    columns: [{
                            data: 'ref_no',
                            name: 'ref_no'
                        },
                        {
                            data: 'stay',
                            name: 'stay',
                            orderable: false,
                            "searchable": false
                        },
                        {
                            data: 'c_name',
                            name: 'c.name',
                        },
                        {
                            data: 'user_name',
                            name: 'u.first_name',
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'payment_status',
                            name: 'payment_status',
                        },
                        {
                            data: 'payment_methods',
                            orderable: false,
                            "searchable": false
                        },
                        {
                            data: 'final_total',
                            name: 'final_total'
                        },
                        {
                            data: 'total_paid',
                            name: 'total_paid',
                            "searchable": false
                        },
                        {
                            data: 'total_remaining',
                            name: 'total_remaining'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            sorting: false,
                        }
                    ],
                    "footerCallback": function(row, data, start, end, display) {
                        var footer_total_amount = 0;
                        var footer_total_paid = 0;
                        var footer_total_remaining = 0;
                        
                        for (var r in data) {
                            footer_total_amount += $(data[r].final_total).data('orig-value') ? parseFloat($(data[r].final_total).data('orig-value')) : 0;
                            footer_total_paid += $(data[r].total_paid).data('orig-value') ? parseFloat($(data[r].total_paid).data('orig-value')) : 0;

                            footer_total_remaining += $(data[r].total_remaining).data('orig-value') ? parseFloat($(data[r].total_remaining).data('orig-value')) : 0;
                        }

                        $('.footer_total_amount').html(__currency_trans_from_en(footer_total_amount));
                        $('.footer_total_paid').html(__currency_trans_from_en(footer_total_paid));

                        $('.footer_total_remaining').html(__currency_trans_from_en(footer_total_remaining));
                    },
                });

                // Initialize date range picker
                $('#date_range_filter').daterangepicker(dateRangeSettings, function(start, end) {
                    $('#date_range_filter').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                    bookings_table.ajax.reload();
                });
                
                $('#date_range_filter').on('cancel.daterangepicker', function(ev, picker) {
                    $('#date_range_filter').val('');
                    bookings_table.ajax.reload();
                });

                $(document).on('change', '#customer_id, #status, #filter_payment_status, #user_id', function() {
                    bookings_table.ajax.reload();
                });

                $(document).on('click', '.btn-modal-checkIn', function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: $(this).attr('href'),
                        dataType: 'html',
                        success: function(result) {
                            $('.check_in_out')
                                .html(result)
                                .modal('show');
                        },
                    });
                });

                $(document).on('click', '.delete-hms-booking', function(e) {
                    e.preventDefault();
                    swal({
                        title: LANG.sure,
                        icon: 'warning',
                        buttons: true,
                        dangerMode: true,
                    }).then(willDelete => {
                        if (willDelete) {
                            $.ajax({
                                method: 'DELETE',
                                url: $(this).attr('href'),
                                dataType: 'json',
                                success: function(result) {
                                    if (result.success == true) {
                                        toastr.success(result.msg);
                                        bookings_table.ajax.reload();
                                    } else {
                                        toastr.error(result.msg);
                                    }
                                },
                            });
                        }
                    });
                });

                $(".check_in_out").on("show.bs.modal", function() {
                    var currentDate = new Date();
                    var currentDateTime = moment(currentDate);

                    // Initialize checkout datetimepicker with 12 PM default
                    if ($('.checkout_date_picker').length > 0) {
                        var checkoutDate = new Date();
                        checkoutDate.setHours(12, 0, 0, 0);
                        var checkoutDateTime = moment(checkoutDate);

                        $('.checkout_date_picker').datetimepicker({
                            format: moment_date_format + ' ' + moment_time_format,
                            ignoreReadonly: true,
                            defaultDate: checkoutDateTime
                        });
                    }

                    // Initialize check-in datetimepicker with current date/time
                    $('.date_picker').not('.checkout_date_picker').datetimepicker({
                        format: moment_date_format + ' ' + moment_time_format,
                        ignoreReadonly: true,
                        defaultDate: currentDateTime
                    });
                });

            });
        </script>
    @endsection
