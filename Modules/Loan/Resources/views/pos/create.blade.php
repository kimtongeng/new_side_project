@extends('layouts.app')

@section('title', __('Loan::lang.pos_sale'))

@section('content')
<section class="content no-print">
    <!-- Connectivity Status Indicator -->
    <div class="alert alert-info" id="connectivity-status">
        <i class="fa fa-spinner fa-spin"></i> @lang('Loan::lang.checking_connectivity')...
    </div>

    <input type="hidden" id="amount_rounding_method" value="{{ $pos_settings['amount_rounding_method'] ?? '' }}">
    @if (!empty($pos_settings['allow_overselling']))
        <input type="hidden" id="is_overselling_allowed">
    @endif
    @if (session('business.enable_rp') == 1)
        <input type="hidden" id="reward_point_enabled">
    @endif
    @php
        $is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
        $is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
    @endphp
    {!! Form::open([
    'url' => action([\Modules\Loan\Http\Controllers\LoanPosController::class, 'store']),
    'method' => 'post',
    'id' => 'add_pos_sell_form',
]) !!}
    <div class="row mb-12">
        <div class="col-md-12 tw-pt-0 tw-mb-14">
            <div
                class="row tw-flex lg:tw-flex-row md:tw-flex-col sm:tw-flex-col tw-flex-col tw-items-start md:tw-gap-4">
                <div
                    class="tw-px-3 tw-w-full lg:tw-px-0 lg:tw-pr-0 @if(empty($pos_settings['hide_product_suggestion'])) lg:tw-w-[60%] @else lg:tw-w-[100%] @endif">
                    <div
                        class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-rounded-2xl tw-bg-white tw-mb-2 md:tw-mb-8 tw-p-2">
                        <div class="box-body pb-0">
                            {!! Form::hidden('location_id', $default_location->id ?? null, [
    'id' => 'location_id',
    'data-receipt_printer_type' => !empty($default_location->receipt_printer_type)
        ? $default_location->receipt_printer_type
        : 'browser',
    'data-default_payment_accounts' => $default_location->default_payment_accounts ?? '',
]) !!}
                            <!-- sub_type -->
                            {!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
                            <input type="hidden" id="item_addition_method"
                                value="{{ $business_details->item_addition_method }}">
                            @include('Loan::pos.partials.pos_form')

                            @include('Loan::pos.partials.pos_form_totals')

                            @include('Loan::pos.partials.payment_modal')

                            @if (empty($pos_settings['disable_suspend']))
                                @include('Loan::pos.partials.suspend_note_modal')
                            @endif

                            @if (empty($pos_settings['disable_recurring_invoice']))
                                @include('Loan::pos.partials.recurring_invoice_modal')
                            @endif
                        </div>
                    </div>
                </div>
                @if (empty($pos_settings['hide_product_suggestion']) && !isMobile())
                    <div class="md:tw-no-padding tw-w-full lg:tw-w-[40%] tw-px-5">
                        @include('Loan::pos.partials.pos_sidebar')
                    </div>
                @endif
            </div>
        </div>
    </div>
    @include('Loan::pos.partials.pos_form_actions')
    {!! Form::close() !!}
</section>

<!-- This will be printed -->
<section class="invoice print_section" id="receipt_section"></section>
<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    @include('contact.create', ['quick_add' => true])
</div>
@if (empty($pos_settings['hide_product_suggestion']) && isMobile())
    @include('Loan::pos.partials.mobile_product_suggestions')
@endif
<!-- /.content -->
<div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

<div class="modal fade" id="expense_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

@include('Loan::pos.partials.configure_search_modal')

@include('Loan::pos.partials.recent_transactions_modal')

@include('Loan::pos.partials.weighing_scale_modal')

@stop

@section('css')
<!-- include module css -->
@if (!empty($pos_module_data))
    @foreach ($pos_module_data as $key => $value)
        @if (!empty($value['module_css_path']))
            @includeIf($value['module_css_path'])
        @endif
    @endforeach
@endif
<style type="text/css">
    #connectivity-status {
        position: fixed;
        top: 10px;
        right: 10px;
        z-index: 1000;
        padding: 10px;
    }

    .online {
        background-color: #28a745 !important;
        color: white !important;
    }

    .offline {
        background-color: #dc3545 !important;
        color: white !important;
    }
</style>
@stop

@section('javascript')
    <!-- Include Dexie.js for IndexedDB -->
    <script src="https://unpkg.com/dexie@3/dist/dexie.min.js"></script>
    <script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
    @include('Loan::pos.partials.keyboard_shortcuts')

    <!-- Call restaurant module if defined -->
    @if (
            in_array('tables', $enabled_modules) ||
            in_array('modifiers', $enabled_modules) ||
            in_array('service_staff', $enabled_modules)
        )
        <script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <!-- include module js -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
        @endforeach
    @endif

    <script type="text/javascript">
        // Initialize Dexie database
        const db = new Dexie('LoanPosOfflineDB');
        db.version(1).stores({
            transactions: 'offline_id,transaction_data'
        });

        // Check connectivity status
        function checkConnectivity() {
            $.ajax({
                url: '{{ action([\Modules\Loan\Http\Controllers\LoanPosOfflineController::class, 'checkConnectivity']) }}',
                method: 'GET',
                success: function (response) {
                    const statusElement = $('#connectivity-status');
                    if (response.is_online) {
                        statusElement.removeClass('alert-info alert-danger offline').addClass('alert-success online');
                        statusElement.html('<i class="fa fa-wifi"></i> @lang("Loan::lang.connected")');
                        syncOfflineTransactions();
                    } else {
                        statusElement.removeClass('alert-info alert-success online').addClass('alert-danger offline');
                        statusElement.html('<i class="fa fa-exclamation-triangle"></i> @lang("Loan::lang.offline_mode")');
                    }
                },
                error: function () {
                    const statusElement = $('#connectivity-status');
                    statusElement.removeClass('alert-info alert-success online').addClass('alert-danger offline');
                    statusElement.html('<i class="fa fa-exclamation-triangle"></i> @lang("Loan::lang.offline_mode")');
                }
            });
        }

        // Sync offline transactions
        function syncOfflineTransactions() {
            db.transactions.toArray().then(transactions => {
                if (transactions.length === 0) return;

                $.ajax({
                    url: '{{ action([\Modules\Loan\Http\Controllers\LoanPosOfflineController::class, 'syncOfflineTransactions']) }}',
                    method: 'POST',
                    data: {
                        offline_transactions: transactions.map(t => t.transaction_data),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.success) {
                            response.synced_transactions.forEach(sync => {
                                db.transactions.where('offline_id').equals(sync.offline_id).delete();
                            });
                            toastr.success(response.msg);
                        } else {
                            toastr.error(response.msg);
                        }
                    },
                    error: function () {
                        toastr.error('@lang("messages.something_went_wrong")');
                    }
                });
            });
        }

        // Handle form submission for offline mode
        $(document).ready(function () {
            checkConnectivity();
            setInterval(checkConnectivity, 10000); // Check every 10 seconds

            $('#add_pos_sell_form').on('submit', function (e) {
                const statusElement = $('#connectivity-status');
                if (statusElement.hasClass('offline')) {
                    e.preventDefault();
                    const formData = $(this).serializeArray();
                    const transactionData = {
                        offline_id: 'offline_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                        transaction_data: formData
                    };

                    db.transactions.put(transactionData).then(() => {
                        toastr.success('@lang("Loan::lang.transaction_saved_offline")');
                        setTimeout(() => {
                            window.location.href = '{{ action([\Modules\Loan\Http\Controllers\LoanPosController::class, 'index']) }}';
                        }, 1000);
                    }).catch(error => {
                        toastr.error('@lang("messages.something_went_wrong")');
                    });
                }
            });
        });
    </script>
@endsection