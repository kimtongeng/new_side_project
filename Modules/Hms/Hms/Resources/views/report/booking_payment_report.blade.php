@extends('layouts.app')
@section('title', __('hms::lang.booking_payment_report'))

@section('content')
    @include('hms::layouts.nav')

    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            @lang('hms::lang.booking_payment_report')
        </h1>
    </section>

    <section class="content no-print">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters')])
                    {!! Form::open(['url' => '#', 'method' => 'get', 'id' => 'booking_payment_report_form']) !!}
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('bpr_customer_id', __('contact.customer') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                {!! Form::select('customer_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('messages.all'), 'id' => 'bpr_customer_id']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('bpr_user_id', __('report.user') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                {!! Form::select('user_id', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('messages.all'), 'id' => 'bpr_user_id']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('bpr_room_type_id', __('hms::lang.type') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-bed"></i></span>
                                {!! Form::select('room_type_id', $room_types, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('messages.all'), 'id' => 'bpr_room_type_id']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('bpr_date_filter', __('report.date_range') . ':') !!}
                            {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'bpr_date_filter', 'readonly']) !!}
                        </div>
                    </div>
                    {!! Form::close() !!}
                @endcomponent
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="booking_payment_report_table">
                            <thead>
                                <tr>
                                    <th>@lang('hms::lang.booking_Id')</th>
                                    <th>@lang('purchase.ref_no')</th>
                                    <th>@lang('lang_v1.paid_on')</th>
                                    <th>@lang('sale.amount')</th>
                                    <th>@lang('contact.customer')</th>
                                    <th>@lang('lang_v1.contact_id')</th>
                                    <th>@lang('lang_v1.payment_method')</th>
                                    <th>@lang('report.user')</th>
                                    <th class="not-export">@lang('messages.action')</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr class="bg-gray font-17 footer-total text-center">
                                    <td colspan="3"><strong>@lang('sale.total'):</strong></td>
                                    <td><span class="display_currency" id="bpr_footer_total_amount" data-currency_symbol="true"></span></td>
                                    <td colspan="5"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endcomponent
            </div>
        </div>
    </section>

    <div class="modal fade view_register" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
@endsection

@section('javascript')
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            var booking_payment_report_table = $('#booking_payment_report_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader: false,
                aaSorting: [[2, 'desc']],
                ajax: {
                    url: '{{ url('hms/booking-payment-report') }}',
                    data: function (d) {
                        d.customer_id = $('#bpr_customer_id').val();
                        d.user_id = $('#bpr_user_id').val();
                        d.room_type_id = $('#bpr_room_type_id').val();
                        if ($('#bpr_date_filter').val()) {
                            d.start_date = $('#bpr_date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                            d.end_date = $('#bpr_date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
                        } else {
                            d.start_date = '';
                            d.end_date = '';
                        }
                    },
                },
                columns: [
                    { data: 'ref_no', name: 't.ref_no' },
                    { data: 'payment_ref_no', name: 'transaction_payments.payment_ref_no' },
                    { data: 'paid_on', name: 'transaction_payments.paid_on' },
                    { data: 'amount', name: 'transaction_payments.amount' },
                    { data: 'customer', name: 'c.name' },
                    { data: 'contact_id', name: 'c.contact_id' },
                    { data: 'method', name: 'transaction_payments.method' },
                    { data: 'user_name', name: 'user_name' },
                    { data: 'action', orderable: false, searchable: false },
                ],
                fnDrawCallback: function () {
                    var total_amount = sum_table_col($('#booking_payment_report_table'), 'paid-amount');
                    $('#bpr_footer_total_amount').text(total_amount);
                    __currency_convert_recursively($('#booking_payment_report_table'));
                },
            });

            if ($('#bpr_date_filter').length == 1) {
                $('#bpr_date_filter').daterangepicker(dateRangeSettings, function (start, end) {
                    $('#bpr_date_filter span').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                    booking_payment_report_table.ajax.reload();
                });
                $('#bpr_date_filter').on('cancel.daterangepicker', function () {
                    $('#bpr_date_filter').val('');
                    booking_payment_report_table.ajax.reload();
                });
            }

            $('#booking_payment_report_form').on('change', '#bpr_customer_id, #bpr_user_id, #bpr_room_type_id', function () {
                booking_payment_report_table.ajax.reload();
            });
        });
    </script>
@endsection
