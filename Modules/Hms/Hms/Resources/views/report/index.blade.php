@extends('layouts.app')
@section('title', __('hms::lang.reports'))

@section('content')
    @include('hms::layouts.nav')

    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            @lang('hms::lang.reports')
        </h1>
        <p><i class="fa fa-info-circle"></i> @lang('hms::lang.report_help_text')</p>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fas fa-chart-bar"></i> @lang('hms::lang.booking_stats_report')
                        </h3>
                    </div>
                    <div class="box-body">
                        <p>@lang('hms::lang.booking_stats_report_desc')</p>
                    </div>
                    <div class="box-footer text-right">
                        <a href="{{ action([\Modules\Hms\Http\Controllers\HmsReportController::class, 'bookingStatsReport']) }}"
                           class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm">
                            <i class="fa fa-eye"></i> @lang('messages.view')
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fas fa-money-bill-alt"></i> @lang('hms::lang.booking_payment_report')
                        </h3>
                    </div>
                    <div class="box-body">
                        <p>@lang('hms::lang.booking_payment_report_desc')</p>
                    </div>
                    <div class="box-footer text-right">
                        <a href="{{ action([\Modules\Hms\Http\Controllers\HmsReportController::class, 'bookingPaymentReport']) }}"
                           class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm">
                            <i class="fa fa-eye"></i> @lang('messages.view')
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
