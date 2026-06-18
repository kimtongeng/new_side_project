@extends('layouts.app')
@section('title', __('Loan::lang.pos_sale'))

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('Loan::lang.pos_sale')</h1>
</section>

<!-- Main content -->
<section class="content no-print">
    <!-- Connectivity Status Indicator -->
    <div class="alert alert-info" id="connectivity-status">
        <i class="fa fa-spinner fa-spin"></i> @lang('Loan::lang.checking_connectivity')...
    </div>

    @component('components.filters', ['title' => __('report.filters')])
        @include('Loan::pos.partials.sell_list_filters')
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => __('Loan::lang.pos_sale')])
        @can('sell.create')
            @slot('tool')
                <div class="box-tools">
                    <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                        href="{{ action([\Modules\Loan\Http\Controllers\LoanPosController::class, 'create']) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg> @lang('messages.add')
                    </a>
                </div>
            @endslot
        @endcan
        @can('sell.view')
            <input type="hidden" name="is_direct_sale" id="is_direct_sale" value="0">
            @include('Loan::pos.partials.sales_table')
        @endcan
    @endcomponent
</section>

<!-- Modals -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
<div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

<!-- Print Section -->
<section class="invoice print_section" id="receipt_section"></section>
@endsection

@section('css')
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
@endsection

@section('javascript')
    <!-- Include Dexie.js for IndexedDB -->
    <script src="https://unpkg.com/dexie@3/dist/dexie.min.js"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>

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
                success: function(response) {
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
                error: function() {
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
                    success: function(response) {
                        if (response.success) {
                            response.synced_transactions.forEach(sync => {
                                db.transactions.where('offline_id').equals(sync.offline_id).delete();
                            });
                            toastr.success(response.msg);
                        } else {
                            toastr.error(response.msg);
                        }
                    },
                    error: function() {
                        toastr.error('@lang("messages.something_went_wrong")');
                    }
                });
            });
        }

        // Check connectivity periodically
        $(document).ready(function() {
            checkConnectivity();
            setInterval(checkConnectivity, 10000); // Check every 10 seconds
        });
    </script>
@endsection